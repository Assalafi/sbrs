@extends('layouts.admin')
@section('title', 'Payment Details')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Payment Details</h3>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Payment Information</h5></div>
            <div class="card-body p-4">
                <table class="table table-borderless">
                    <tr><td class="text-muted">Reference:</td><td class="fw-medium">{{ $payment->reference ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">RRR:</td><td class="fw-medium">{{ $payment->rrr ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Order ID:</td><td class="fw-medium">{{ $payment->order_id ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Type:</td><td><span class="badge bg-info bg-opacity-10 text-info">{{ $payment->paymentType->name ?? ucfirst($payment->payment_type) }}</span></td></tr>
                    <tr><td class="text-muted">Sponsor:</td><td>{{ $payment->sponsor ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Method:</td><td>{{ $payment->payment_method ?? 'Online' }}</td></tr>
                    <tr><td class="text-muted">Amount:</td><td class="fw-bold fs-5">&#8358;{{ number_format($payment->amount, 2) }}</td></tr>
                    <tr><td class="text-muted">Status:</td><td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }} px-3 py-2">{{ ucfirst($payment->status) }}</span></td></tr>
                    <tr><td class="text-muted">Session:</td><td>{{ $payment->academicSession->name ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Description:</td><td>{{ $payment->description ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Notes:</td><td>{{ $payment->notes ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Created:</td><td>{{ $payment->created_at->format('M d, Y H:i:s') }}</td></tr>
                    <tr><td class="text-muted">Paid At:</td><td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('M d, Y H:i:s') : 'N/A' }}</td></tr>
                </table>

                @if($payment->status === 'successful')
                    <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-success btn-sm w-100 mb-2"><i class="material-symbols-outlined fs-16 align-middle">download</i> Download receipt</a>
                @endif
                @if($payment->status === \App\Models\Payment::STATUS_PENDING)
                    @can('payments.delete')<button type="button" class="btn btn-outline-danger btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#deletePaymentModal"><i class="material-symbols-outlined fs-16 align-middle">delete</i> Delete pending payment</button>@endcan
                @endif
                @if(strtolower((string)$payment->payment_method) === 'offline' || $payment->reference)
                    @can('payments.edit')<a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-outline-secondary btn-sm w-100 mb-2"><i class="material-symbols-outlined fs-16 align-middle">edit</i> Edit offline payment</a>@endcan
                    @if($payment->status !== 'cancelled') @can('payments.edit')<form action="{{ route('admin.payments.cancel', $payment) }}" method="POST" onsubmit="return confirm('Cancel this offline payment?');">@csrf<button class="btn btn-outline-warning btn-sm w-100 mb-2"><i class="material-symbols-outlined fs-16 align-middle">cancel</i> Cancel offline payment</button></form>@endcan
                    @else @can('payments.delete')<button type="button" class="btn btn-outline-danger btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#deletePaymentModal"><i class="material-symbols-outlined fs-16 align-middle">delete</i> Delete cancelled payment</button>@endcan @endif
                @endif
                @if($payment->hasRrr() && $payment->status !== 'successful' && strtolower((string)$payment->payment_method) !== 'offline')
                <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="mt-3">
                    @csrf
                    <button class="btn btn-info btn-sm w-100"><i class="material-symbols-outlined fs-16 align-middle">sync</i> Verify Payment</button>
                </form>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Payer Information</h5></div>
            <div class="card-body p-4">
                @if($payment->payable)
                <table class="table table-borderless">
                    <tr><td class="text-muted">Name:</td><td class="fw-medium">{{ $payment->payable->surname ?? '' }} {{ $payment->payable->first_name ?? '' }}</td></tr>
                    <tr><td class="text-muted">Email:</td><td>{{ $payment->payable->email ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Phone:</td><td>{{ $payment->payable->phone ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Type:</td><td>{{ class_basename($payment->payable_type) }}</td></tr>
                    @if($payment->payable_type === 'App\\Models\\Applicant')
                    <tr><td class="text-muted">App No:</td><td>{{ $payment->payable->application_number ?? 'N/A' }}</td></tr>
                    @elseif($payment->payable_type === 'App\\Models\\Student')
                    <tr><td class="text-muted">Reg No:</td><td>{{ $payment->payable->registration_number ?? 'N/A' }}</td></tr>
                    @endif
                </table>
                @else
                <p class="text-muted">Payer information not available.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($payment->proof_path)
<div class="card border-0 rounded-3 mt-4"><div class="card-body p-4"><h6 class="fw-semibold">Proof of payment</h6><a class="btn btn-outline-success btn-sm" href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank"><i class="material-symbols-outlined fs-16 align-middle">attach_file</i> View uploaded proof</a></div></div>
@endif

@if($payment->fee)
<div class="card border-0 rounded-3">
    <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Fee Information</h5></div>
    <div class="card-body p-4">
        <table class="table table-borderless">
            <tr><td class="text-muted">Fee Type:</td><td>{{ ucfirst($payment->fee->fee_type) }}</td></tr>
            <tr><td class="text-muted">Programme Type:</td><td>{{ $payment->fee->programme_type }}</td></tr>
            <tr><td class="text-muted">Standard Amount:</td><td>&#8358;{{ number_format($payment->fee->amount, 2) }}</td></tr>
        </table>
    </div>
</div>
@endif

@php
    $isOfflinePayment = strtolower((string) $payment->payment_method) === 'offline' || !empty($payment->reference);
    $canDeletePayment = $payment->status === \App\Models\Payment::STATUS_PENDING
        || ($isOfflinePayment && $payment->status === \App\Models\Payment::STATUS_CANCELLED);
@endphp
@if($canDeletePayment)
    @can('payments.delete')
        <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentTitle" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deletePaymentTitle"><i class="material-symbols-outlined me-2 align-middle">delete</i>{{ $payment->status === \App\Models\Payment::STATUS_PENDING ? 'Delete pending payment' : 'Delete cancelled payment' }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">{{ $payment->status === \App\Models\Payment::STATUS_PENDING ? 'This removes the pending payment so a new payment can be started. Successful payments cannot be deleted.' : 'This permanently removes the cancelled offline payment record.' }}</p>
                            <label class="form-label fw-semibold">Reason for deletion <span class="text-danger">*</span></label>
                            <textarea name="deletion_reason" class="form-control" rows="3" minlength="5" maxlength="500" required placeholder="Explain why this payment is being removed"></textarea>
                            <small class="text-muted">Enter at least 5 characters for the audit record.</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger"><i class="material-symbols-outlined align-middle me-1">delete</i>Delete payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endif
@endsection
