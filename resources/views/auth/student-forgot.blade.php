@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('auth-image', 'login.jpg')
@section('auth-image-key', 'auth_student_login_image')
@section('auth-title', 'Forgot Password')
@section('auth-subtitle', 'Verify your identity to reset your password')

@section('content')
<form method="POST" action="{{ route('student.forgot.verify') }}">
    @csrf
    <div class="form-group mb-4">
        <label class="label text-secondary">Email Address</label>
        <input type="email" class="form-control h-55" name="email" value="{{ old('email') }}" required autofocus placeholder="your@email.com">
    </div>
    <div class="form-group mb-4">
        <label class="label text-secondary">Phone Number</label>
        <input type="text" class="form-control h-55" name="phone" value="{{ old('phone') }}" required placeholder="Enter your registered phone number">
    </div>
    <div class="form-group mb-4">
        <button type="submit" class="btn btn-primary fw-medium py-2 px-3 w-100" style="background:#006633;border-color:#006633;">
            <div class="d-flex align-items-center justify-content-center py-1">
                <i class="material-symbols-outlined text-white fs-20 me-2">verified_user</i>
                <span>Verify Identity</span>
            </div>
        </button>
    </div>
    <div class="form-group">
        <p><a href="{{ route('student.login') }}" class="fw-medium text-decoration-none" style="color:#006633;">
            <i class="material-symbols-outlined fs-16" style="vertical-align:middle;">arrow_back</i> Back to Login
        </a></p>
    </div>
</form>
@endsection
