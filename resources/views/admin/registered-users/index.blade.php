@extends('layouts.admin')
@section('title', 'Registered Users')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0"><i class="material-symbols-outlined align-middle me-1">people</i> All Registered Users</h3>
    <a href="{{ route('admin.registered-users.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
        <i class="material-symbols-outlined fs-16 align-middle">download</i> Export CSV
    </a>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="fs-3 fw-bold text-primary">{{ number_format($totalRegistered) }}</div>
                <small class="text-muted">Total Registered</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="fs-3 fw-bold text-success">{{ number_format($totalPaid) }}</div>
                <small class="text-muted">Paid Application Fee</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="fs-3 fw-bold text-info">{{ number_format($totalSubmitted) }}</div>
                <small class="text-muted">Submitted Applications</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body text-center p-3">
                <div class="fs-3 fw-bold text-warning">{{ number_format($totalNotStarted) }}</div>
                <small class="text-muted">Not Yet Submitted</small>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.registered-users.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-medium small">Search</label>
                    <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Name, email, phone, app number...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium small">Status</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Statuses</option>
                        @foreach(['registered', 'form_filling', 'submitted', 'under_review', 'approved', 'rejected'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium small">Programme Type</label>
                    <select class="form-select form-select-sm" name="programme_type">
                        <option value="">All Types</option>
                        <option value="IJMB" {{ request('programme_type') === 'IJMB' ? 'selected' : '' }}>IJMB</option>
                        <option value="Remedial" {{ request('programme_type') === 'Remedial' ? 'selected' : '' }}>Remedial</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium small">Session</label>
                    <select class="form-select form-select-sm" name="academic_session_id">
                        <option value="">All Sessions</option>
                        @foreach($sessions as $sess)
                            <option value="{{ $sess->id }}" {{ request('academic_session_id') == $sess->id ? 'selected' : '' }}>{{ $sess->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1" style="background:#006633;border-color:#006633;">
                        <i class="material-symbols-outlined fs-16 align-middle">filter_list</i> Filter
                    </button>
                    <a href="{{ route('admin.registered-users.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Results Table --}}
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Programme</th>
                        <th>Status</th>
                        <th>Fee Paid</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applicants as $i => $applicant)
                    @php
                        $hasPaid = $applicant->payments->where('payment_type', 'application')->where('status', 'successful')->count() > 0;
                        $statusColors = [
                            'registered' => 'secondary',
                            'form_filling' => 'warning',
                            'submitted' => 'info',
                            'under_review' => 'primary',
                            'approved' => 'success',
                            'rejected' => 'danger',
                        ];
                    @endphp
                    <tr>
                        <td>{{ $applicants->firstItem() + $i }}</td>
                        <td class="fw-medium">{{ $applicant->full_name }}</td>
                        <td><small>{{ $applicant->email }}</small></td>
                        <td><small>{{ $applicant->phone }}</small></td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $applicant->programme_type }}</span></td>
                        <td><small>{{ $applicant->programme->name ?? '<em class="text-muted">Not Selected</em>' }}</small></td>
                        <td><span class="badge bg-{{ $statusColors[$applicant->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $applicant->status)) }}</span></td>
                        <td>
                            @if($hasPaid)
                                <span class="badge bg-success"><i class="material-symbols-outlined fs-14 align-middle">check</i> Yes</span>
                            @else
                                <span class="badge bg-danger"><i class="material-symbols-outlined fs-14 align-middle">close</i> No</span>
                            @endif
                        </td>
                        <td><small>{{ $applicant->created_at->format('M d, Y') }}</small></td>
                        <td>
                            @if(in_array($applicant->status, ['submitted', 'under_review', 'approved', 'rejected']))
                            <a href="{{ route('admin.applications.show', $applicant) }}" class="btn btn-sm btn-outline-primary" title="View Application">
                                <i class="material-symbols-outlined fs-16">visibility</i>
                            </a>
                            @else
                            <span class="text-muted small">No Application</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4"><i class="material-symbols-outlined fs-1">search_off</i><br>No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $applicants->links() }}
        </div>
    </div>
</div>
@endsection
