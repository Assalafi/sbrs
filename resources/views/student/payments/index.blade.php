@extends('layouts.student')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Fee Payments</h3>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

@if($pendingPayments->count())
    <div class="card border-0 rounded-3 mb-4" style="border-left: 4px solid #f59e0b !important;">
        <div class="card-header bg-transparent d-flex align-items-center">
            <i class="material-symbols-outlined me-2 text-warning">schedule</i>
            <h5 class="mb-0 fw-semibold">Pending Payment{{ $pendingPayments->count() > 1 ? 's' : '' }}</h5>
            <span class="badge bg-warning text-dark ms-2">{{ $pendingPayments->count() }}</span>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                You have payment(s) already started but not yet confirmed. Please <strong>complete the payment</strong> online, then <strong>verify</strong> it — or cancel it to start a fresh one.
            </p>
            <div class="list-group">
                @foreach($pendingPayments as $payment)
                    @php $widgetKey = 'Pay' . str_replace('-', '', $payment->id); @endphp
                    <div class="list-group-item border-0 rounded-3 shadow-sm mb-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <h6 class="mb-1 fw-semibold">{{ $payment->paymentType->name ?? ucwords(str_replace('_', ' ', $payment->payment_type)) }}</h6>
                                <div class="text-muted small mb-1">
                                    Amount: <strong class="text-dark">&#8358;{{ number_format($payment->amount, 2) }}</strong>
                                    @if($payment->installment_label)
                                        <span class="badge bg-light text-dark border ms-1">{{ $payment->installment_label }}</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted small">RRR:</span>
                                    <code class="bg-light px-2 py-1 rounded" id="rrr-{{ $payment->id }}">{{ $payment->rrr }}</code>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyRrr('{{ $payment->id }}')" title="Copy RRR">
                                        <i class="material-symbols-outlined fs-16 align-middle">content_copy</i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" onclick="makePayment{{ $widgetKey }}()" class="btn btn-warning btn-sm">
                                    <i class="material-symbols-outlined fs-16 align-middle me-1">credit_card</i> Pay Online Now
                                </button>
                                <form id="verify-form{{ $widgetKey }}" action="{{ route('student.payments.verify', ['payment_type_id' => $payment->payment_type_id]) }}" method="GET">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="material-symbols-outlined fs-16 align-middle me-1">verified</i> Verify Payment
                                    </button>
                                </form>
                                <form action="{{ route('student.payments.cancel') }}" method="POST" onsubmit="return confirm('Cancel this pending payment and start a fresh one?');">
                                    @csrf
                                    <input type="hidden" name="payment_type_id" value="{{ $payment->payment_type_id }}">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="material-symbols-outlined fs-16 align-middle me-1">cancel</i> Cancel
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if($hasRequiredDue)
<div class="alert alert-warning mb-4">
    <i class="material-symbols-outlined align-middle me-1">warning</i>
    <strong>Action Required:</strong> You have outstanding required payment(s). Please complete them to proceed.
</div>
@endif

{{-- Student summary card --}}
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    @if($student->passport_photo)
                        <img src="{{ asset('storage/' . $student->passport_photo) }}" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;">
                    @endif
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $student->full_name }}</h5>
                        <div class="text-muted small">
                            {{ $student->registration_number }} &nbsp;|&nbsp; {{ $student->programme->name ?? $student->programme_type }} &nbsp;|&nbsp; {{ $student->academicSession->name ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-{{ $student->is_registered ? 'success' : 'warning' }} fs-13">{{ $student->is_registered ? 'Registered' : 'Not Registered' }}</span>
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
                $pending = $item['pending'] ?? null;
            @endphp
            <div class="col-md-6 mb-4">
                <div class="card border-0 rounded-3 h-100 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="material-symbols-outlined me-1 align-middle text-primary">receipt_long</i>
                            {{ $type->name }}
                        </h5>
                        <div>
                            @if($type->is_required)
                                <span class="badge bg-danger">Required</span>
                            @else
                                <span class="badge bg-secondary">Optional</span>
                            @endif
                            @if($pending && $pending->hasRrr())
                                <span class="badge bg-warning text-dark ms-1">Pending RRR</span>
                            @endif
                        </div>
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
                        @elseif($pending && $pending->hasRrr())
                            @php $widgetKey = 'Card' . str_replace('-', '', $pending->id); @endphp
                            <div class="alert alert-warning mb-3 small">
                                <i class="material-symbols-outlined align-middle me-1">schedule</i>
                                A payment for this fee is pending with RRR <code>{{ $pending->rrr }}</code>. Complete it online, then verify below.
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" onclick="makePayment{{ $widgetKey }}()" class="btn btn-warning btn-sm flex-fill">
                                    <i class="material-symbols-outlined fs-16 align-middle me-1">credit_card</i> Pay Online Now
                                </button>
                                <form id="verify-form{{ $widgetKey }}" action="{{ route('student.payments.verify', ['payment_type_id' => $type->id]) }}" method="GET" class="flex-fill">
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="material-symbols-outlined fs-16 align-middle me-1">verified</i> Verify
                                    </button>
                                </form>
                            </div>
                        @else
                            @if($p['installment'] !== null && $type->split_enabled)
                                <div class="alert alert-info small mb-3">
                                    <i class="material-symbols-outlined align-middle me-1">call_split</i>
                                    Split payment available: {{ $p['installment_label'] }} (&#8358;{{ number_format($p['installment_amount'], 2) }})
                                </div>
                            @endif

                            <form action="{{ route('student.payments.initiate') }}" method="POST" class="mb-2">
                                @csrf
                                <input type="hidden" name="payment_type_id" value="{{ $type->id }}">
                                <input type="hidden" name="mode" value="full">
                                <button type="submit" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                                    <i class="material-symbols-outlined fs-16 align-middle me-1">payments</i>
                                    Pay Full (&#8358;{{ number_format($p['remaining'], 2) }})
                                </button>
                            </form>

                            @if($p['installment'] !== null && $type->split_enabled)
                                <form action="{{ route('student.payments.initiate') }}" method="POST">
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
                            <td>
                                @if($h->rrr)
                                    <code>{{ $h->rrr }}</code>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($h->status === 'successful')
                                    <span class="badge bg-success"><i class="material-symbols-outlined fs-14 align-middle me-1">check_circle</i>Paid</span>
                                @elseif($h->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @elseif($h->status === 'cancelled')
                                    <span class="badge bg-secondary">Cancelled</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
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

@foreach($pendingPayments as $payment)
    @include('partials.remita-pay', ['payment' => $payment, 'widgetKey' => 'Pay' . str_replace('-', '', $payment->id)])
    @include('partials.remita-pay', ['payment' => $payment, 'widgetKey' => 'Card' . str_replace('-', '', $payment->id)])
@endforeach

@push('scripts')
<script>
    function copyRrr(id) {
        var el = document.getElementById('rrr-' + id);
        if (!el) return;
        var text = el.textContent.trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                alert('RRR copied: ' + text);
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            alert('RRR copied: ' + text);
        }
    }
</script>
@endpush
