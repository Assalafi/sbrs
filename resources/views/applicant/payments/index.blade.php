@extends('layouts.applicant')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Fee Payments</h3>
    <a href="{{ route('applicant.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

@if($hasRequiredDue)
<div class="alert alert-warning mb-4">
    <i class="material-symbols-outlined align-middle me-1">warning</i>
    <strong>Action Required:</strong> You have outstanding required payment(s). Please complete them to proceed.
</div>
@endif

{{-- Applicant summary card --}}
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    @if($applicant->passport_photo)
                        <img src="{{ asset('storage/' . $applicant->passport_photo) }}" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;">
                    @endif
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $applicant->full_name }}</h5>
                        <div class="text-muted small">
                            {{ $applicant->application_number }} &nbsp;|&nbsp; {{ $applicant->programme->name ?? $applicant->programme_type }} &nbsp;|&nbsp; {{ $applicant->academicSession->name ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-{{ $applicant->status === 'admitted' ? 'success' : 'warning' }} fs-13">{{ ucfirst(str_replace('_', ' ', $applicant->status)) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Due payments --}}
@if($items->isEmpty())
    <div class="card border-0 rounded-3">
        <div class="card-body p-5 text-center">
            <i class="material-symbols-outlined text-success" style="font-size:3rem;">check_circle</i>
            <p class="text-muted mt-3 mb-0">You have no outstanding payments. All your fees are settled.</p>
        </div>
    </div>
@else
    <div class="row">
        @foreach($items as $item)
            @php
                $type = $item['type'];
                $p = $item['progress'];
            @endphp
            <div class="col-md-6 mb-4">
                <div class="card border-0 rounded-3 h-100 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="material-symbols-outlined me-1 align-middle text-primary">receipt_long</i>
                            {{ $type->name }}
                        </h5>
                        @if($type->is_required)
                            <span class="badge bg-danger">Required</span>
                        @else
                            <span class="badge bg-secondary">Optional</span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        @if($type->description)
                            <p class="text-muted small mb-3">{{ $type->description }}</p>
                        @endif

                        <div class="row text-center mb-3 g-2">
                            <div class="col-4">
                                <div class="bg-light rounded py-2">
                                    <div class="fs-4 fw-bold text-success">&#8358;{{ number_format($p['full_amount'], 2) }}</div>
                                    <small class="text-muted">Total Fee</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded py-2">
                                    <div class="fs-4 fw-bold text-primary">&#8358;{{ number_format($p['paid'], 2) }}</div>
                                    <small class="text-muted">Paid</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded py-2">
                                    <div class="fs-4 fw-bold text-warning">&#8358;{{ number_format($p['remaining'], 2) }}</div>
                                    <small class="text-muted">Remaining</small>
                                </div>
                            </div>
                        </div>

                        @if($p['fully_paid'])
                            <div class="alert alert-success mb-0 text-center">
                                <i class="material-symbols-outlined align-middle me-1">check_circle</i> Fully Paid
                            </div>
                        @else
                            @if($p['installment'] !== null && $type->split_enabled)
                                <div class="alert alert-info small mb-3">
                                    <i class="material-symbols-outlined align-middle me-1">call_split</i>
                                    Split payment available: {{ $p['installment_label'] }} (&#8358;{{ number_format($p['installment_amount'], 2) }})
                                </div>
                            @endif

                            <form action="{{ route('applicant.payments.initiate') }}" method="POST" class="mb-2">
                                @csrf
                                <input type="hidden" name="payment_type_id" value="{{ $type->id }}">
                                <input type="hidden" name="mode" value="full">
                                <button type="submit" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                                    <i class="material-symbols-outlined fs-16 align-middle me-1">payments</i>
                                    Pay Full (&#8358;{{ number_format($p['remaining'], 2) }})
                                </button>
                            </form>

                            @if($p['installment'] !== null && $type->split_enabled)
                                <form action="{{ route('applicant.payments.initiate') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="payment_type_id" value="{{ $type->id }}">
                                    <input type="hidden" name="mode" value="installment">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="material-symbols-outlined fs-16 align-middle me-1">call_split</i>
                                        {{ $p['installment_label'] }} (&#8358;{{ number_format($p['installment_amount'], 2) }})
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Payment history --}}
<div class="card border-0 rounded-3 mt-2">
    <div class="card-header bg-transparent">
        <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">history</i>Payment History</h5>
    </div>
    <div class="card-body p-4">
        @if($history->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Date</th><th>Fee</th><th>Amount</th><th>RRR</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($history as $h)
                        <tr>
                            <td class="fs-13 text-muted">{{ $h->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                {{ $h->paymentType->name ?? ucwords(str_replace('_', ' ', $h->payment_type)) }}
                                @if($h->installment_label)
                                    <br><small class="text-muted">{{ $h->installment_label }}</small>
                                @endif
                            </td>
                            <td class="fw-medium">&#8358;{{ number_format($h->amount, 2) }}</td>
                            <td>{{ $h->rrr ?? 'N/A' }}</td>
                            <td>
                                @if($h->status === 'successful')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($h->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No payment records yet.</p>
        @endif
    </div>
</div>
@endsection
