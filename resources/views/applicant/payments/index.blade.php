@extends('layouts.applicant')
@section('title', 'Payments')

@push('styles')
<style>
    .applicant-payments-page{max-width:1180px;margin:0 auto;padding-bottom:2rem}
    .payments-page-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.25rem}
    .payments-page-head h1{font-size:clamp(1.35rem,2.4vw,2rem);font-weight:750;letter-spacing:-.02em;color:#123b31;margin:0}
    .payments-page-head p{margin:.25rem 0 0;color:#6b7e78;font-size:.93rem}
    .payments-hero{border:0;border-radius:1.15rem;background:linear-gradient(120deg,#0b4f3a,#087f5b);color:#fff;box-shadow:0 14px 32px rgba(5,76,55,.16);overflow:hidden;position:relative}
    .payments-hero:after{content:"";position:absolute;width:220px;height:220px;border-radius:50%;right:-80px;top:-100px;background:rgba(255,255,255,.1)}
    .payments-hero .card-body{position:relative;z-index:1;padding:1.35rem 1.5rem}
    .payments-hero .kicker{font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:#b7e9d7;font-weight:700}
    .payments-hero h2{font-size:clamp(1.35rem,3vw,2rem);margin:.35rem 0 .25rem;color:#fff;font-weight:750}
    .payments-hero p{margin:0;color:#e2f5ed;max-width:650px}
    .payment-profile-card,.payment-due-card,.payment-history-card{border:1px solid #e4eee9!important;border-radius:1rem!important;box-shadow:0 8px 24px rgba(21,56,45,.06)!important;background:#fff}
    .payment-profile-card{margin-top:1rem}
    .payment-profile-card .card-body{padding:1.1rem 1.25rem}
    .payment-avatar{width:54px;height:54px;border-radius:50%;object-fit:cover;border:3px solid #e5f4ee;background:#edf8f3;display:grid;place-items:center;color:#087f5b}
    .payment-profile-name{color:#153d32;font-weight:750}
    .payment-profile-meta{color:#71817b;font-size:.82rem;line-height:1.5}
    .payment-status{border-radius:999px;padding:.42rem .75rem;font-size:.74rem;font-weight:700}
    .payment-alert{border:0;border-left:4px solid #d99a16;background:#fff8e8;color:#6c4d09;border-radius:.75rem}
    .payment-due-card{height:100%;overflow:hidden}
    .payment-due-card .card-header{background:#fbfdfc;border-bottom:1px solid #e8f0ec;padding:1rem 1.1rem}
    .payment-due-card .card-header h5{font-size:1rem;color:#173e33;font-weight:750}
    .payment-due-card .card-body{padding:1.1rem}
    .payment-due-card .description{color:#72817b;min-height:2.4rem}
    .payment-metric{background:#f5f9f7;border:1px solid #e7f0eb;border-radius:.75rem;padding:.65rem .35rem;height:100%}
    .payment-metric .amount{font-size:clamp(.85rem,2vw,1.1rem);font-weight:800;white-space:nowrap}
    .payment-metric small{font-size:.68rem;color:#72817b;font-weight:600}
    .payment-history-card{margin-top:1.25rem;overflow:hidden}
    .payment-history-card .card-header{background:#fbfdfc;border-bottom:1px solid #e8f0ec;padding:1rem 1.15rem}
    .payment-history-card .card-header h5{color:#173e33;font-weight:750}
    .payment-history-card table{margin:0;min-width:680px}
    .payment-history-card thead th{background:#f3f8f5;color:#5c7169;border:0;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em}
    .payment-history-card tbody td{border-color:#edf2ef;color:#304a41;font-size:.86rem}
    .payment-history-card .table-responsive{margin:-.25rem -.25rem}
    .payment-clean-pending{border:1px solid #f0dfae;border-radius:1rem;background:#fffdf6;box-shadow:0 8px 24px rgba(116,87,20,.06);margin-bottom:1.25rem;overflow:hidden}
    .payment-clean-pending-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.05rem 1.2rem;border-bottom:1px solid #f3e8c8;background:#fff9e9}
    .payment-clean-pending-head h2{margin:0;color:#5b4309;font-size:1.05rem;font-weight:800}
    .payment-clean-pending-head p{margin:.25rem 0 0;color:#806c37;font-size:.86rem}
    .payment-clean-pending-list{padding:.8rem 1rem}
    .payment-clean-pending-item{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 0;border-bottom:1px solid #f3ead2}
    .payment-clean-pending-item:last-child{border-bottom:0}
    .payment-clean-pending-main{display:flex;align-items:flex-start;gap:.75rem;min-width:0}
    .payment-clean-pending-icon{width:2.5rem;height:2.5rem;border-radius:.8rem;display:grid;place-items:center;background:#fff0c5;color:#9b700d;flex:0 0 auto}
    .payment-clean-pending-main h3{font-size:.97rem;color:#43350f;margin:0 0 .2rem;font-weight:800}
    .payment-clean-pending-main p{margin:0;color:#77683e;font-size:.8rem}
    .payment-clean-rrr{display:flex;align-items:center;gap:.45rem;margin-top:.45rem;font-size:.76rem;color:#786d4e}
    .payment-clean-rrr code{font-size:.8rem;color:#3e351e;background:#fff4d5;border:1px solid #ead8a4;border-radius:.35rem;padding:.2rem .4rem}
    .payment-clean-rrr button{border:0;background:transparent;color:#755e20;padding:0;line-height:1}
    .payment-clean-pending-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.45rem;flex:0 0 auto}
    .payment-clean-pending-actions form{margin:0}
    .payment-clean-pending-actions .btn{display:inline-flex;align-items:center;gap:.25rem;font-size:.78rem;font-weight:700;border-radius:.55rem}
    .payment-step-note{display:flex;align-items:flex-start;gap:.45rem;margin-bottom:.75rem;padding:.65rem .75rem;border-radius:.65rem;background:#eff8ff;color:#315d7d;font-size:.79rem}
    .payment-inline-pending{display:flex;align-items:flex-start;gap:.5rem;padding:.7rem .75rem;margin-bottom:.8rem;border:1px solid #f0dfae;border-radius:.65rem;background:#fff9e9;color:#6e571b;font-size:.8rem}
    .payment-inline-pending code{color:#493b16;background:#fff0bd;padding:.15rem .35rem;border-radius:.3rem}
    .payment-action-row{display:flex;flex-wrap:wrap;gap:.5rem}
    .payment-action-row form{flex:1 1 180px;margin:0}
    .payment-action-row .btn{width:100%;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:.25rem}
    .payment-empty{border:1px dashed #cfe2d8;background:#fbfefc;border-radius:1rem;padding:2rem;text-align:center;color:#60766b}
    .payment-empty .material-symbols-outlined{font-size:2.5rem;color:#16845e}
.payment-registration-success{display:flex;align-items:center;gap:.75rem;margin:0 0 1.1rem;padding:1rem 1.1rem;border:1px solid #bde4cf;border-radius:.9rem;background:#effbf4;color:#145c3d}
.payment-registration-success .material-symbols-outlined{font-size:2rem;color:#16845e}
.payment-registration-success strong{display:block;color:#124d37}
.payment-registration-success code{color:#0b5e3e;background:#dcf5e7;border:1px solid #b9e5cc;padding:.18rem .4rem;border-radius:.3rem}
    .payment-toast{position:fixed;right:1rem;bottom:1rem;z-index:1080;max-width:min(420px,calc(100vw - 2rem));box-shadow:0 12px 30px rgba(20,45,35,.15)}
    @media (max-width:600px){.applicant-payments-page{padding:0 .1rem 1.25rem}.payments-page-head{align-items:flex-start}.payments-page-head .btn{white-space:nowrap}.payments-hero .card-body{padding:1.15rem}.payment-profile-card .card-body{padding:1rem}.payment-profile-meta{font-size:.76rem}.payment-due-card .card-body{padding:.9rem}.payment-metric{padding:.55rem .15rem}.payment-metric .amount{font-size:.78rem}.payment-history-card .card-body{padding:.85rem}}
    @media (max-width:700px){.payment-clean-pending-head{padding:.95rem}.payment-clean-pending-list{padding:.65rem .85rem}.payment-clean-pending-item{align-items:stretch;flex-direction:column}.payment-clean-pending-actions{width:100%;justify-content:stretch}.payment-clean-pending-actions form,.payment-clean-pending-actions .btn{flex:1 1 0}.payment-clean-pending-actions .btn{width:100%;justify-content:center}.payment-action-row{display:grid;grid-template-columns:1fr}.payment-action-row form{flex:auto}}
</style>
@endpush

@section('content')
@php
    $pendingPayments = $pendingPayments ?? collect();
    $items = $items ?? collect();
    $history = $history ?? collect();
@endphp
<div class="applicant-payments-page">
<div class="payments-page-head">
    <div><h1>Payments</h1><p>Review your fees, complete a payment and keep your receipts.</p></div>
    <a href="{{ route('applicant.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

@if($hasRequiredDue)
<div class="alert payment-alert mb-4">
    <i class="material-symbols-outlined align-middle me-1">warning</i>
    <strong>Action Required:</strong> You have outstanding required payment(s). Please complete them to proceed.
</div>
@endif

@if($applicant->student)
<div class="payment-registration-success" role="status">
    <i class="material-symbols-outlined">verified</i>
    <div><strong>Registration complete</strong>Your student account is ready. Student ID: <code>{{ $applicant->student->registration_number }}</code></div>
</div>
@endif

@if($pendingPayments->isNotEmpty())
<section class="payment-clean-pending" aria-labelledby="pending-payments-heading">
    <div class="payment-clean-pending-head">
        <div>
            <h2 id="pending-payments-heading"><i class="material-symbols-outlined align-middle me-1">pending_actions</i>Payment waiting for action</h2>
            <p>Continue the payment, verify it after paying, or cancel it to start again. You can only have one pending payment for the same fee.</p>
        </div>
        <span class="badge rounded-pill bg-warning text-dark">{{ $pendingPayments->count() }}</span>
    </div>
    <div class="payment-clean-pending-list">
        @foreach($pendingPayments as $payment)
            @php $widgetKey = 'ApplicantPay' . str_replace('-', '', $payment->id); @endphp
            <article class="payment-clean-pending-item">
                <div class="payment-clean-pending-main">
                    <span class="payment-clean-pending-icon"><i class="material-symbols-outlined">schedule</i></span>
                    <div>
                        <h3>{{ $payment->paymentType->name ?? ucwords(str_replace('_', ' ', $payment->payment_type)) }}</h3>
                        <p>&#8358;{{ number_format($payment->gross_amount, 2) }} &middot; Started {{ optional($payment->created_at)->format('M d, Y H:i') }}</p>
                        <div class="payment-clean-rrr">
                            <span>RRR</span>
                            <code id="applicant-rrr-{{ $payment->id }}">{{ $payment->rrr ?: 'Not generated yet' }}</code>
                            @if($payment->rrr)
                                <button type="button" onclick="copyApplicantRrr('{{ $payment->id }}')" aria-label="Copy RRR"><i class="material-symbols-outlined fs-16">content_copy</i></button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="payment-clean-pending-actions">
                    @if($payment->hasRrr())
                        <button type="button" class="btn btn-warning" onclick="launchApplicantPayment(this, makePayment{{ $widgetKey }})"><i class="material-symbols-outlined fs-16">credit_card</i>Pay online</button>
                        <form id="verify-form{{ $widgetKey }}" action="{{ route('applicant.payments.verify') }}" method="GET" onsubmit="return paymentAction(this, 'Checking payment status…');">
                            <input type="hidden" name="payment_type_id" value="{{ $payment->payment_type_id }}">
                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                            <button type="submit" class="btn btn-success"><i class="material-symbols-outlined fs-16">verified</i>Verify payment</button>
                        </form>
                    @else
                        <span class="btn btn-outline-secondary disabled"><i class="material-symbols-outlined fs-16">hourglass_empty</i>RRR being prepared</span>
                    @endif
                    <form action="{{ route('applicant.payments.cancel') }}" method="POST" onsubmit="return confirmApplicantCancel(this);">
                        @csrf
                        <input type="hidden" name="payment_type_id" value="{{ $payment->payment_type_id }}">
                        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                        <button type="submit" class="btn btn-outline-danger"><i class="material-symbols-outlined fs-16">cancel</i>Cancel</button>
                    </form>
                </div>
            </article>
            @include('partials.remita-pay', ['payment' => $payment, 'widgetKey' => $widgetKey])
        @endforeach
    </div>
</section>
@endif

{{-- Applicant summary card --}}
<div class="card payment-profile-card mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    @if($applicant->passport_photo)
                        <img src="{{ asset('storage/' . $applicant->passport_photo) }}" class="payment-avatar" alt="">
                    @else
                        <span class="payment-avatar"><i class="material-symbols-outlined">person</i></span>
                    @endif
                    <div>
                        <h5 class="mb-0 payment-profile-name">{{ $applicant->full_name }}</h5>
                        <div class="payment-profile-meta">
                            {{ $applicant->application_number }} &nbsp;|&nbsp; {{ $applicant->programme->name ?? $applicant->programme_type }} &nbsp;|&nbsp; {{ $applicant->academicSession->name ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="payment-status bg-{{ $applicant->status === 'admitted' ? 'success' : 'warning' }} text-{{ $applicant->status === 'admitted' ? 'white' : 'dark' }}">{{ ucfirst(str_replace('_', ' ', $applicant->status)) }}</span>
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
                <div class="card payment-due-card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
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
                            <p class="description small mb-3">{{ $type->description }}</p>
                        @endif

                        <div class="row text-center mb-3 g-2">
                            <div class="col-4">
                                <div class="payment-metric">
                                    <div class="amount text-success">&#8358;{{ number_format($p['full_amount'], 2) }}</div>
                                    <small class="text-muted">Total Fee</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="payment-metric">
                                    <div class="amount text-primary">&#8358;{{ number_format($p['paid'], 2) }}</div>
                                    <small class="text-muted">Paid</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="payment-metric">
                                    <div class="amount text-warning">&#8358;{{ number_format($p['remaining'], 2) }}</div>
                                    <small class="text-muted">Remaining</small>
                                </div>
                            </div>
                        </div>

                        @if($p['fully_paid'])
                            <div class="alert alert-success mb-0 text-center">
                                <i class="material-symbols-outlined align-middle me-1">check_circle</i> Fully Paid
                            </div>
                        @elseif($pending && $pending->hasRrr())
                            @php $cardWidgetKey = 'ApplicantCard' . str_replace('-', '', $pending->id); @endphp
                            <div class="payment-inline-pending">
                                <i class="material-symbols-outlined">schedule</i>
                                <span>You already started this payment. Finish it with RRR <code>{{ $pending->rrr }}</code>, then verify it below.</span>
                            </div>
                            <div class="payment-action-row">
                                <button type="button" class="btn btn-warning" onclick="launchApplicantPayment(this, makePayment{{ $cardWidgetKey }})"><i class="material-symbols-outlined fs-16">credit_card</i>Pay online</button>
                                <form id="verify-form{{ $cardWidgetKey }}" action="{{ route('applicant.payments.verify') }}" method="GET" onsubmit="return paymentAction(this, 'Checking payment status…');">
                                    <input type="hidden" name="payment_type_id" value="{{ $pending->payment_type_id }}">
                                    <input type="hidden" name="payment_id" value="{{ $pending->id }}">
                                    <button type="submit" class="btn btn-success"><i class="material-symbols-outlined fs-16">verified</i>Verify</button>
                                </form>
                                <form action="{{ route('applicant.payments.cancel') }}" method="POST" onsubmit="return confirmApplicantCancel(this);">
                                    @csrf
                                    <input type="hidden" name="payment_type_id" value="{{ $pending->payment_type_id }}">
                                    <input type="hidden" name="payment_id" value="{{ $pending->id }}">
                                    <button type="submit" class="btn btn-outline-danger"><i class="material-symbols-outlined fs-16">cancel</i>Cancel</button>
                                </form>
                            </div>
                            @include('partials.remita-pay', ['payment' => $pending, 'widgetKey' => $cardWidgetKey])
                        @else
                            @if($p['installment'] !== null && $type->split_enabled)
                                <div class="alert alert-info small mb-3">
                                    <i class="material-symbols-outlined align-middle me-1">call_split</i>
                                    Split payment available: {{ $p['installment_label'] }} (&#8358;{{ number_format($p['installment_amount'], 2) }})
                                </div>
                            @endif

                            <div class="payment-step-note"><i class="material-symbols-outlined">info</i><span>Click pay to create an RRR. After it appears, pay online, then return here and choose <strong>Verify</strong>.</span></div>
                            <form action="{{ route('applicant.payments.initiate') }}" method="POST" class="mb-2" onsubmit="return paymentAction(this, 'Generating your payment reference…');">
                                @csrf
                                <input type="hidden" name="payment_type_id" value="{{ $type->id }}">
                                <input type="hidden" name="mode" value="full">
                                <button type="submit" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                                    <i class="material-symbols-outlined fs-16 align-middle me-1">payments</i>
                                    Pay Full (&#8358;{{ number_format($p['remaining'], 2) }})
                                </button>
                            </form>

                            @if($p['installment'] !== null && $type->split_enabled)
                                <form action="{{ route('applicant.payments.initiate') }}" method="POST" onsubmit="return paymentAction(this, 'Generating your payment reference…');">
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
<div class="card payment-history-card mt-2">
    <div class="card-header">
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
                                @elseif($h->status === 'cancelled')
                                    <span class="badge bg-secondary">Cancelled</span>
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
</div>
@endsection

@push('scripts')
<script>
    function launchApplicantPayment(button, opener) {
        if (!button || button.disabled) return;
        var original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Opening secure payment…';
        try {
            opener();
        } catch (error) {
            button.disabled = false;
            button.innerHTML = original;
            window.alert('The payment window could not be opened. Please try again.');
            return;
        }
        window.setTimeout(function () {
            button.disabled = false;
            button.innerHTML = original;
        }, 1200);
    }

    function paymentAction(form, message) {
        var button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + message;
        }
        return true;
    }

    function confirmApplicantCancel(form) {
        if (!window.confirm('Cancel this pending payment? You can start a new payment afterwards.')) return false;
        return paymentAction(form, 'Cancelling…');
    }

    function copyApplicantRrr(id) {
        var node = document.getElementById('applicant-rrr-' + id);
        if (!node) return;
        var value = node.textContent.trim();
        var done = function () { window.alert('RRR copied: ' + value); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value).then(done).catch(function () { fallbackCopy(value, done); });
        } else {
            fallbackCopy(value, done);
        }
    }

    function fallbackCopy(value, done) {
        var input = document.createElement('textarea');
        input.value = value;
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        input.remove();
        done();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[action*="payments/initiate"]').forEach(function (form) {
            form.addEventListener('submit', function () { paymentAction(form, 'Generating…'); });
        });
    });
</script>
@endpush
