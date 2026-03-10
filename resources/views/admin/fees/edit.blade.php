@extends('layouts.admin')
@section('title', 'Edit Fee')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Edit Fee</h3>
    <a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.fees.update', $fee) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Academic Session</label>
                    <select class="form-select" name="academic_session_id" required>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ $fee->academic_session_id == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Fee Type</label>
                    <select class="form-select" name="fee_type" required>
                        @foreach(['application','admission','registration','examination'] as $type)
                            <option value="{{ $type }}" {{ $fee->fee_type == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Programme Type</label>
                    <select class="form-select" name="programme_type" required>
                        <option value="all" {{ $fee->programme_type == 'all' ? 'selected' : '' }}>All</option>
                        <option value="IJMB" {{ $fee->programme_type == 'IJMB' ? 'selected' : '' }}>IJMB</option>
                        <option value="Remedial" {{ $fee->programme_type == 'Remedial' ? 'selected' : '' }}>Remedial</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Amount (&#8358;)</label>
                    <input type="number" step="0.01" class="form-control" name="amount" value="{{ old('amount', $fee->amount) }}" required>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <input type="text" class="form-control" name="description" value="{{ old('description', $fee->description) }}">
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $fee->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Update Fee</button>
        </form>
    </div>
</div>
@endsection
