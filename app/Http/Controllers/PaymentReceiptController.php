<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PaymentReceiptController extends Controller
{
    public function student(Payment $payment)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 401, 'Unauthorized');

        if ($payment->payable_type !== Student::class || (string) $payment->payable_id !== (string) $student->id) {
            abort(403, 'This receipt does not belong to your account.');
        }

        return $this->download($payment->load(['payable', 'academicSession', 'paymentType']), $student);
    }

    public function admin(Payment $payment)
    {
        abort_unless(Auth::guard('web')->check(), 401, 'Unauthorized');

        $payment->load(['payable', 'academicSession', 'paymentType']);
        return $this->download($payment, $payment->payable);
    }

    private function download(Payment $payment, $payer)
    {
        if ($payment->status !== Payment::STATUS_SUCCESSFUL) {
            return back()->with('error', 'A receipt is available only for successful payments.');
        }

        $amountInWords = $this->amountInWords((float) $payment->amount);
        $logoBase64 = $this->localImageDataUri(setting_image('main_logo'))
            ?: $this->localImageDataUri(setting_image('logo'))
            ?: $this->localImageDataUri(asset('assets/images/favicon.png'));

        $pdf = Pdf::loadView('pdf.payment-receipt', compact('payment', 'payer', 'amountInWords', 'logoBase64'))
            ->setPaper('a4', 'portrait');

        $reference = $payment->reference ?: ($payment->rrr ?: $payment->id);
        return $pdf->download('payment-receipt-' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $reference) . '.pdf');
    }

    private function amountInWords(float $amount): string
    {
        if (class_exists('NumberFormatter')) {
            $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
            $naira = (int) floor($amount);
            $kobo = (int) round(($amount - $naira) * 100);
            $words = ucfirst($formatter->format($naira)) . ' Naira';
            if ($kobo > 0) {
                $words .= ' and ' . ucfirst($formatter->format($kobo)) . ' Kobo';
            }
            return $words;
        }

        return number_format($amount, 2) . ' Naira';
    }

    private function localImageDataUri(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $pathPart = parse_url($url, PHP_URL_PATH) ?: $url;
        $relative = ltrim((string) $pathPart, '/');
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        $candidates = [
            storage_path('app/public/' . $relative),
            public_path($relative),
        ];

        foreach ($candidates as $path) {
            if (! is_file($path)) {
                continue;
            }

            $mime = mime_content_type($path) ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        }

        return null;
    }
}
