<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\Student;
use App\Services\RemitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    protected RemitaService $remitaService;

    public function __construct(RemitaService $remitaService)
    {
        $this->remitaService = $remitaService;
    }

    public function index()
    {
        $student = Auth::guard('student')->user();
        $student->load(['programme', 'academicSession', 'payments']);

        if ($student->screening_status !== 'approved') {
            return redirect()->route('student.dashboard')
                ->with('error', 'You must be screened before registration.');
        }

        $fee = Fee::getActiveFee('registration', $student->programme_type, $student->academic_session_id);
        $payment = $student->payments()
            ->where('payment_type', 'registration')
            ->where('status', 'pending')
            ->latest()->first();

        return view('student.registration.index', compact('student', 'fee', 'payment'));
    }

    public function initiatePayment()
    {
        $student = Auth::guard('student')->user();

        if ($student->hasPaidRegistrationFee()) {
            return back()->with('info', 'Registration fee already paid.');
        }

        $fee = Fee::getActiveFee('registration', $student->programme_type, $student->academic_session_id);
        if (!$fee) {
            return back()->with('error', 'No registration fee configured.');
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'payable_type' => Student::class,
                'payable_id' => $student->id,
                'payment_type' => 'registration',
                'academic_session_id' => $student->academic_session_id,
                'fee_id' => $fee->id,
                'amount' => $fee->amount,
                'description' => 'Registration Fee - ' . $student->programme_type,
                'status' => Payment::STATUS_PENDING,
            ]);

            $result = $this->remitaService->generateRRR($payment, [
                'name' => $student->full_name,
                'email' => $student->email,
                'phone' => $student->phone,
            ]);

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

            DB::commit();
            return back()->with('success', 'RRR generated: ' . $result['rrr']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration Fee Init Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred.');
        }
    }

    public function verifyPayment()
    {
        $student = Auth::guard('student')->user();
        $payment = $student->payments()
            ->where('payment_type', 'registration')
            ->where('status', 'pending')
            ->latest()->first();

        if (!$payment || !$payment->hasRrr()) {
            return back()->with('error', 'No pending payment found.');
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            $student->update([
                'is_registered' => true,
                'registered_at' => now(),
            ]);
            return redirect()->route('student.dashboard')
                ->with('success', 'Registration fee verified! You are now registered.');
        }

        return back()->with('info', $result['message'] ?? 'Payment not yet confirmed.');
    }
}
