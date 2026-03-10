@extends('layouts.admin')
@section('title', 'Students')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Students</h3>
    <a href="{{ route('admin.students.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-success btn-sm">
        <i class="material-symbols-outlined fs-16 align-middle">download</i> Export CSV
    </a>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Programme Type</label>
                <select class="form-select form-select-sm" name="programme_type">
                    <option value="">All</option>
                    <option value="IJMB" {{ request('programme_type') == 'IJMB' ? 'selected' : '' }}>IJMB</option>
                    <option value="Remedial" {{ request('programme_type') == 'Remedial' ? 'selected' : '' }}>Remedial</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Session</label>
                <select class="form-select form-select-sm" name="academic_session_id">
                    <option value="">All</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Name, Reg No...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Reg. Number</th><th>Name</th><th>Programme</th><th>Session</th><th>Registered</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td class="fw-medium">{{ $student->registration_number }}</td>
                        <td>{{ $student->surname }} {{ $student->first_name }}</td>
                        <td><small>{{ $student->programme->name ?? 'N/A' }}</small></td>
                        <td class="fs-13">{{ $student->academicSession->name ?? 'N/A' }}</td>
                        <td>
                            @if($student->is_registered)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $student->is_active ? 'bg-success' : 'bg-danger' }}">{{ $student->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary"><i class="material-symbols-outlined fs-16">visibility</i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $students->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
