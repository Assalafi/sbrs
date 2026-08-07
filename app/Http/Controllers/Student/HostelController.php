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
        $url = $this->getApiUrl() . '/' . $endpoint;
        $apiKey = $this->getApiKey();

        $data['api_key'] = $apiKey;

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(30)->$method($url, $data);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('USP Hostel API error', ['error' => $e->getMessage()]);
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
        ]);
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
                return back()->with('error', $result['message']);
            }

            DB::commit();
            return back()->with('success', 'RRR generated: ' . $result['rrr']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hostel Fee Init Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred.');
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
            ]);

            return redirect()->route('student.hostel.index')
                ->with('success', 'Hostel fee verified and payment confirmed.');
        }

        return back()->with('info', $result['message'] ?? 'Payment not yet confirmed.');
    }
}
