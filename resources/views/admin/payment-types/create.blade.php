@extends('layouts.admin')
@section('title', 'Create Payment Type')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Create Payment Type</h3>
    <a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.payment-types.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="code" value="{{ old('code') }}" required placeholder="e.g. library_fee">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required placeholder="e.g. Library Fee">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Programme Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="programme_type" required>
                        <option value="all" {{ old('programme_type') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="IJMB" {{ old('programme_type') == 'IJMB' ? 'selected' : '' }}>IJMB</option>
                        <option value="Remedial" {{ old('programme_type') == 'Remedial' ? 'selected' : '' }}>Remedial</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Academic Session</label>
                    <select class="form-select" name="academic_session_id">
                        <option value="">All Sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Amount (&#8358;) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="amount" value="{{ old('amount') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Remita Service Type ID</label>
                    <input type="text" class="form-control" name="remita_service_type_id" value="{{ old('remita_service_type_id') }}" placeholder="e.g. 982250364">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Optional description shown to students">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="border rounded p-3 mb-3">
                <h6 class="fw-semibold mb-3">Split Payment</h6>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="split_enabled" id="split_enabled" value="1" {{ old('split_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="split_enabled">Enable Split Payment</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-medium small">First Installment %</label>
                        <input type="number" step="0.01" min="1" max="100" class="form-control" name="split_percent" value="{{ old('split_percent', 50) }}">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-medium small">Total Installments</label>
                        <input type="number" min="1" max="12" class="form-control" name="installment_count" value="{{ old('installment_count', 2) }}">
                    </div>
                    <div class="col-12">
                        <small class="text-muted">e.g. 50% / 2 installments = student can pay 100% or a 50% first installment.</small>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3 mb-3">
                <h6 class="fw-semibold mb-3">Behaviour</h6>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">Required (MUST) - shown on dashboard popup</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-medium small">Sort Order</label>
                        <input type="number" min="0" class="form-control" name="sort_order" value="{{ old('sort_order', 10) }}">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Create Payment Type</button>
        </form>
    </div>
</div>
@endsection
