@extends('layouts.auth')

@section('title', 'Applicant Registration')
@section('auth-image', 'register.jpg')
@section('auth-image-key', 'auth_applicant_register_image')
@section('auth-title', 'Create Your Account')
@section('auth-subtitle', 'Register as a new applicant')

@section('content')
@if(!$session)
    <div class="alert alert-warning">
        <strong>Registration Closed:</strong> No active academic session. Please check back later.
    </div>
@else
<form method="POST" action="{{ route('applicant.register') }}">
    @csrf
    <div class="form-group mb-3">
        <label class="label text-secondary">Programme Type <span class="text-danger">*</span></label>
        <select class="form-select h-55" name="programme_type" required>
            <option value="">-- Select Programme --</option>
            <option value="IJMB" {{ old('programme_type') == 'IJMB' ? 'selected' : '' }}>IJMB Programme</option>
            <option value="Remedial" {{ old('programme_type') == 'Remedial' ? 'selected' : '' }}>Remedial Programme</option>
        </select>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label class="label text-secondary">Surname <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="surname" value="{{ old('surname') }}" required placeholder="Surname">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label class="label text-secondary">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" required placeholder="First Name">
            </div>
        </div>
    </div>
    <div class="form-group mb-3">
        <label class="label text-secondary">Other Names</label>
        <input type="text" class="form-control" name="other_names" value="{{ old('other_names') }}" placeholder="Other Names (optional)">
    </div>
    <div class="form-group mb-3">
        <label class="label text-secondary">Email Address <span class="text-danger">*</span></label>
        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="your@email.com">
    </div>
    <div class="form-group mb-3">
        <label class="label text-secondary">Phone Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required placeholder="08012345678">
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label class="label text-secondary">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password" required placeholder="Min. 6 characters">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label class="label text-secondary">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password_confirmation" required placeholder="Confirm password">
            </div>
        </div>
    </div>
    <div class="mb-3 small text-muted">
        Academic Session: <strong>{{ $session->name }}</strong>
    </div>
    <div class="form-group mb-3">
        <button type="submit" class="btn btn-primary fw-medium py-2 px-3 w-100" style="background:#006633;border-color:#006633;">
            <div class="d-flex align-items-center justify-content-center py-1">
                <i class="material-symbols-outlined text-white fs-20 me-2">person_add</i>
                <span>Create Account</span>
            </div>
        </button>
    </div>
    <div class="form-group">
        <p>Already have an account? <a href="{{ route('applicant.login') }}" class="fw-medium text-decoration-none" style="color:#006633;">Log In</a></p>
        <p><a href="{{ url('/') }}" class="fw-medium text-decoration-none" style="color:#006633;">
            <i class="material-symbols-outlined fs-16" style="vertical-align:middle;">arrow_back</i> Back to Homepage
        </a></p>
    </div>
</form>
@endif
@endsection
