@extends('layouts.admin')
@section('title', 'Applications')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Applications</h3>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">All</option>
                    @foreach(['submitted','under_review','approved','rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Programme</label>
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
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Name, App No, Email...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>App. Number</th><th>Name</th><th>Type</th><th>Session</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($applicants as $applicant)
                    <tr>
                        <td class="fw-medium">{{ $applicant->application_number }}</td>
                        <td>{{ $applicant->surname }} {{ $applicant->first_name }}</td>
                        <td><span class="badge {{ $applicant->programme_type === 'IJMB' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $applicant->programme_type === 'IJMB' ? 'text-success' : 'text-danger' }}">{{ $applicant->programme_type }}</span></td>
                        <td class="fs-13">{{ $applicant->academicSession->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-{{ $applicant->status === 'approved' ? 'success' : ($applicant->status === 'rejected' ? 'danger' : ($applicant->status === 'submitted' ? 'primary' : 'warning')) }}">{{ ucfirst(str_replace('_',' ',$applicant->status)) }}</span></td>
                        <td class="fs-13 text-muted">{{ $applicant->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.applications.show', $applicant) }}" class="btn btn-sm btn-outline-primary"><i class="material-symbols-outlined fs-16">visibility</i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $applicants->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
