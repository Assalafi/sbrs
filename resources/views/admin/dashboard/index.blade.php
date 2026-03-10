@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Dashboard</h3>
    <span class="badge bg-primary fs-13 px-3 py-2">{{ $currentSession->name ?? 'No Active Session' }}</span>
</div>

<div class="row mb-4">
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card bg-card-one h-100 border-0 rounded-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13 text-uppercase text-muted">Total Applicants</span>
                        <h2 class="fw-bold mb-0 fs-28">{{ number_format($stats['total_applicants']) }}</h2>
                    </div>
                    <div class="wh-55 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="material-symbols-outlined text-primary fs-24">group_add</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card bg-card-one h-100 border-0 rounded-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13 text-uppercase text-muted">Pending Review</span>
                        <h2 class="fw-bold mb-0 fs-28">{{ number_format($stats['pending_applications']) }}</h2>
                    </div>
                    <div class="wh-55 bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="material-symbols-outlined text-warning fs-24">pending</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card bg-card-one h-100 border-0 rounded-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13 text-uppercase text-muted">Approved</span>
                        <h2 class="fw-bold mb-0 fs-28">{{ number_format($stats['approved_applications']) }}</h2>
                    </div>
                    <div class="wh-55 bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="material-symbols-outlined text-success fs-24">check_circle</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card bg-card-one h-100 border-0 rounded-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13 text-uppercase text-muted">Total Revenue</span>
                        <h2 class="fw-bold mb-0 fs-22">&#8358;{{ number_format($stats['total_payments'], 2) }}</h2>
                    </div>
                    <div class="wh-55 bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="material-symbols-outlined text-info fs-24">payments</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                <i class="material-symbols-outlined text-primary mb-2" style="font-size:2rem;">school</i>
                <h5 class="fw-bold">{{ number_format($stats['total_students']) }}</h5>
                <span class="text-muted fs-13">Active Students</span>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                <i class="material-symbols-outlined text-success mb-2" style="font-size:2rem;">eco</i>
                <h5 class="fw-bold">{{ number_format($stats['ijmb_applicants']) }}</h5>
                <span class="text-muted fs-13">IJMB Applicants</span>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                <i class="material-symbols-outlined text-danger mb-2" style="font-size:2rem;">biotech</i>
                <h5 class="fw-bold">{{ number_format($stats['remedial_applicants']) }}</h5>
                <span class="text-muted fs-13">Remedial Applicants</span>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                <i class="material-symbols-outlined text-warning mb-2" style="font-size:2rem;">fact_check</i>
                <h5 class="fw-bold">{{ number_format($stats['pending_screening']) }}</h5>
                <span class="text-muted fs-13">Pending Screening</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">Recent Submitted Applications</h5>
            </div>
            <div class="card-body px-4">
                @if($recentApplications->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="fs-13">Applicant</th>
                                <th class="fs-13">Type</th>
                                <th class="fs-13">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentApplications as $app)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $app->surname }} {{ $app->first_name }}</span><br>
                                    <small class="text-muted">{{ $app->application_number }}</small>
                                </td>
                                <td><span class="badge {{ $app->programme_type === 'IJMB' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $app->programme_type === 'IJMB' ? 'text-success' : 'text-danger' }}">{{ $app->programme_type }}</span></td>
                                <td class="fs-13 text-muted">{{ $app->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-4">No pending applications.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">Recent Payments</h5>
            </div>
            <div class="card-body px-4">
                @if($recentPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="fs-13">RRR</th>
                                <th class="fs-13">Type</th>
                                <th class="fs-13">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                            <tr>
                                <td class="fw-medium">{{ $payment->rrr ?? 'N/A' }}</td>
                                <td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($payment->payment_type) }}</span></td>
                                <td class="fw-medium">&#8358;{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-4">No recent payments.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
