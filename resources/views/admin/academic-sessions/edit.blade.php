@extends('layouts.admin')
@section('title', 'Edit Academic Session')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Edit Academic Session</h3>
    <a href="{{ route('admin.academic-sessions.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back
    </a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.academic-sessions.update', $academicSession) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Session Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $academicSession->name) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ old('start_date', $academicSession->start_date ? \Carbon\Carbon::parse($academicSession->start_date)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ old('end_date', $academicSession->end_date ? \Carbon\Carbon::parse($academicSession->end_date)->format('Y-m-d') : '') }}">
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_current" value="0">
                <input type="checkbox" class="form-check-input" id="is_current" name="is_current" value="1" {{ $academicSession->is_current ? 'checked' : '' }}>
                <label class="form-check-label" for="is_current">Set as Current Session</label>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $academicSession->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Update Session</button>
        </form>
    </div>
</div>
@endsection
