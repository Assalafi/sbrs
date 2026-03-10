@extends('layouts.admin')
@section('title', 'Screening')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Student Screening</h3>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select class="form-select form-select-sm" name="screening_status">
                    <option value="">All</option>
                    @foreach(['pending','approved','rejected'] as $s)
                        <option value="{{ $s }}" {{ request('screening_status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Name, Reg No...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('admin.screening.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Reg. Number</th><th>Name</th><th>Programme</th><th>Screening Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td class="fw-medium">{{ $student->registration_number }}</td>
                        <td>{{ $student->surname }} {{ $student->first_name }}</td>
                        <td>{{ $student->programme->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $student->screening_status === 'approved' ? 'success' : ($student->screening_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($student->screening_status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.screening.show', $student) }}" class="btn btn-sm btn-outline-primary"><i class="material-symbols-outlined fs-16">visibility</i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No screening records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $students->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
