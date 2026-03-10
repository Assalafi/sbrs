@extends('layouts.admin')
@section('title', 'Results')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Results Management</h3>
    <a href="{{ route('admin.results.create') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
        <i class="material-symbols-outlined fs-16 align-middle">upload</i> Upload Results
    </a>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Session</label>
                <select class="form-select form-select-sm" name="academic_session_id">
                    <option value="">All</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
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
                <label class="form-label small">Search</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Name, Reg No...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('admin.results.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Student</th><th>Reg No</th><th>Course</th><th>CA</th><th>Exam</th><th>Total</th><th>Grade</th><th>Semester</th></tr></thead>
                <tbody>
                    @forelse($results as $result)
                    <tr>
                        <td>{{ $result->student->surname ?? '' }} {{ $result->student->first_name ?? '' }}</td>
                        <td class="fw-medium">{{ $result->student->registration_number ?? 'N/A' }}</td>
                        <td>{{ $result->course->course_code ?? 'N/A' }}</td>
                        <td>{{ $result->ca_score }}</td>
                        <td>{{ $result->exam_score }}</td>
                        <td class="fw-bold">{{ $result->total_score }}</td>
                        <td><span class="badge bg-{{ in_array($result->grade, ['A','B','C']) ? 'success' : (in_array($result->grade, ['D','E']) ? 'warning' : 'danger') }}">{{ $result->grade }}</span></td>
                        <td>{{ ucfirst($result->semester) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No results found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $results->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
