@extends('layouts.student')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="student-page-heading">
    <div>
        <h1>Your student dashboard</h1>
        <p>Keep track of your registration, payments and academic progress.</p>
    </div>
</div>

@php
    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->surname ?? '')) ?: 'Student';
    $initials = strtoupper(substr($student->first_name ?? 'S', 0, 1) . substr($student->surname ?? '', 0, 1));
    $paidPayments = $student->payments->where('status', 'successful')->count();
    $registeredCourses = $student->courseRegistrations->count();
    $resultCount = $student->results->count();
@endphp

<section class="student-dashboard-hero">
    <div class="student-dashboard-identity">
        <div class="student-dashboard-avatar">
            @if($student->passport_photo)
                <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Profile photo">
            @else
                {{ $initials }}
            @endif
        </div>
        <div class="student-dashboard-copy">
            <p class="kicker">Welcome back</p>
            <h1>{{ $studentName }}</h1>
            <p>{{ $student->registration_number }} · {{ $student->programme->name ?? 'Programme not assigned' }}</p>
            <div class="student-dashboard-meta">
                <span>{{ $student->programme_type ?? 'Student' }}</span>
                <span>{{ $student->academicSession->name ?? 'Current session' }}</span>
                <span>{{ $student->is_registered ? 'Registration complete' : 'Registration pending' }}</span>
            </div>
        </div>
    </div>
    <div class="student-dashboard-hero-action">
        <a href="{{ route('student.courses.index') }}" class="btn d-inline-flex align-items-center gap-2"><span class="material-symbols-outlined">menu_book</span>Course registration</a>
    </div>
</section>

<section class="student-stat-grid" aria-label="Student summary">
    <div class="student-stat-card"><span class="student-stat-icon"><span class="material-symbols-outlined">menu_book</span></span><span><span class="student-stat-value">{{ $registeredCourses }}</span><span class="student-stat-label">Courses registered</span></span></div>
    <div class="student-stat-card"><span class="student-stat-icon"><span class="material-symbols-outlined">payments</span></span><span><span class="student-stat-value">{{ $paidPayments }}</span><span class="student-stat-label">Successful payments</span></span></div>
    <div class="student-stat-card"><span class="student-stat-icon"><span class="material-symbols-outlined">grade</span></span><span><span class="student-stat-value">{{ $resultCount }}</span><span class="student-stat-label">Results available</span></span></div>
    <div class="student-stat-card"><span class="student-stat-icon"><span class="material-symbols-outlined">event</span></span><span><span class="student-stat-value" style="font-size:.9rem">{{ $student->academicSession->name ?? '—' }}</span><span class="student-stat-label">Academic session</span></span></div>
</section>

<div class="student-dashboard-grid">
    <section class="student-panel">
        <div class="student-panel-header"><div><h2>Academic profile</h2><p>Your current student record at a glance.</p></div><span class="material-symbols-outlined" style="color:var(--sp-primary)">school</span></div>
        <div class="student-panel-body">
            <div class="student-detail-grid">
                <div class="student-detail-item"><small>Registration number</small><strong>{{ $student->registration_number ?: 'Not provided' }}</strong></div>
                <div class="student-detail-item"><small>Programme</small><strong>{{ $student->programme->name ?? 'Not assigned' }}</strong></div>
                <div class="student-detail-item"><small>Subject combination</small><strong>{{ $student->subjectCombination->name ?? 'Not assigned' }}</strong></div>
                <div class="student-detail-item"><small>Screening status</small><strong>{{ ucfirst($student->screening_status ?? 'pending') }}</strong></div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="{{ route('student.biodata.index') }}" class="btn btn-sm btn-outline-primary"><span class="material-symbols-outlined align-middle" style="font-size:18px">badge</span> View bio-data</a>
                <a href="{{ route('student.results.index') }}" class="btn btn-sm btn-outline-primary"><span class="material-symbols-outlined align-middle" style="font-size:18px">grade</span> View results</a>
            </div>
        </div>
    </section>
    <section class="student-panel">
        <div class="student-panel-header"><div><h2>Quick actions</h2><p>Common tasks are one tap away.</p></div><span class="material-symbols-outlined" style="color:var(--sp-primary)">bolt</span></div>
        <div class="student-panel-body"><div class="student-action-list">
            @if(!$student->is_registered)<a class="student-action-link" href="{{ route('student.registration.index') }}"><span class="leading"><span class="material-symbols-outlined">how_to_reg</span>Complete registration</span><span class="material-symbols-outlined">chevron_right</span></a>@endif
            <a class="student-action-link" href="{{ route('student.payments.index') }}"><span class="leading"><span class="material-symbols-outlined">payments</span>Make a payment</span><span class="material-symbols-outlined">chevron_right</span></a>
            <a class="student-action-link" href="{{ route('student.hostel.index') }}"><span class="leading"><span class="material-symbols-outlined">bed</span>Hostel services</span><span class="material-symbols-outlined">chevron_right</span></a>
            <a class="student-action-link" href="{{ route('student.password') }}"><span class="leading"><span class="material-symbols-outlined">lock</span>Change password</span><span class="material-symbols-outlined">chevron_right</span></a>
        </div></div>
    </section>
</div>

