<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use App\Services\RemitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HostelController extends Controller
{
    protected RemitaService $remitaService;

    public function __construct(RemitaService $remitaService)
    {
        $this->remitaService = $remitaService;
    }

    private function getApiUrl()
    {
        return config('app.usp_hostel_api_url', env('USP_HOSTEL_API_URL', 'https://umstad.online/api/v1/hostel'));
    }

    private function getApiKey()
    {
        return config('app.usp_hostel_api_key', env('USP_HOSTEL_API_KEY'));
    }

    private function apiCall(string $method, string $endpoint, array $data = [])
    {
        $url = rtrim($this->getApiUrl(), '/') . '/' . ltrim($endpoint, '/');
        $apiKey = $this->getApiKey();

        if (!$apiKey) {
            Log::error('USP Hostel API key is not configured.');
            return [
                'success' => false,
                'message' => 'Hostel service is not configured. Please contact Student Affairs.',
            ];
        }

        $method = strtolower($method);
        $signedData = array_filter($data, static fn ($value) => $value !== null && $value !== '');
        ksort($signedData);
        $timestamp = (string) time();
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '/');
        $canonical = strtoupper($method) . "\n" . $path . "\n"
            . http_build_query($signedData, '', '&', PHP_QUERY_RFC3986) . "\n" . $timestamp;
        $signature = hash_hmac('sha256', $canonical, $apiKey);

        try {
            $http = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'X-API-Timestamp' => $timestamp,
                'X-API-Signature' => $signature,
                'Accept' => 'application/json',
            ])->acceptJson()->connectTimeout(8)->timeout(20);

            $response = $method === 'get'
                ? $http->get($url, $signedData)
                : $http->asJson()->post($url, $signedData);

            if (!$response->successful()) {
                Log::warning('USP Hostel API returned an error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            }

            return $response->json() ?: [
                'success' => false,
                'message' => 'Hostel service returned an invalid response.',
            ];
        } catch (\Exception $e) {
            Log::error('USP Hostel API error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Hostel service is temporarily unavailable. Please try again later.',
            ];
        }
    }

    /**
     * All SBRS students (IJMB and Remedial) can apply for hostel via this portal.
     */
    private function authStudentOrFail()
    {
        $student = Auth::guard('student')->user();

        if (!$student) {
            abort(401, 'Unauthorized');
        }

        return $student;
    }

    public function index()
    {
        $student = $this->authStudentOrFail();
        $student->load(['programme', 'academicSession']);

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        $overview = $this->apiCall('get', 'overview', ['gender' => $gender]);
        $reservation = $this->apiCall('get', 'status', ['registration_number' => $student->registration_number]);
        $pinStatus = $this->apiCall('get', 'pin-status', ['registration_number' => $student->registration_number]);

        $payment = $student->payments()
            ->where('payment_type', 'hostel')
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        return view('student.hostel.index', [
            'student' => $student,
            'gender' => $gender,
            'payment' => $payment,
            'overview' => $overview['data'] ?? [],
            'overviewError' => isset($overview['success']) && !$overview['success'] ? ($overview['message'] ?? 'Unable to reach hostel service.') : null,
            'reservation' => $reservation['data'] ?? null,
            'reservationError' => isset($reservation['success']) && !$reservation['success'] ? ($reservation['message'] ?? '') : null,
            'pinVerified' => (bool) ($pinStatus['data']['validated'] ?? false),
            'pinError' => isset($pinStatus['success']) && !$pinStatus['success'] ? ($pinStatus['message'] ?? '') : null,
        ]);
    }

    /**
     * Validate a one-time hostel PIN issued by Student Affairs. The PIN is
     * assigned to this SBRS registration number in the UG hostel database,
     * so the reservation API can enforce the same rule as UG students.
     */
    public function validatePin(Request $request)
    {
        $student = $this->authStudentOrFail();

        $request->validate([
            'pin' => 'required|string|max:64',
        ]);

        $result = $this->apiCall('post', 'validate-pin', [
            'registration_number' => $student->registration_number,
            'pin' => trim((string) $request->input('pin')),
        ]);

        if (!empty($result['success'])) {
            return redirect()->route('student.hostel.index')->with('success', 'Hostel PIN validated. You can now reserve a bed space.');
        }

        return back()->withInput()->with('error', $result['message'] ?? 'The hostel PIN could not be validated.');
    }

    public function overview(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');
        return response()->json($this->apiCall('get', 'overview', ['gender' => $gender]));
    }

    public function blocks(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        return response()->json($this->apiCall('get', 'blocks', [
            'hall' => $request->input('hall'),
            'gender' => $gender,
        ]));
    }

    public function rooms(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        return response()->json($this->apiCall('get', 'rooms', [
            'hall' => $request->input('hall'),
            'block' => $request->input('block'),
            'gender' => $gender,
        ]));
    }

    public function beds(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        return response()->json($this->apiCall('get', 'beds', [
            'hall' => $request->input('hall'),
            'block' => $request->input('block'),
            'room' => $request->input('room'),
            'gender' => $gender,
        ]));
    }

    public function reserve(Request $request)
    {
        $student = $this->authStudentOrFail();

        $request->validate([
            'hall' => 'required|string',
            'block' => 'required|string',
            'room' => 'required|integer',
            'bed' => 'required|integer',
        ]);

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        $pinStatus = $this->apiCall('get', 'pin-status', ['registration_number' => $student->registration_number]);
        if (empty($pinStatus['data']['validated'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validate your hostel PIN before reserving a bed space.',
            ], 422);
        }

        return response()->json($this->apiCall('post', 'reserve', [
            'registration_number' => $student->registration_number,
            'hall' => $request->input('hall'),
            'block' => $request->input('block'),
            'room' => $request->input('room'),
            'bed' => $request->input('bed'),
            'gender' => $gender,
        ]));
    }

    public function status()
    {
        $student = $this->authStudentOrFail();

        return response()->json($this->apiCall('get', 'status', [
            'registration_number' => $student->registration_number,
        ]));
    }

    public function release()
    {
        $student = $this->authStudentOrFail();

        return response()->json($this->apiCall('post', 'release', [
            'registration_number' => $student->registration_number,
        ]));
    }

    /**
     * Generate a Remita RRR for the reserved hostel bed.
     */
    public function payInitiate()
    {
        $student = $this->authStudentOrFail();

        if ($student->hasPaidHostelFee()) {
            return back()->with('info', 'Hostel fee already paid.');
        }

        $reservation = $this->apiCall('get', 'status', ['registration_number' => $student->registration_number]);
        $reservationData = $reservation['data'] ?? null;

        if (!$reservationData) {
            return back()->with('error', 'No hostel reservation found. Please reserve a bed first.');
        }

        if (($reservationData['hostel_payment'] ?? 0) == 1) {
            return back()->with('info', 'Hostel fee already paid.');
        }

        $amount = $reservationData['amount'] ?? 0;
        if ($amount <= 0) {
            return back()->with('error', 'Invalid hostel fee amount.');
        }

        DB::beginTransaction();
        try {
            $existingPayment = $student->payments()
                ->where('payment_type', 'hostel')
                ->where('status', Payment::STATUS_PENDING)
                ->latest()
                ->first();

            $payment = $existingPayment ?? new Payment();
            $payment->fill([
                'payable_type' => Student::class,
                'payable_id' => $student->id,
                'payment_type' => 'hostel',
                'academic_session_id' => $student->academic_session_id,
                'amount' => $amount,
                'currency' => 'NGN',
                'description' => 'Hostel Fee - ' . $student->programme_type,
                'status' => Payment::STATUS_PENDING,
            ]);
            $payment->save();

            $result = $this->remitaService->generateRRR($payment, [
                'name' => $student->full_name,
                'email' => $student->email,
                'phone' => $student->phone,
            ], null, 'hostel');

            if (!$result['success']) {
                DB::rollBack();
                Log::error('Hostel Fee RRR Generation Failed', [
                    'registration_number' => $student->registration_number,
                    'amount' => $amount,
                    'message' => $result['message'] ?? 'Unknown error',
                    'debug' => $result['debug'] ?? null,
                ]);
                return back()->with('error', 'RRR generation failed: ' . ($result['message'] ?? 'Unknown error'));
            }

            DB::commit();
            return back()->with('success', 'RRR generated: ' . $result['rrr']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hostel Fee Init Failed', [
                'registration_number' => $student->registration_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An error occurred while generating RRR: ' . $e->getMessage());
        }
    }

    /**
     * Verify the hostel fee payment and confirm to USP.
     */
    public function payVerify()
    {
        $student = $this->authStudentOrFail();

        $payment = $student->payments()
            ->where('payment_type', 'hostel')
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if (!$payment || !$payment->hasRrr()) {
            return back()->with('error', 'No pending payment found.');
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            $payment->update(['verified_at' => now()]);

            $confirm = $this->apiCall('post', 'confirm-payment', [
                'registration_number' => $student->registration_number,
                'rrr' => $payment->rrr,
                'amount' => (float) $payment->amount,
            ]);

            if (empty($confirm['success'])) {
                Log::error('SBRS hostel payment confirmation failed after successful Remita verification', [
                    'registration_number' => $student->registration_number,
                    'payment_id' => $payment->id,
                    'rrr' => $payment->rrr,
                    'response' => $confirm,
                ]);

                return redirect()->route('student.hostel.index')->with('error', 'Payment was received, but the hostel allocation could not be updated yet. Please try verification again or contact Student Affairs.');
            }

            return redirect()->route('student.hostel.index')
                ->with('success', 'Hostel fee verified and payment confirmed.');
        }

        return back()->with('info', $result['message'] ?? 'Payment not yet confirmed.');
    }
}
