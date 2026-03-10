@extends('layouts.admin')
@section('title', 'Courses')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Courses</h3>
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
        <i class="material-symbols-outlined fs-16 align-middle">add</i> New Course
    </a>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Programme</label>
                <select class="form-select form-select-sm" name="programme_id">
                    <option value="">All</option>
                    @foreach($programmes as $prog)
                        <option value="{{ $prog->id }}" {{ request('programme_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }} ({{ $prog->type }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Semester</label>
                <select class="form-select form-select-sm" name="semester">
                    <option value="">All</option>
                    <option value="first" {{ request('semester') == 'first' ? 'selected' : '' }}>First</option>
                    <option value="second" {{ request('semester') == 'second' ? 'selected' : '' }}>Second</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Code</th><th>Title</th><th>Programme</th><th>Credits</th><th>Semester</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="fw-medium">{{ $course->course_code }}</td>
                        <td>{{ $course->course_title }}</td>
                        <td><small>{{ $course->programme->name ?? 'N/A' }}</small></td>
                        <td>{{ $course->credit_units }}</td>
                        <td>{{ ucfirst($course->semester) }}</td>
                        <td><span class="badge {{ $course->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $course->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary me-1"><i class="material-symbols-outlined fs-16">edit</i></a>
                            <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="material-symbols-outlined fs-16">delete</i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No courses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $courses->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
