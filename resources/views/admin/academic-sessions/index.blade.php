@extends('layouts.admin')
@section('title', 'Academic Sessions')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Academic Sessions</h3>
    <a href="{{ route('admin.academic-sessions.create') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
        <i class="material-symbols-outlined fs-16 align-middle">add</i> New Session
    </a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Session Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Current</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td class="fw-medium">{{ $session->name }}</td>
                        <td>{{ $session->start_date ? \Carbon\Carbon::parse($session->start_date)->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $session->end_date ? \Carbon\Carbon::parse($session->end_date)->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            @if($session->is_current)
                                <span class="badge bg-success">Current</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $session->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $session->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.academic-sessions.edit', $session) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="material-symbols-outlined fs-16">edit</i>
                            </a>
                            @if(!$session->is_current)
                            <form action="{{ route('admin.academic-sessions.set-current', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Set {{ $session->name }} as current session?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-success me-1" title="Set as Current">
                                    <i class="material-symbols-outlined fs-16">check_circle</i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.academic-sessions.destroy', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this session?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="material-symbols-outlined fs-16">delete</i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No academic sessions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
