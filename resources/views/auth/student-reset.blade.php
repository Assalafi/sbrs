@extends('layouts.auth')

@section('title', 'Reset Password')
@section('auth-image', 'login.jpg')
@section('auth-image-key', 'auth_student_login_image')
@section('auth-title', 'Reset Password')
@section('auth-subtitle', 'Set a new password for your account')

@section('content')
<div class="alert alert-info mb-4">
    <strong>{{ $student->surname }} {{ $student->first_name }}</strong><br>
    <small>{{ $student->email }} &bull; {{ $student->registration_number }}</small>
</div>
<form method="POST" action="{{ route('student.forgot.update') }}">
    @csrf
    <div class="form-group mb-4">
        <label class="label text-secondary">New Password</label>
        <input type="password" class="form-control h-55" name="password" required placeholder="Enter new password (min 6 characters)" minlength="6">
    </div>
    <div class="form-group mb-4">
        <label class="label text-secondary">Confirm New Password</label>
        <input type="password" class="form-control h-55" name="password_confirmation" required placeholder="Confirm new password" minlength="6">
    </div>
    <div class="form-group mb-4">
        <button type="submit" class="btn btn-primary fw-medium py-2 px-3 w-100" style="background:#006633;border-color:#006633;">
            <div class="d-flex align-items-center justify-content-center py-1">
                <i class="material-symbols-outlined text-white fs-20 me-2">lock_reset</i>
                <span>Reset Password</span>
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
