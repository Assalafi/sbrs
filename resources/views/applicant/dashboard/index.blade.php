@extends('layouts.applicant')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Welcome, {{ $applicant->first_name }}!</h3>
    <span class="badge bg-primary fs-13 px-3 py-2">{{ $applicant->application_number }}</span>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                @if($applicant->passport_photo)
                    <img src="{{ asset('storage/' . $applicant->passport_photo) }}" alt="Photo" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3 text-white fw-bold" style="width:100px;height:100px;font-size:2rem;">
                        {{ strtoupper(substr($applicant->surname, 0, 1)) }}{{ strtoupper(substr($applicant->first_name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $applicant->surname }} {{ $applicant->first_name }}</h5>
                <p class="text-muted small mb-2">{{ $applicant->email }}</p>
                <span class="badge bg-{{ $applicant->programme_type === 'IJMB' ? 'success' : 'danger' }} mb-2">{{ $applicant->programme_type }}</span>
                <p class="mb-1"><strong>Session:</strong> {{ $applicant->academicSession->name ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Programme:</strong> {{ $applicant->programme->name ?? 'Not selected' }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white pt-4 px-4">
                <h5 class="fw-semibold mb-0">Application Status</h5>
            </div>
            <div class="card-body p-4">
                @php
                    $steps = [
                        'registered' => ['label' => 'Registered', 'icon' => 'person_add'],
                        'form_filling' => ['label' => 'Payment & Form', 'icon' => 'edit_note'],
                        'submitted' => ['label' => 'Submitted', 'icon' => 'send'],
                        'under_review' => ['label' => 'Under Review', 'icon' => 'rate_review'],
                        'approved' => ['label' => 'Approved', 'icon' => 'check_circle'],
                        'admitted' => ['label' => 'Admitted', 'icon' => 'school'],
                    ];
                    $statusOrder = array_keys($steps);
                    $currentIndex = array_search($applicant->status, $statusOrder);
                    if ($applicant->status === 'rejected') $currentIndex = -1;
                @endphp

                @if($applicant->status === 'rejected')
                    <div class="alert alert-danger">
                        <i class="material-symbols-outlined align-middle me-1">error</i>
                        Your application has been <strong>rejected</strong>. Please contact the admin for more information.
                    </div>
                @endif

                <div class="d-flex justify-content-between mb-4 flex-wrap">
                    @foreach($steps as $key => $step)
                    @php $stepIndex = array_search($key, $statusOrder); @endphp
                    <div class="text-center flex-fill px-1" style="min-width:80px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $stepIndex <= $currentIndex ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width:45px;height:45px;">
                            <i class="material-symbols-outlined" style="font-size:1.2rem;">{{ $step['icon'] }}</i>
                        </div>
                        <small class="{{ $stepIndex <= $currentIndex ? 'fw-semibold text-success' : 'text-muted' }}">{{ $step['label'] }}</small>
                    </div>
                    @endforeach
                </div>

                <hr>

                <div class="row g-3">
                    @if(!$applicant->hasPaidApplicationFee())
                    <div class="col-md-6">
                        <a href="{{ route('applicant.payment.application-fee') }}" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                            <i class="material-symbols-outlined fs-16 align-middle">payments</i> Pay Application Fee
                        </a>
                    </div>
                    @endif

                    @if($applicant->hasPaidApplicationFee() && in_array($applicant->status, ['form_filling', 'registered']))
                    <div class="col-md-6">
                        <a href="{{ route('applicant.application.edit') }}" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                            <i class="material-symbols-outlined fs-16 align-middle">edit_note</i> Fill Application Form
                        </a>
                    </div>
                    @endif

                    @if($applicant->status === 'approved')
                    <div class="col-md-6">
                        <a href="{{ route('applicant.payment.admission-fee') }}" class="btn btn-success w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">payments</i> Pay Admission Fee
                        </a>
                    </div>
                    @endif

                    @if($applicant->status === 'admitted')
                    <div class="col-md-6">
                        <a href="{{ route('applicant.admission.letter') }}" class="btn btn-success w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">description</i> View Admission Letter
                        </a>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <a href="{{ route('applicant.password') }}" class="btn btn-outline-secondary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">lock</i> Change Password
                        </a>
                    </div>
                </div>

                @if($applicant->status === 'admitted' && $applicant->student)
                <div class="alert alert-success mt-4">
                    <h6 class="fw-bold mb-2"><i class="material-symbols-outlined align-middle me-1">key</i> Your Student Login Credentials</h6>
                    <p class="mb-1"><strong>Student ID:</strong> <code class="fs-14">{{ $applicant->student->registration_number }}</code></p>
                    <p class="mb-1"><strong>Email:</strong> <code class="fs-14">{{ $applicant->student->email }}</code></p>
                    <p class="mb-1"><strong>Password:</strong> Same as your applicant login password</p>
                    <p class="mb-0 text-muted small">Use these credentials to log in at the <a href="{{ route('student.login') }}">Student Portal</a>.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($applicant->payments->count())
<div class="card border-0 rounded-3">
    <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Payment History</h5></div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Type</th><th>Amount</th><th>RRR</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach($applicant->payments as $payment)
                    <tr>
                        <td>{{ ucfirst($payment->payment_type) }}</td>
                        <td class="fw-medium">&#8358;{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ $payment->rrr ?? 'N/A' }}</td>
                        <td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($payment->status) }}</span></td>
                        <td class="fs-13 text-muted">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
