@extends('layouts.admin')
@section('title', 'Edit Course')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Edit Course</h3>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.courses.update', $course) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Programme <span class="text-danger">*</span></label>
                    <select class="form-select" name="programme_id" required>
                        @foreach($programmes as $prog)
                            <option value="{{ $prog->id }}" {{ $course->programme_id == $prog->id ? 'selected' : '' }}>{{ $prog->name }} ({{ $prog->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Subject Combination</label>
                    <select class="form-select" name="subject_combination_id">
                        <option value="">All combinations</option>
                        @foreach($combinations as $combo)
                            <option value="{{ $combo->id }}" {{ $course->subject_combination_id == $combo->id ? 'selected' : '' }}>{{ $combo->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Course Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="course_code" value="{{ old('course_code', $course->course_code) }}" required>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label fw-medium">Course Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="course_title" value="{{ old('course_title', $course->course_title) }}" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-medium">Credits</label>
                    <input type="number" class="form-control" name="credit_units" value="{{ old('credit_units', $course->credit_units) }}" required min="0">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-medium">Semester</label>
                    <select class="form-select" name="semester" required>
                        <option value="first" {{ $course->semester == 'first' ? 'selected' : '' }}>First</option>
                        <option value="second" {{ $course->semester == 'second' ? 'selected' : '' }}>Second</option>
                    </select>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $course->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Update Course</button>
        </form>
    </div>
</div>
@endsection
