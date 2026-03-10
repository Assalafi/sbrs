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
                    <tr><td class="text-muted">RRR:</td><td class="fw-medium">{{ $payment->rrr ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Order ID:</td><td class="fw-medium">{{ $payment->order_id ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Type:</td><td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($payment->payment_type) }}</span></td></tr>
                    <tr><td class="text-muted">Amount:</td><td class="fw-bold fs-5">&#8358;{{ number_format($payment->amount, 2) }}</td></tr>
                    <tr><td class="text-muted">Status:</td><td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }} px-3 py-2">{{ ucfirst($payment->status) }}</span></td></tr>
                    <tr><td class="text-muted">Session:</td><td>{{ $payment->academicSession->name ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Description:</td><td>{{ $payment->description ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Created:</td><td>{{ $payment->created_at->format('M d, Y H:i:s') }}</td></tr>
                    <tr><td class="text-muted">Paid At:</td><td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('M d, Y H:i:s') : 'N/A' }}</td></tr>
                </table>

                @if($payment->hasRrr() && $payment->status !== 'successful')
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
@endsection
