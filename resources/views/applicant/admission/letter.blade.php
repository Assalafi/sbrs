@extends('layouts.applicant')
@section('title', 'Admission Letter')

@php
    $session = $applicant->academicSession;
    $student = $applicant->student;
    $progName = $applicant->programme_type === 'IJMB'
        ? 'One-Year IJMB Programme'
        : 'One-Year Remedial Programme (Arts/Science)';
    $year2 = $session ? substr($session->name, 2, 2) : substr(date('Y'), 2, 2);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Admission Letter</h3>
    <div>
        <a href="{{ route('applicant.admission.download') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
            <i class="material-symbols-outlined fs-16 align-middle">download</i> Download PDF
        </a>
        <a href="{{ route('applicant.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
    </div>
</div>

@if($student)
<div class="alert alert-success mb-3">
    <h6 class="fw-bold mb-2"><i class="material-symbols-outlined align-middle me-1">key</i> Your Student Login Credentials</h6>
    <p class="mb-1"><strong>Student ID:</strong> <code class="fs-14">{{ $student->registration_number }}</code></p>
    <p class="mb-1"><strong>Email:</strong> <code class="fs-14">{{ $student->email }}</code></p>
    <p class="mb-1"><strong>Password:</strong> Same as your applicant login password</p>
    <p class="mb-0 text-muted small">Use these to log in at the <a href="{{ route('student.login') }}">Student Portal</a>.</p>
</div>
@endif

<div class="card border-0 rounded-3">
    <div class="card-body p-5" style="font-family: 'Times New Roman', Times, serif; color: #000;">
        <div class="text-center mb-2 pb-3" style="border-bottom: 2px solid #000;">
            <h4 class="fw-bold mb-0" style="letter-spacing:1px;">UNIVERSITY OF MAIDUGURI</h4>
            <small>(OFFICE OF THE VICE-CHANCELLOR)</small>
            <h5 class="fw-bold mt-1 mb-0">SCHOOL OF BASIC AND REMEDIAL STUDIES</h5>
            <p class="mb-0">P.M.B. 1069</p>
            <small><strong>{{ setting('director_name', 'PROFESSOR ABDULKARIM ISHAQ') }}</strong>, {{ setting('director_title', 'B.A Ed, M.Ed (Curriculum & Instruction), Ph.D') }}</small><br>
            <small>&#9993; {{ setting('director_email', 'sbrs@unimaid.edu.ng') }} &nbsp; &#9742; {{ setting('director_phone', '+2348035837228') }}</small>
        </div>

        <div class="mt-3 mb-3">
            <p class="float-end mb-0"><strong>{{ now()->format('jS M. Y') }}</strong></p>
            <p class="mb-0" style="clear:left;"><strong>SBRS.{{ $year2 }}/VOL.II</strong></p>
            <p class="mb-0">{{ strtoupper($applicant->full_name) }}</p>
            @if($student)
            <p class="mb-0"><strong>{{ $student->registration_number }}</strong></p>
            @endif
        </div>

        <h5 class="text-center fw-bold mt-3 mb-3" style="text-decoration:underline;">PROVISIONAL ADMISSION INTO {{ strtoupper($progName) }}</h5>

        <p style="text-align:justify;">I am pleased to inform you that you have been provisionally admitted into the <strong>{{ $progName }}</strong> in {{ $applicant->programme->name ?? 'Science' }} at the School of Basic and Remedial Studies, University of Maiduguri, for the <strong>{{ $session->name ?? '' }}</strong> Academic Session, subject to fulfillment of the conditions outlined below.</p>

        <p class="fw-bold mb-1"><u>Admission Details:</u></p>
        <div class="ps-3 mb-3">
            <p class="mb-0">- Programme: {{ $progName }}</p>
            <p class="mb-0">- School: Basic and Remedial Studies</p>
            <p class="mb-0">- Duration: One Academic Session</p>
            <p class="mb-0">- Entry Requirements: O'Level results with at least 5 credits, including English and Mathematics</p>
        </div>

        <p class="fw-bold mb-1"><u>Conditions for Admission:</u></p>
        <div class="ps-4 mb-3">
            <p class="mb-0">1. Payment of Acceptance Fee/ Tuition Fees as stipulated by the School of Basic and Remedial Studies</p>
            <p class="mb-0">2. Submission of original and certified copies of academic documents (O'Level)</p>
            <p class="mb-0">3. Primary school certificate</p>
            <p class="mb-0">4. Indigene letter/ Certificate of Birth</p>
            <p class="mb-0">5. Compliance with other University regulations.</p>
        </div>

        <p class="fw-bold mb-1"><u>Registration:</u></p>
        <p class="mb-1">Registration for the programme will take place immediately after the conclusion of admissions. You are required to report with the following documents:</p>
        <div class="ps-3 mb-3">
            <p class="mb-0">- Admission letter</p>
            <p class="mb-0">- Evidence of payment/original certificates</p>
            <p class="mb-0">- JAMB result slip if any</p>
        </div>

        <p style="text-align:justify;">Please confirm your acceptance and comply with the admission conditions at the earliest convenience. Congratulations on your admission. We look forward to your registration.</p>

        <div class="mt-5">
            <p class="mb-0">Yours Sincerely,</p>
            <br>
            <p class="mb-0"><strong>{{ setting('director_name', 'Professor Abdulkarim Ishaq') }}</strong></p>
            <p class="mb-0"><em>Director</em></p>
        </div>
    </div>
</div>
@endsection
