@extends('layouts.student')
@section('title', 'Admission Letter')

@php
    $session = $applicant->academicSession;
    $programme = $applicant->programme->name ?? ($applicant->programme_type === 'IJMB' ? 'IJMB' : 'Remedial Science');
    $programmeLabel = $applicant->programme_type === 'IJMB' ? 'One-Year IJMB Programme' : 'One-Year Remedial Programme';
    $fullName = trim($applicant->surname . ' ' . $applicant->first_name . ' ' . $applicant->other_names);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <div class="text-uppercase small text-muted fw-semibold">Student services</div>
        <h1 class="h3 mb-1">Your admission letter</h1>
        <p class="text-muted mb-0">Download the official SBRS letter for your records and registration.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('student.admission.download') }}" class="btn btn-primary" style="color:#fff !important;"><span class="material-symbols-outlined align-middle me-1">download</span>Download PDF</a>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary"><span class="material-symbols-outlined align-middle me-1">arrow_back</span>Dashboard</a>
    </div>
</div>

<div class="alert alert-success d-flex gap-2 align-items-start mb-4">
    <span class="material-symbols-outlined">verified</span>
    <div><strong>Admission confirmed.</strong><br><span class="small">Keep this letter and present it with your payment evidence and original documents during registration.</span></div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center border-bottom pb-3 mb-4">
            <div class="small text-uppercase text-muted">Office of the Vice-Chancellor</div>
            <h2 class="h4 fw-bold mb-1">UNIVERSITY OF MAIDUGURI</h2>
            <div class="fw-semibold text-success">SCHOOL OF BASIC AND REMEDIAL STUDIES</div>
            <div class="small text-muted">P.M.B. 1069, Maiduguri, Borno State, Nigeria</div>
        </div>

        <div class="row g-2 small mb-4">
            <div class="col-sm-6"><strong>Applicant:</strong> {{ $fullName }}</div>
            <div class="col-sm-6"><strong>Student ID:</strong> {{ $student->registration_number }}</div>
            <div class="col-sm-6"><strong>Programme:</strong> {{ $programmeLabel }}</div>
            <div class="col-sm-6"><strong>Session:</strong> {{ $session->name ?? '—' }}</div>
        </div>

        <h3 class="h5 text-center text-uppercase text-success text-decoration-underline mb-4">Provisional Admission into {{ $programmeLabel }}</h3>
        <p style="text-align:justify">You have been provisionally admitted into the {{ $programmeLabel }}{{ $programme ? ' (' . $programme . ')' : '' }} at the School of Basic and Remedial Studies, University of Maiduguri, for the {{ $session->name ?? 'current' }} Academic Session, subject to fulfilment of the stated conditions.</p>
        <h4 class="h6 fw-bold text-decoration-underline mt-4">Conditions for admission</h4>
        <ol class="small ps-4">
            <li>Pay the acceptance and tuition fees stipulated by the School.</li>
            <li>Submit original and certified academic documents, including O'Level results.</li>
            <li>Submit a primary school certificate and an indigene letter or certificate of birth.</li>
            <li>Comply with all other University regulations.</li>
        </ol>
        <h4 class="h6 fw-bold text-decoration-underline mt-4">Registration</h4>
        <p class="small">Report with this admission letter, evidence of payment, original certificates and a JAMB result slip where applicable.</p>
        <p class="small">Congratulations on your admission. We look forward to welcoming you.</p>
        <div class="mt-5 small"><div>Yours sincerely,</div><div style="height:35px"></div><strong>{{ setting('director_name', 'Professor Abdulkarim Ishaq') }}</strong><br><em>{{ setting('director_title', 'Director, School of Basic and Remedial Studies') }}</em></div>
    </div>
</div>
@endsection
