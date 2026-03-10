@extends('layouts.admin')
@section('title', 'Create Academic Session')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Create Academic Session</h3>
    <a href="{{ route('admin.academic-sessions.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back
    </a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.academic-sessions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Session Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required placeholder="e.g. 2025/2026">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ old('start_date') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ old('end_date') }}">
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_current" value="0">
                <input type="checkbox" class="form-check-input" id="is_current" name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_current">Set as Current Session</label>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Create Session</button>
        </form>
    </div>
</div>
@endsection
