@extends('layouts.applicant')
@section('title', 'Admission Payment Confirmation')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Admission Payment Confirmation</h3>
    <a href="{{ route('applicant.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">
    <i class="material-symbols-outlined me-2 align-middle">check_circle</i>
    {{ session('success') }}
</div>
@endif

@if($applicant->student)
<div class="alert alert-info mb-4">
    <h6 class="fw-bold mb-2"><i class="material-symbols-outlined align-middle me-1">key</i> Your Student Login Credentials</h6>
    <p class="mb-1"><strong>Student ID:</strong> <code class="fs-14">{{ $applicant->student->registration_number }}</code></p>
    <p class="mb-1"><strong>Email:</strong> <code class="fs-14">{{ $applicant->student->email }}</code></p>
    <p class="mb-1"><strong>Password:</strong> Same as your applicant login password</p>
    <p class="mb-0 text-muted small">Use these to log in at the <a href="{{ route('student.login') }}">Student Portal</a>.</p>
</div>
@endif

<div class="card border-0 rounded-3">
    <div class="card-header bg-primary text-white" style="background:#006633;">
        <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">campaign</i>NOTICE TO ADMITTED STUDENTS</h5>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-warning mb-4">
            <h6 class="fw-bold mb-2"><i class="material-symbols-outlined align-middle me-1">receipt_long</i> Important Instructions</h6>
            <p class="mb-0">Congratulations on your admission! Please read the instructions below carefully to complete your registration process.</p>
        </div>

        <div class="mb-4">
            <ol class="mb-0 ps-3">
                <li class="mb-3">
                    <strong class="d-block mb-2">Bursary Verification</strong>
                    <p class="mb-0">Students who have successfully paid their acceptance fee on the portal are advised to print their Remita receipt and take it to the Bursary for verification. After verification, they should proceed to the School of Basic and Remedial Studies to collect their admission letter.</p>
                </li>
                <li class="mb-0">
                    <strong class="d-block mb-2">Registration Schedule</strong>
                    <p class="mb-0">Registration commences on <strong>Monday, 8th June, 2026</strong>.</p>
                </li>
            </ol>
        </div>

        <div class="bg-light p-4 rounded">
            <h6 class="fw-bold mb-3"><i class="material-symbols-outlined align-middle me-1">info</i> Next Steps</h6>
            <ul class="mb-0">
                <li class="mb-2">Print your Remita receipt from the payment history section</li>
                <li class="mb-2">Take the receipt to the Bursary for verification</li>
                <li class="mb-2">Proceed to School of Basic and Remedial Studies to collect your admission letter</li>
                <li class="mb-0">Complete registration on or after Monday, 8th June, 2026</li>
            </ul>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('applicant.dashboard') }}" class="btn btn-primary" style="background:#006633;border-color:#006633;">
                <i class="material-symbols-outlined me-2 align-middle">dashboard</i> Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