{{-- The legacy dashboard markup remains below for compatibility with existing modal hooks. --}}
@if(false)
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Welcome, {{ $student->first_name }}!</h3>
    <span class="badge bg-primary fs-13 px-3 py-2">{{ $student->registration_number }}</span>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                @if($student->passport_photo)
                    <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Photo" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3 text-white fw-bold" style="width:100px;height:100px;font-size:2rem;">
                        {{ strtoupper(substr($student->surname, 0, 1)) }}{{ strtoupper(substr($student->first_name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $student->surname }} {{ $student->first_name }} {{ $student->other_names }}</h5>
                <p class="text-muted small mb-2">{{ $student->email }}</p>
                <span class="badge bg-{{ $student->programme_type === 'IJMB' ? 'success' : 'danger' }} mb-2">{{ $student->programme_type }}</span>
                <div class="text-start mt-3">
                    <p class="mb-1"><strong>Programme:</strong> {{ $student->programme->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Combination:</strong> {{ $student->subjectCombination->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Session:</strong> {{ $student->academicSession->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Status:</strong>
                        <span class="badge {{ $student->is_registered ? 'bg-success' : 'bg-warning' }}">{{ $student->is_registered ? 'Registered' : 'Not Registered' }}</span>
                    </p>
                    <p class="mb-1"><strong>Screening:</strong>
                        <span class="badge bg-{{ $student->screening_status === 'approved' ? 'success' : ($student->screening_status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($student->screening_status) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="row mb-4">
            <div class="col-sm-4 mb-3">
                <div class="card border-0 rounded-3 bg-primary bg-opacity-10 h-100">
                    <div class="card-body p-3 text-center">
                        <i class="material-symbols-outlined text-primary mb-1" style="font-size:2rem;">menu_book</i>
                        <h5 class="fw-bold mb-0">{{ $student->courseRegistrations->count() }}</h5>
                        <small class="text-muted">Courses Registered</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                <div class="card border-0 rounded-3 bg-success bg-opacity-10 h-100">
                    <div class="card-body p-3 text-center">
                        <i class="material-symbols-outlined text-success mb-1" style="font-size:2rem;">payments</i>
                        <h5 class="fw-bold mb-0">{{ $student->payments->where('status', 'successful')->count() }}</h5>
                        <small class="text-muted">Payments Made</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                <div class="card border-0 rounded-3 bg-info bg-opacity-10 h-100">
                    <div class="card-body p-3 text-center">
                        <i class="material-symbols-outlined text-info mb-1" style="font-size:2rem;">grade</i>
                        <h5 class="fw-bold mb-0">{{ $student->results->count() }}</h5>
                        <small class="text-muted">Results Available</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 rounded-3">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Quick Actions</h5></div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @if(!$student->is_registered)
                    <div class="col-md-6">
                        <a href="{{ route('student.registration.index') }}" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                            <i class="material-symbols-outlined fs-16 align-middle">how_to_reg</i> Complete Registration
                        </a>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <a href="{{ route('student.biodata.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">person</i> Update Bio-data
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">menu_book</i> Course Registration
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.results.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">assessment</i> View Results
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.exam.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">receipt_long</i> Exam Fee
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.payments.index') }}" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                            <i class="material-symbols-outlined fs-16 align-middle">payments</i> Fee Payments
                            @if($requiredDue->count() > 0)
                                <span class="badge bg-danger ms-1">{{ $requiredDue->count() }} due</span>
                            @endif
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.hostel.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">bed</i> Hostel Application
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.password') }}" class="btn btn-outline-secondary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">lock</i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif

<!-- Required Payment Modal -->
@if($requiredDue->count() > 0)
<div class="modal fade" id="requiredPaymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="material-symbols-outlined me-2 align-middle">payments</i>Outstanding Payment(s) Required</h5>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted">You have the following required payment(s) outstanding for this session. Please complete them to continue.</p>
                <div class="list-group mb-3">
                    @foreach($requiredDue as $type)
                        @php $pr = $student->paymentTypeProgress($type); @endphp
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $type->name }}</strong>
                                @if($type->split_enabled && $pr['installment'] !== null)
                                    <br><small class="text-muted">{{ $pr['installment_label'] }} (&#8358;{{ number_format($pr['installment_amount'], 2) }})</small>
                                @endif
                            </div>
                            <span class="fw-bold text-danger">&#8358;{{ number_format($pr['remaining'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('student.payments.index') }}" class="btn btn-primary" style="background:#006633;border-color:#006633;">
                    <i class="material-symbols-outlined fs-16 align-middle me-1">payments</i> Proceed to Payment
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Notice Board Modal -->
@if(trim((string) ($dashboardNotice ?? '')) !== '')
<div class="modal fade" id="noticeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" style="background:#006633;">
                <h5 class="modal-title"><i class="material-symbols-outlined me-2 align-middle">campaign</i>Notice Board</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info mb-0 sbrs-dashboard-notice">{!! $dashboardNotice !!}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" style="background:#006633;border-color:#006633;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show required payment modal first (if any), else notice board
    var requiredModalEl = document.getElementById('requiredPaymentModal');
    if (requiredModalEl) {
        var requiredModal = new bootstrap.Modal(requiredModalEl);
        requiredModal.show();
    } else {
        var noticeElement = document.getElementById('noticeModal');
        if (noticeElement) new bootstrap.Modal(noticeElement).show();
    }
});
</script>
<style>.sbrs-dashboard-notice{line-height:1.65}.sbrs-dashboard-notice h2,.sbrs-dashboard-notice h3,.sbrs-dashboard-notice h4,.sbrs-dashboard-notice p,.sbrs-dashboard-notice ul,.sbrs-dashboard-notice ol,.sbrs-dashboard-notice blockquote{margin-top:0;margin-bottom:.85rem}.sbrs-dashboard-notice ul,.sbrs-dashboard-notice ol{padding-left:1.35rem}.sbrs-dashboard-notice p:last-child,.sbrs-dashboard-notice ul:last-child,.sbrs-dashboard-notice ol:last-child{margin-bottom:0}.sbrs-dashboard-notice a{font-weight:600}</style>
@endsection
