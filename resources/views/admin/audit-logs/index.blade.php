@extends('layouts.admin')
@section('title', 'Audit Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Audit Logs</h3>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Action</label>
                <select class="form-select form-select-sm" name="action">
                    <option value="">All</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">User</label>
                <select class="form-select form-select-sm" name="user_id">
                    <option value="">All</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Model</label>
                <select class="form-select form-select-sm" name="model_type">
                    <option value="">All</option>
                    @foreach($modelTypes as $mt)
                        <option value="{{ $mt['full'] }}" {{ request('model_type') == $mt['full'] ? 'selected' : '' }}>{{ $mt['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Search</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Search...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Model</th><th>Details</th><th></th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="fs-13 text-muted">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $log->user_name ?? 'System' }}</td>
                        <td><span class="badge bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'deleted' ? 'danger' : 'info') }}">{{ ucfirst($log->action) }}</span></td>
                        <td class="fs-13">{{ class_basename($log->model_type ?? '') }}</td>
                        <td class="fs-13 text-muted text-truncate" style="max-width:200px;">{{ $log->description ?? Str::limit(json_encode($log->new_values), 50) }}</td>
                        <td><a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary"><i class="material-symbols-outlined fs-16">visibility</i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
