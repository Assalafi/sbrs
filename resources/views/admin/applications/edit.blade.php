@extends('layouts.admin')
@section('title', 'Edit Application')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Edit Application: {{ $application->application_number }}</h3>
    <div>
        <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> View</a>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">list</i> All Applications</a>
    </div>
</div>

@if($application->student)
<div class="alert alert-info mb-4">
    <i class="material-symbols-outlined align-middle me-1">school</i>
    This applicant has a linked student record (<strong>{{ $application->student->registration_number }}</strong>).
    Changes to name, email, phone, programme and session will be applied to the student record too.
</div>
@endif

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.applications.update', $application) }}" method="POST">
            @csrf @method('PUT')

            <h6 class="fw-semibold mb-3">Personal Details</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Surname <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="surname" value="{{ old('surname', $application->surname) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="first_name" value="{{ old('first_name', $application->first_name) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Other Names</label>
                    <input type="text" class="form-control" name="other_names" value="{{ old('other_names', $application->other_names) }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $application->email) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone', $application->phone) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Application Number</label>
                    <input type="text" class="form-control" value="{{ $application->application_number }}" disabled>
                </div>
            </div>

            <h6 class="fw-semibold mb-3 mt-2">Programme & Session</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Programme Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="programme_type" required>
                        <option value="IJMB" {{ old('programme_type', $application->programme_type) == 'IJMB' ? 'selected' : '' }}>IJMB</option>
                        <option value="Remedial" {{ old('programme_type', $application->programme_type) == 'Remedial' ? 'selected' : '' }}>Remedial</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Programme <span class="text-danger">*</span></label>
                    <select class="form-select" name="programme_id" required>
                        <option value="">-- Select Programme --</option>
                        @foreach($programmes as $programme)
                            <option value="{{ $programme->id }}" {{ old('programme_id', $application->programme_id) == $programme->id ? 'selected' : '' }}>{{ $programme->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Combination</label>
                    <select class="form-select" name="subject_combination_id">
                        <option value="">-- None --</option>
                        @foreach($combinations as $combo)
                            <option value="{{ $combo->id }}" {{ old('subject_combination_id', $application->subject_combination_id) == $combo->id ? 'selected' : '' }}>{{ $combo->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Session <span class="text-danger">*</span></label>
                    <select class="form-select" name="academic_session_id" required>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id', $application->academic_session_id) == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="fw-semibold mb-3 mt-2">Status & Account</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" required>
                        @foreach(['registered','payment_pending','form_filling','submitted','under_review','approved','rejected','admitted'] as $st)
                            <option value="{{ $st }}" {{ old('status', $application->status) == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Setting status to <strong>Admitted</strong> creates/keeps the student record.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium d-block">Account Active</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $application->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle me-1">save</i> Update Application</button>
        </form>
    </div>
</div>
@endsection
