<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\Student;
use App\Models\AcademicSession;
use App\Services\RemitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamController extends Controller
{
    protected RemitaService $remitaService;

    public function __construct(RemitaService $remitaService)
    {
        $this->remitaService = $remitaService;
    }

    public function index()
    {
        $student = Auth::guard('student')->user();

        if (!$student->is_registered) {
            return redirect()->route('student.dashboard')
                ->with('error', 'You must complete registration first.');
        }

        $fee = Fee::getActiveFee('examination', $student->programme_type, $student->academic_session_id);
        $payment = $student->payments()
            ->where('payment_type', 'examination')
            ->where('status', 'pending')
            ->latest()->first();

        $hasPaid = $student->hasPaidExamFee();

        return view('student.exam.index', compact('student', 'fee', 'payment', 'hasPaid'));
    }

    public function initiatePayment()
    {
        $student = Auth::guard('student')->user();

        if ($student->hasPaidExamFee()) {
            return back()->with('info', 'Exam fee already paid.');
        }

        $fee = Fee::getActiveFee('examination', $student->programme_type, $student->academic_session_id);
        if (!$fee) {
            return back()->with('error', 'No exam fee configured.');
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'payable_type' => Student::class,
                'payable_id' => $student->id,
                'payment_type' => 'examination',
                'academic_session_id' => $student->academic_session_id,
                'fee_id' => $fee->id,
                'amount' => $fee->amount,
                'description' => 'Examination Fee - ' . $student->programme_type,
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
            Log::error('Exam Fee Init Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred.');
        }
    }

    public function verifyPayment()
    {
        $student = Auth::guard('student')->user();
        $payment = $student->payments()
            ->where('payment_type', 'examination')
            ->where('status', 'pending')
            ->latest()->first();

        if (!$payment || !$payment->hasRrr()) {
            return back()->with('error', 'No pending payment found.');
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            return redirect()->route('student.dashboard')
                ->with('success', 'Exam fee verified!');
        }

        return back()->with('info', $result['message'] ?? 'Payment not yet confirmed.');
    }
}
