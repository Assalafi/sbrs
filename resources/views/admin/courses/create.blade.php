@extends('layouts.admin')
@section('title', 'Create Course')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Create Course</h3>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.courses.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Programme <span class="text-danger">*</span></label>
                    <select class="form-select" name="programme_id" id="programme_id" required>
                        <option value="">-- Select --</option>
                        @foreach($programmes as $prog)
                            <option value="{{ $prog->id }}" {{ old('programme_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }} ({{ $prog->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Subject Combination</label>
                    <select class="form-select" name="subject_combination_id" id="subject_combination_id">
                        <option value="">All combinations</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Course Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="course_code" value="{{ old('course_code') }}" required placeholder="e.g. PHY101">
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label fw-medium">Course Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="course_title" value="{{ old('course_title') }}" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-medium">Credits <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="credit_units" value="{{ old('credit_units', 3) }}" required min="0">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-medium">Semester <span class="text-danger">*</span></label>
                    <select class="form-select" name="semester" required>
                        <option value="first" {{ old('semester') == 'first' ? 'selected' : '' }}>First</option>
                        <option value="second" {{ old('semester') == 'second' ? 'selected' : '' }}>Second</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Create Course</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('programme_id').addEventListener('change', function() {
    const programmeId = this.value;
    const comboSelect = document.getElementById('subject_combination_id');
    comboSelect.innerHTML = '<option value="">All combinations</option>';
    if (programmeId) {
        fetch('/admin/programmes/' + programmeId + '/combinations')
            .then(r => r.json())
            .then(data => {
                data.forEach(c => {
                    comboSelect.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
                });
            });
    }
});
</script>
@endpush
