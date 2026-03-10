<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RemitaService
{
    private string $merchantId;
    private string $apiKey;
    private string $serviceTypeId;
    private string $baseUrl;
    private bool $isLive;

    public function __construct()
    {
        $this->isLive = (bool) setting('remita_live', config('services.remita.live', false));
        $this->merchantId = setting('remita_merchant_id', config('services.remita.merchant_id', ''));
        $this->apiKey = setting('remita_api_key', config('services.remita.api_key', ''));
        $this->serviceTypeId = setting('remita_service_type_id', config('services.remita.service_type_id', ''));

        $this->baseUrl = $this->isLive
            ? 'https://login.remita.net'
            : 'https://remitademo.net';
    }

    /**
     * Get the service type ID for a specific programme and fee type.
     * e.g. getServiceTypeId('ijmb', 'application') or getServiceTypeId('remedial', 'exam')
     */
    public function getServiceTypeId(?string $programmeType = null, ?string $feeType = null): string
    {
        if ($programmeType && $feeType) {
            $key = 'remita_' . strtolower($programmeType) . '_' . strtolower($feeType) . '_service_type_id';
            $value = setting($key);
            if ($value) {
                return $value;
            }
        }
        return $this->serviceTypeId;
    }

    public function generateRRR(Payment $payment, array $payerDetails, ?string $programmeType = null, ?string $feeType = null): array
    {
        try {
            $serviceTypeId = $this->getServiceTypeId($programmeType, $feeType);
            $orderId = $this->generateOrderId();
            $hash = $this->generateHash($orderId, $payment->amount, $serviceTypeId);

            $payload = [
                'serviceTypeId' => $serviceTypeId,
                'amount' => $payment->amount,
                'orderId' => $orderId,
                'payerName' => $payerDetails['name'],
                'payerEmail' => $payerDetails['email'],
                'payerPhone' => $payerDetails['phone'] ?? '',
                'description' => $payment->description ?? 'SBRS Fee Payment',
            ];

            $apiUrl = $this->isLive
                ? 'https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit'
                : 'https://demo.remita.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit';

            $response = Http::withoutRedirecting()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "remitaConsumerKey={$this->merchantId},remitaConsumerToken={$hash}",
                ])
                ->post($apiUrl, $payload);

            $rawBody = $response->body();
            $result = $response->json();
            if ($result === null && $rawBody) {
                if (preg_match('/\{.*\}/s', $rawBody, $matches)) {
                    $result = json_decode($matches[0], true);
                }
            }

            Log::info('Remita RRR Generation Request', [
                'payment_id' => $payment->id,
                'url' => $apiUrl,
                'payload' => $payload,
                'response' => $result,
                'status_code' => $response->status(),
            ]);

            if (isset($result['statuscode']) && $result['statuscode'] == '025') {
                $payment->update([
                    'rrr' => $result['RRR'],
                    'order_id' => $orderId,
                    'status' => Payment::STATUS_PENDING,
                ]);

                return [
                    'success' => true,
                    'rrr' => $result['RRR'],
                    'order_id' => $orderId,
                    'message' => 'RRR generated successfully',
                ];
            }

            return [
                'success' => false,
                'message' => $result['status'] ?? $result['responseMsg'] ?? 'Failed to generate RRR',
                'debug' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('Remita RRR Generation Exception', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while generating RRR',
            ];
        }
    }

    public function verifyPayment(Payment $payment): array
    {
        if (!$payment->rrr) {
            return ['success' => false, 'message' => 'No RRR found for this payment'];
        }

        try {
            $hash = hash('sha512', $payment->rrr . $this->apiKey . $this->merchantId);

            $verifyUrl = $this->isLive
                ? "https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc/{$this->merchantId}/{$payment->rrr}/{$hash}/status.reg"
                : "https://demo.remita.net/remita/exapp/api/v1/send/api/echannelsvc/{$this->merchantId}/{$payment->rrr}/{$hash}/status.reg";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "remitaConsumerKey={$this->merchantId},remitaConsumerToken={$hash}",
            ])->get($verifyUrl);

            $rawBody = $response->body();
            $result = $response->json();
            if ($result === null && $rawBody) {
                if (preg_match('/\{.*\}/s', $rawBody, $matches)) {
                    $result = json_decode($matches[0], true);
                }
            }

            Log::info('Remita Payment Verification', [
                'payment_id' => $payment->id,
                'rrr' => $payment->rrr,
                'response' => $result,
            ]);

            if (isset($result['status'])) {
                $statusCode = $result['status'];

                if ($statusCode == '00' || $statusCode == '01') {
                    $payment->markAsSuccessful($result, $result['paymentMethod'] ?? null);
                    return [
                        'success' => true,
                        'status' => 'successful',
                        'message' => 'Payment verified successfully',
                        'data' => $result,
                    ];
                } elseif ($statusCode == '021' || $statusCode == '025') {
                    return [
                        'success' => true,
                        'status' => 'pending',
                        'message' => 'Payment is still pending',
                        'data' => $result,
                    ];
                } else {
                    $payment->markAsFailed($result);
                    return [
                        'success' => false,
                        'status' => 'failed',
                        'message' => $result['message'] ?? 'Payment verification failed',
                        'data' => $result,
                    ];
                }
            }

            return ['success' => false, 'message' => 'Invalid response from payment gateway'];

        } catch (\Exception $e) {
            Log::error('Remita Payment Verification Exception', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'An error occurred while verifying payment'];
        }
    }

    public function getPaymentUrl(Payment $payment, string $callbackUrl = ''): string
    {
        $baseUrl = $this->isLive
            ? 'https://login.remita.net'
            : 'https://demo.remita.net';

        if ($payment->rrr) {
            $url = "{$baseUrl}/remita/ecomm/finalize.reg?rrr={$payment->rrr}";
            if ($callbackUrl) {
                $url .= '&responseurl=' . urlencode($callbackUrl);
            }
            return $url;
        }

        return "{$baseUrl}/remita/onepage/biller/{$this->serviceTypeId}";
    }

    private function generateOrderId(): string
    {
        return 'SBRS' . date('YmdHis') . Str::random(6);
    }

    private function generateHash(string $orderId, $amount, ?string $serviceTypeId = null): string
    {
        $formattedAmount = number_format((float)$amount, 2, '.', '');
        $sid = $serviceTypeId ?? $this->serviceTypeId;
        $hashString = $this->merchantId . $sid . $orderId . $formattedAmount . $this->apiKey;
        return hash('sha512', $hashString);
    }
}
