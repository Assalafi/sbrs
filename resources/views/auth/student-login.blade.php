@extends('layouts.auth')

@section('title', 'Student Login')
@section('auth-image', 'login.jpg')
@section('auth-image-key', 'auth_student_login_image')
@section('auth-title', 'Student Portal')
@section('auth-subtitle', 'Sign in with your registration number')

@section('content')
<form method="POST" action="{{ route('student.login') }}">
    @csrf
    <div class="form-group mb-4">
        <label class="label text-secondary">Registration Number</label>
        <input type="text" class="form-control h-55" name="registration_number" value="{{ old('registration_number') }}" required autofocus placeholder="e.g. SBRS/IJMB/2026/001">
    </div>
    <div class="form-group mb-4">
        <label class="label text-secondary">Password</label>
        <input type="password" class="form-control h-55" name="password" required placeholder="Enter your password">
    </div>
    <div class="form-group mb-4 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <a href="{{ route('student.forgot') }}" class="fw-medium text-decoration-none" style="color:#006633;">Forgot Password?</a>
    </div>
    <div class="form-group mb-4">
        <button type="submit" class="btn btn-primary fw-medium py-2 px-3 w-100" style="background:#006633;border-color:#006633;">
            <div class="d-flex align-items-center justify-content-center py-1">
                <i class="material-symbols-outlined text-white fs-20 me-2">login</i>
                <span>Sign In</span>
            </div>
        </button>
    </div>
    <div class="form-group">
        <p class="text-muted small">Default password is your application's password.</p>
        <p><a href="{{ url('/') }}" class="fw-medium text-decoration-none" style="color:#006633;">
            <i class="material-symbols-outlined fs-16" style="vertical-align:middle;">arrow_back</i> Back to Homepage
        </a></p>
    </div>
</form>
@endsection
