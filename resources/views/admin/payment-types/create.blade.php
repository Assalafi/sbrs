@extends('layouts.admin')
@section('title', 'Create Payment Type')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Create Payment Type</h3>
    <a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.payment-types.store') }}" method="POST" id="paymentTypeForm">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pt_name" name="name" value="{{ old('name') }}" required placeholder="e.g. Library Fee" oninput="autoCode()">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Code <span class="text-danger">*</span> <small class="text-muted">(auto)</small></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="pt_code" name="code" value="{{ old('code') }}" required placeholder="auto-generated">
                        <button type="button" class="btn btn-outline-secondary" onclick="autoCode(true)" title="Regenerate"><i class="material-symbols-outlined fs-18">refresh</i></button>
                    </div>
                    <small class="text-muted" id="codeHint">Auto-generated from the name. You can edit it.</small>
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
                    <label class="form-label fw-medium">Payer <span class="text-danger">*</span></label>
                    <select class="form-select" name="payer_type" required>
                        <option value="both" {{ old('payer_type', 'both') == 'both' ? 'selected' : '' }}>Both (Applicants &amp; Students)</option>
                        <option value="applicant" {{ old('payer_type') == 'applicant' ? 'selected' : '' }}>Applicants only</option>
                        <option value="student" {{ old('payer_type') == 'student' ? 'selected' : '' }}>Students only</option>
                    </select>
                    <small class="text-muted">Applicants = before admission (application/admission fee). Students = after admission (registration/exam/hostel).</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Amount (&#8358;) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="amount" value="{{ old('amount') }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Remita Service Type ID</label>
                    <select class="form-select" name="remita_service_type_id" id="remita_select">
                        <option value="">-- Select from configured service types --</option>
                        @foreach($remitaOptions as $val => $label)
                            <option value="{{ $val }}" {{ old('remita_service_type_id') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Only unique service type IDs from settings are shown.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Optional description shown to payers">{{ old('description') }}</textarea>
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
                        <small class="text-muted">e.g. 50% / 2 installments = payer can pay 100% or a 50% first installment.</small>
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

@push('scripts')
<script>
    var existingCodes = {!! json_encode(\App\Models\PaymentType::pluck('code')->map(fn($c) => strtolower($c))->all()) !!};

    function slugify(str) {
        return str.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '_')
            .replace(/-+/g, '_');
    }

    function autoCode(force) {
        var name = document.getElementById('pt_name').value.trim();
        var codeInput = document.getElementById('pt_code');
        if (!name) { return; }

        // If user manually edited the code and we're not forcing, keep it.
        if (!force && codeInput.dataset.touched === '1' && codeInput.value) { return; }

        var base = slugify(name);
        var code = base;
        var n = 1;
        while (existingCodes.indexOf(code) !== -1) {
            n++;
            code = base + '_' + n;
        }
        codeInput.value = code;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var codeInput = document.getElementById('pt_code');
        // Mark as touched only when the user types directly (not via auto).
        codeInput.addEventListener('input', function () {
            codeInput.dataset.touched = '1';
        });
        autoCode(false);
    });
</script>
@endpush
