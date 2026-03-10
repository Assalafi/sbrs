@extends('layouts.admin')
@section('title', 'Audit Log Details')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Audit Log Details</h3>
    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-3">Log Info</h5>
                <table class="table table-borderless">
                    <tr><td class="text-muted">Action:</td><td><span class="badge bg-{{ $auditLog->action === 'created' ? 'success' : ($auditLog->action === 'deleted' ? 'danger' : 'info') }}">{{ ucfirst($auditLog->action) }}</span></td></tr>
                    <tr><td class="text-muted">User:</td><td class="fw-medium">{{ $auditLog->user_name ?? 'System' }}</td></tr>
                    <tr><td class="text-muted">Model:</td><td>{{ class_basename($auditLog->model_type ?? '') }}</td></tr>
                    <tr><td class="text-muted">Model ID:</td><td class="fw-medium">{{ $auditLog->model_id ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">IP Address:</td><td>{{ $auditLog->ip_address ?? 'N/A' }}</td></tr>
                    <tr><td class="text-muted">Date:</td><td>{{ $auditLog->created_at->format('M d, Y H:i:s') }}</td></tr>
                </table>
                @if($auditLog->description)
                <p class="mt-2"><strong>Description:</strong> {{ $auditLog->description }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-4">
        @if($auditLog->old_values)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Old Values</h5></div>
            <div class="card-body p-4">
                <pre class="bg-light p-3 rounded" style="max-height:300px;overflow:auto;">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif
        @if($auditLog->new_values)
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">New Values</h5></div>
            <div class="card-body p-4">
                <pre class="bg-light p-3 rounded" style="max-height:300px;overflow:auto;">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
