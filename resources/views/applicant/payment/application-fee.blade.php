@extends('layouts.applicant')
@section('title', 'Application Fee')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Application Fee Payment</h3>
    <a href="{{ route('applicant.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">receipt_long</i>Payment Details</h5>
            </div>
            <div class="card-body p-4">
                @if(!$fee)
                    <div class="alert alert-warning mb-0">
                        <i class="material-symbols-outlined align-middle">warning</i>
                        No application fee has been configured for this session. Please contact admin.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="40%">Applicant Name:</td>
                                    <td class="fw-bold">{{ $applicant->surname }} {{ $applicant->first_name }} {{ $applicant->other_names }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Application Number:</td>
                                    <td>{{ $applicant->application_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Programme Type:</td>
                                    <td><span class="badge bg-primary">{{ $applicant->programme_type }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Academic Session:</td>
                                    <td>{{ $applicant->academicSession->name ?? 'N/A' }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted fs-5">Application Fee:</td>
                                    <td class="fw-bold fs-4 text-success">&#8358;{{ number_format($fee->amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        @if($fee)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">payments</i>Payment Options</h5>
            </div>
            <div class="card-body p-4">
                @if($payment && $payment->hasRrr())
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="material-symbols-outlined me-2 align-middle">info</i>Payment Reference Generated</h6>
                        <p class="mb-0">Your Remita Retrieval Reference (RRR) has been generated. Use it to pay online or at any bank.</p>
                    </div>

                    <div class="bg-light p-4 rounded text-center mb-4">
                        <p class="text-muted mb-2">Your RRR</p>
                        <h2 class="text-primary mb-0" style="letter-spacing: 3px;">{{ $payment->rrr }}</h2>
                        <p class="text-muted mt-2 mb-0">Amount: &#8358;{{ number_format($payment->amount, 2) }}</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" onclick="makePayment()" class="btn btn-success btn-lg w-100">
                                <i class="material-symbols-outlined me-2 align-middle">credit_card</i>Pay Online Now
                            </button>
                        </div>
                        <div class="col-md-6">
                            <form id="verify-form" action="{{ route('applicant.payment.application-fee.verify') }}" method="GET">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="material-symbols-outlined me-2 align-middle">verified</i>Verify Payment
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted">Or pay at any bank with RRR: <strong>{{ $payment->rrr }}</strong></small>
                    </div>
                @else
                    <form action="{{ route('applicant.payment.application-fee.initiate') }}" method="POST">
                        @csrf
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" style="background:#006633;border-color:#006633;">
                                <i class="material-symbols-outlined me-2 align-middle">receipt</i> Generate RRR & Continue
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">help</i>Payment Instructions</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">Click <strong>"Generate RRR"</strong> to get your payment reference number.</li>
                    <li class="mb-2">Use the RRR to pay via:
                        <ul class="mt-1">
                            <li>Online (Card/Bank Transfer) - click <strong>"Pay Online Now"</strong></li>
                            <li>Any commercial bank</li>
                            <li>USSD banking</li>
                        </ul>
                    </li>
                    <li class="mb-2">After payment, click <strong>"Verify Payment"</strong> to confirm.</li>
                    <li>Once verified, you can proceed to fill your application form.</li>
                </ol>
            </div>
        </div>
        <div class="card border-0 rounded-3">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">warning</i>Important Notes</h5>
            </div>
            <div class="card-body">
                <ul class="text-muted small mb-0">
                    <li class="mb-2">Your RRR is unique and should not be shared.</li>
                    <li class="mb-2">RRR does not expire but amount cannot change.</li>
                    <li class="mb-2">Keep your payment receipt for reference.</li>
                    <li>Contact support if payment verification fails after bank confirmation.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@if($payment && $payment->hasRrr())
    @include('partials.remita-pay')
@endif
