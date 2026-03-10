@extends('layouts.admin')
@section('title', 'Create Programme')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Create Programme</h3>
    <a href="{{ route('admin.programmes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.programmes.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Programme Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="code" value="{{ old('code') }}" required placeholder="e.g. SCI">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="type" required>
                        <option value="IJMB" {{ old('type') == 'IJMB' ? 'selected' : '' }}>IJMB</option>
                        <option value="Remedial" {{ old('type') == 'Remedial' ? 'selected' : '' }}>Remedial</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Description</label>
                <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Create Programme</button>
        </form>
    </div>
</div>
@endsection
