<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Payment;
use App\Models\Fee;
use App\Models\Student;
use App\Models\AcademicSession;
use App\Models\PaymentType;
use App\Services\RemitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected RemitaService $remitaService;

    public function __construct(RemitaService $remitaService)
    {
        $this->remitaService = $remitaService;
    }

    public function applicationFee()
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->hasPaidApplicationFee()) {
            return redirect()->route('applicant.dashboard')
                ->with('info', 'You have already paid the application fee.');
        }

        $fee = Fee::getActiveFee('application', $applicant->programme_type, $applicant->academic_session_id);
        $payment = $applicant->getPendingPayment('application');

        return view('applicant.payment.application-fee', compact('applicant', 'fee', 'payment'));
    }

    public function initiateApplicationFee(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->hasPaidApplicationFee()) {
            return back()->with('error', 'Already paid.');
        }

        $fee = Fee::getActiveFee('application', $applicant->programme_type, $applicant->academic_session_id);
        if (!$fee) {
            return back()->with('error', 'No application fee configured. Please contact admin.');
        }

        $existingPayment = $applicant->getPendingPayment('application');
        if ($existingPayment && $existingPayment->hasRrr()) {
            return back()->with('info', 'You already have a pending payment. Please complete or verify it.');
        }

        DB::beginTransaction();
        try {
            $payment = $existingPayment ?? new Payment();
            $payment->fill([
                'payable_type' => Applicant::class,
                'payable_id' => $applicant->id,
                'payment_type' => 'application',
                'academic_session_id' => $applicant->academic_session_id,
                'fee_id' => $fee->id,
                'amount' => $fee->amount,
                'currency' => 'NGN',
                'description' => 'Application Fee - ' . $applicant->programme_type . ' Programme',
                'status' => Payment::STATUS_PENDING,
            ]);
            $payment->save();

            $result = $this->remitaService->generateRRR($payment, [
                'name' => $applicant->full_name,
                'email' => $applicant->email,
                'phone' => $applicant->phone,
            ]);

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

            DB::commit();
            return back()->with('success', 'Payment initialized. Your RRR is: ' . $result['rrr']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Application Fee Init Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function verifyApplicationFee()
    {
        $applicant = Auth::guard('applicant')->user();
        $payment = $applicant->getPendingPayment('application');

        if (!$payment || !$payment->hasRrr()) {
            return back()->with('error', 'No pending payment found.');
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            $applicant->update(['status' => 'form_filling']);
            return redirect()->route('applicant.dashboard')
                ->with('success', 'Application fee verified! You can now fill your application form.');
        } elseif ($result['success'] && $result['status'] === 'pending') {
            return back()->with('info', 'Payment is still pending. Please complete your payment.');
        }

        return back()->with('error', $result['message'] ?? 'Verification failed.');
    }

    public function admissionFee()
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->status !== 'approved') {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'Your application must be approved first.');
        }

        if ($applicant->hasPaidAdmissionFee()) {
            return redirect()->route('applicant.dashboard')
                ->with('info', 'You have already paid the admission fee.');
        }

        $fee = Fee::getActiveFee('admission', $applicant->programme_type, $applicant->academic_session_id);
        $payment = $applicant->getPendingPayment('admission');

        return view('applicant.payment.admission-fee', compact('applicant', 'fee', 'payment'));
    }

    public function initiateAdmissionFee(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->status !== 'approved') {
            return back()->with('error', 'Application must be approved.');
        }

        if ($applicant->hasPaidAdmissionFee()) {
            return back()->with('error', 'Already paid.');
        }

        $fee = Fee::getActiveFee('admission', $applicant->programme_type, $applicant->academic_session_id);
        if (!$fee) {
            return back()->with('error', 'No admission fee configured. Contact admin.');
        }

        $existingPayment = $applicant->getPendingPayment('admission');
        if ($existingPayment && $existingPayment->hasRrr()) {
            return back()->with('info', 'You already have a pending payment.');
        }

        DB::beginTransaction();
        try {
            $payment = $existingPayment ?? new Payment();
            $payment->fill([
                'payable_type' => Applicant::class,
                'payable_id' => $applicant->id,
                'payment_type' => 'admission',
                'academic_session_id' => $applicant->academic_session_id,
                'fee_id' => $fee->id,
                'amount' => $fee->amount,
                'currency' => 'NGN',
                'description' => 'Admission Fee - ' . $applicant->programme_type . ' Programme',
                'status' => Payment::STATUS_PENDING,
            ]);
            $payment->save();

            $result = $this->remitaService->generateRRR($payment, [
                'name' => $applicant->full_name,
                'email' => $applicant->email,
                'phone' => $applicant->phone,
            ]);

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

            DB::commit();
            return back()->with('success', 'RRR generated: ' . $result['rrr']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admission Fee Init Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred.');
        }
    }

    public function verifyAdmissionFee()
    {
        $applicant = Auth::guard('applicant')->user();
        $payment = $applicant->getPendingPayment('admission');

        if (!$payment || !$payment->hasRrr()) {
            return back()->with('error', 'No pending payment found.');
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            DB::beginTransaction();
            try {
                $payment->update(['verified_at' => now()]);
                $applicant->update(['status' => 'admitted']);

                $student = Student::createFromApplicant($applicant, $applicant->password);

                DB::commit();

                return redirect()->route('applicant.admission.payment-confirmation')
                    ->with('success', 'Admission fee verified! Your student account has been created. Use your applicant password to log in as a student.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Admission Processing Failed', ['error' => $e->getMessage()]);
                return back()->with('error', 'Payment verified but processing failed. Contact admin with RRR: ' . $payment->rrr);
            }
        } elseif ($result['success'] && $result['status'] === 'pending') {
            return back()->with('info', 'Payment is still pending.');
        }

        return back()->with('error', $result['message'] ?? 'Verification failed.');
    }

    public function callback(Request $request)
    {
        $rrr = $request->input('RRR') ?? $request->input('rrr');
        if (!$rrr) {
            return redirect()->route('applicant.dashboard')->with('error', 'Invalid callback.');
        }

        $payment = Payment::where('rrr', $rrr)->first();
        if (!$payment) {
            return redirect()->route('applicant.dashboard')->with('error', 'Payment not found.');
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            if ($payment->payment_type === 'application') {
                $payment->payable->update(['status' => 'form_filling']);
                return redirect()->route('applicant.dashboard')
                    ->with('success', 'Application fee payment successful!');
            } elseif ($payment->payment_type === 'admission') {
                return redirect()->route('applicant.payment.admission-fee.verify');
            }
        }

        return redirect()->route('applicant.dashboard')
            ->with('info', 'Payment status: ' . ($result['status'] ?? 'unknown'));
    }

    /* ============================================================
     * Generic configurable payment types (applicant + both payer types)
     * ============================================================ */

    private function applicant(): Applicant
    {
        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 401, 'Unauthorized');
        return $applicant;
    }

    /**
     * Generic payment page for applicants (any applicant/both payment types).
     */
    public function index()
    {
        $applicant = $this->applicant();
        $applicant->load(['programme', 'academicSession', 'payments']);

        $dueTypes = $applicant->duePaymentTypes();
        $items = $dueTypes->map(function ($type) use ($applicant) {
            return ['type' => $type, 'progress' => $applicant->paymentTypeProgress($type)];
        });

        $history = $applicant->payments()->with('paymentType')->orderBy('created_at', 'desc')->take(50)->get();
        $hasRequiredDue = $dueTypes->contains(fn ($t) => $t->is_required);

        return view('applicant.payments.index', compact('applicant', 'items', 'history', 'hasRequiredDue'));
    }

    /**
     * Initiate a payment (full or split installment).
     */
    public function initiate(Request $request)
    {
        $applicant = $this->applicant();

        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'mode' => 'required|in:full,installment',
        ]);

        $type = PaymentType::findOrFail($request->payment_type_id);

        if (!$type->is_active) {
            return back()->with('error', 'This payment type is not active.');
        }

        $progress = $applicant->paymentTypeProgress($type);
        if ($progress['fully_paid']) {
            return back()->with('info', 'This payment is already fully paid.');
        }

        $amount = 0;
        $installment = null;
        $installmentLabel = null;
        $installmentTotal = null;
        $isFullPayment = false;

        if ($request->mode === 'full') {
            $amount = $progress['remaining'];
            $isFullPayment = true;
        } else {
            $installment = $progress['installment'];
            if ($installment === null) {
                return back()->with('error', 'No installment available for this payment.');
            }
            $amount = $progress['installment_amount'] ?: $progress['remaining'];
            $installmentLabel = $progress['installment_label'] ?: ('Installment ' . $installment . ' of ' . $progress['installment_total']);
            $installmentTotal = $progress['installment_total'] ?: $type->installment_count;
        }

        if ($amount <= 0) {
            return back()->with('error', 'Invalid payment amount.');
        }

        $existing = $applicant->payments()
            ->where('payment_type_id', $type->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();
        if ($existing && $existing->hasRrr()) {
            return back()->with('info', 'You already have a pending payment for this fee. Please complete or verify it.');
        }

        DB::beginTransaction();
        try {
            $payment = $existing ?? new Payment();
            $payment->fill([
                'payable_type' => Applicant::class,
                'payable_id' => $applicant->id,
                'payment_type' => $type->code,
                'payment_type_id' => $type->id,
                'installment' => $installment,
                'installment_label' => $installmentLabel,
                'installment_total' => $installmentTotal,
                'is_full_payment' => $isFullPayment,
                'academic_session_id' => $applicant->academic_session_id,
                'amount' => $amount,
                'currency' => 'NGN',
                'description' => $type->name . ' - ' . $applicant->programme_type,
                'status' => Payment::STATUS_PENDING,
            ]);
            $payment->save();

            $result = $this->remitaService->generateRRR(
                $payment,
                ['name' => $applicant->full_name, 'email' => $applicant->email, 'phone' => $applicant->phone],
                null,
                null,
                $type->remita_service_type_id
            );

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

            DB::commit();
            return back()->with('success', 'RRR generated: ' . $result['rrr']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Applicant Payment Init Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Verify a pending payment and apply side effects.
     */
    public function verify(Request $request)
    {
        $applicant = $this->applicant();

        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
        ]);

        $type = PaymentType::findOrFail($request->payment_type_id);

        $payment = $applicant->payments()
            ->where('payment_type_id', $type->id)
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if (!$payment || !$payment->hasRrr()) {
            return back()->with('error', 'No pending payment found for this fee.');
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            $updates = ['verified_at' => now()];
            if ($payment->is_full_payment) {
                $updates['full_payment_at'] = now();
            }
            $payment->update($updates);

            // Side effects: application fee -> form_filling; admission fee -> admitted + create student
            if ($type->code === 'application' && $applicant->status === 'registered') {
                $applicant->update(['status' => 'form_filling']);
            } elseif ($type->code === 'admission') {
                $progress = $applicant->paymentTypeProgress($type);
                if ($progress['fully_paid']) {
                    $applicant->update(['status' => 'admitted']);
                    if (!$applicant->student) {
                        Student::createFromApplicant($applicant, $applicant->password);
                    }
                }
            }

            return redirect()->route('applicant.payments.index')
                ->with('success', 'Payment verified successfully!');
        }

        return back()->with('info', $result['message'] ?? 'Payment not yet confirmed.');
    }
}
