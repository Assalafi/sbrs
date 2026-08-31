<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Student;
use App\Services\RemitaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected RemitaService $remitaService;

    public function __construct(RemitaService $remitaService)
    {
        $this->remitaService = $remitaService;
    }

    private function student(): Student
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 401, 'Unauthorized');
        return $student;
    }

    /**
     * Professional payment page listing all due payment types.
     */
    public function index()
    {
        $student = $this->student();
        $student->load(['programme', 'academicSession', 'payments']);

        $dueTypes = $student->duePaymentTypes();

        // Build progress for each due type
        $items = $dueTypes->map(function ($type) use ($student) {
            $progress = $student->paymentTypeProgress($type);
            $pending = $student->payments()
                ->where('payment_type_id', $type->id)
                ->where('status', Payment::STATUS_PENDING)
                ->latest()
                ->first();
            return [
                'type' => $type,
                'progress' => $progress,
                'pending' => $pending,
            ];
        });

        // Pending payments (RRR generated but not yet verified) - shown first so
        // the student can continue / complete / verify them before starting new ones.
        $pendingPayments = $student->payments()
            ->with('paymentType')
            ->where('status', Payment::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        // Payment history
        $history = $student->payments()
            ->with('paymentType')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $hasRequiredDue = $dueTypes->contains(fn ($t) => $t->is_required);

        return view('student.payments.index', compact('student', 'items', 'history', 'hasRequiredDue', 'pendingPayments'));
    }

    /**
     * Initiate a payment for a given payment type (full or split installment).
     */
    public function initiate(Request $request)
    {
        $student = $this->student();

        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'mode' => 'required|in:full,installment',
        ]);

        $type = PaymentType::findOrFail($request->payment_type_id);

        if (!$type->is_active) {
            return back()->with('error', 'This payment type is not active.');
        }

        // Recompute progress (fresh) to avoid double-paying
        $progress = $student->paymentTypeProgress($type);
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
            // split / installment
            $installment = $progress['installment'];
            if ($installment === null) {
                return back()->with('error', 'No installment available for this payment.');
            }
            $installmentAmount = $progress['installment_amount'] ?: $progress['remaining'];
            $amount = $installmentAmount;
            $installmentLabel = $progress['installment_label'] ?: ('Installment ' . $installment . ' of ' . $progress['installment_total']);
            $installmentTotal = $progress['installment_total'] ?: $type->installment_count;
        }

        if ($amount <= 0) {
            return back()->with('error', 'Invalid payment amount.');
        }

        // Check for existing pending payment on this type
        $existing = $student->payments()
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
                'payable_type' => Student::class,
                'payable_id' => $student->id,
                'payment_type' => $type->code,
                'payment_type_id' => $type->id,
                'installment' => $installment,
                'installment_label' => $installmentLabel,
                'installment_total' => $installmentTotal,
                'is_full_payment' => $isFullPayment,
                'academic_session_id' => $student->academic_session_id,
                'amount' => $amount,
                'currency' => 'NGN',
                'description' => $type->name . ' - ' . $student->programme_type,
                'status' => Payment::STATUS_PENDING,
            ]);
            $payment->save();

            $result = $this->remitaService->generateRRR(
                $payment,
                ['name' => $student->full_name, 'email' => $student->email, 'phone' => $student->phone],
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
            Log::error('Payment Type Init Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Verify a pending payment and apply side effects.
     */
    public function verify(Request $request, Payment $payment = null)
    {
        $student = $this->student();

        if ($payment === null) {
            $request->validate([
                'payment_type_id' => 'required|exists:payment_types,id',
            ]);
            $payment = $student->payments()
                ->where('payment_type_id', $request->payment_type_id)
                ->where('status', Payment::STATUS_PENDING)
                ->latest()
                ->first();
        } else {
            // Ensure the payment belongs to this student and is pending
            $payment = $student->payments()
                ->where('id', $payment->id)
                ->where('status', Payment::STATUS_PENDING)
                ->first();
        }

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

            // Side effect: registration fully paid -> mark registered
            if (($payment->payment_type ?? $payment->paymentType?->code) === 'registration') {
                $type = $payment->paymentType ?? PaymentType::where('code', 'registration')->first();
                if ($type) {
                    $progress = $student->paymentTypeProgress($type);
                    if ($progress['fully_paid'] && !$student->is_registered) {
                        $student->update([
                            'is_registered' => true,
                            'registered_at' => now(),
                        ]);
                    }
                }
            }

            return redirect()->route('student.payments.index')
                ->with('success', 'Payment verified successfully!');
        }

        return back()->with('info', $result['message'] ?? 'Payment not yet confirmed.');
    }

    /**
     * Cancel/abandon a pending payment so the student can retry with a fresh RRR.
     */
    public function cancel(Request $request, Payment $payment = null)
    {
        $student = $this->student();

        if ($payment === null) {
            $request->validate([
                'payment_type_id' => 'required|exists:payment_types,id',
            ]);
            $payment = $student->payments()
                ->where('payment_type_id', $request->payment_type_id)
                ->where('status', Payment::STATUS_PENDING)
                ->latest()
                ->first();
        } else {
            $payment = $student->payments()
                ->where('id', $payment->id)
                ->where('status', Payment::STATUS_PENDING)
                ->first();
        }

        if (!$payment) {
            return back()->with('error', 'No pending payment found for this fee.');
        }

        $payment->update([
            'status' => Payment::STATUS_CANCELLED,
            'rrr' => null,
            'order_id' => null,
        ]);

        return back()->with('success', 'Pending payment cancelled. You can start a fresh payment for this fee.');
    }
}
