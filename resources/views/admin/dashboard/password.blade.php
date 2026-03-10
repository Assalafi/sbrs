@extends('layouts.admin')
@section('title', 'Change Password')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Change Password</h3>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('admin.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required placeholder="Min. 6 characters">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
