@extends('layouts.admin')
@section('title', 'Create Fee')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Create Fee</h3>
    <a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.fees.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Academic Session <span class="text-danger">*</span></label>
                    <select class="form-select" name="academic_session_id" required>
                        <option value="">-- Select Session --</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Fee Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="fee_type" required>
                        <option value="application" {{ old('fee_type') == 'application' ? 'selected' : '' }}>Application</option>
                        <option value="admission" {{ old('fee_type') == 'admission' ? 'selected' : '' }}>Admission</option>
                        <option value="registration" {{ old('fee_type') == 'registration' ? 'selected' : '' }}>Registration</option>
                        <option value="examination" {{ old('fee_type') == 'examination' ? 'selected' : '' }}>Examination</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
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
                    <label class="form-label fw-medium">Amount (&#8358;) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="amount" value="{{ old('amount') }}" required>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <input type="text" class="form-control" name="description" value="{{ old('description') }}" placeholder="Optional description">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Create Fee</button>
        </form>
    </div>
</div>
@endsection
