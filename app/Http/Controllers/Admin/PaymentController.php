<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AcademicSession;
use App\Services\RemitaService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected RemitaService $remitaService;

    public function __construct(RemitaService $remitaService)
    {
        $this->remitaService = $remitaService;
    }

    public function index(Request $request)
    {
        $query = Payment::with(['payable', 'academicSession', 'fee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('rrr', 'like', "%{$request->search}%")
                  ->orWhere('order_id', 'like', "%{$request->search}%");
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(25);
        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        return view('admin.payments.index', compact('payments', 'sessions'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['payable', 'academicSession', 'fee']);
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
        if (!$payment->hasRrr()) {
            return response()->json(['type' => 'error', 'message' => 'No RRR found for this payment.']);
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            return response()->json([
                'type' => 'success',
                'message' => 'Payment verified successfully!',
                'status' => 'successful',
            ]);
        } elseif ($result['success'] && $result['status'] === 'pending') {
            return response()->json([
                'type' => 'warning',
                'message' => 'Payment is still pending.',
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'type' => 'error',
            'message' => 'Verification: ' . ($result['message'] ?? 'Failed'),
            'status' => 'failed',
        ]);
    }

    public function verifyByRrr(Request $request)
    {
        $rrr = trim($request->rrr);
        if (empty($rrr)) {
            return response()->json(['type' => 'error', 'message' => 'Please enter an RRR.']);
        }

        $payment = Payment::where('rrr', $rrr)->first();
        if (!$payment) {
            return response()->json(['type' => 'error', 'message' => "No payment found with RRR: {$rrr}"]);
        }

        return $this->verify($payment);
    }

    public function export(Request $request)
    {
        $query = Payment::with(['payable', 'academicSession']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('payment_type')) $query->where('payment_type', $request->payment_type);
        if ($request->filled('academic_session_id')) $query->where('academic_session_id', $request->academic_session_id);

        $payments = $query->orderBy('created_at', 'desc')->get();

        $filename = 'payments_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'RRR', 'Order ID', 'Payer', 'Type', 'Amount', 'Status', 'Session']);
            foreach ($payments as $payment) {
                $payerName = $payment->payable ? ($payment->payable->surname ?? '') . ' ' . ($payment->payable->first_name ?? '') : 'N/A';
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->rrr ?? 'N/A',
                    $payment->order_id ?? 'N/A',
                    trim($payerName),
                    ucfirst($payment->payment_type),
                    number_format($payment->amount, 2),
                    ucfirst($payment->status),
                    $payment->academicSession->name ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Payment $payment)
    {
        if ($payment->status === 'successful') {
            return back()->with('error', 'Cannot delete a successful payment.');
        }
        $payment->delete();
        return back()->with('success', 'Payment record deleted.');
    }
}
