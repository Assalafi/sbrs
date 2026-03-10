@extends('layouts.admin')
@section('title', 'Fee Management')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Fee Management</h3>
    <a href="{{ route('admin.fees.create') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
        <i class="material-symbols-outlined fs-16 align-middle">add</i> New Fee
    </a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Session</th><th>Fee Type</th><th>Programme</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($fees as $fee)
                    <tr>
                        <td>{{ $fee->academicSession->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($fee->fee_type) }}</span></td>
                        <td>{{ $fee->programme_type === 'all' ? 'All Programmes' : $fee->programme_type }}</td>
                        <td class="fw-medium">&#8358;{{ number_format($fee->amount, 2) }}</td>
                        <td><span class="badge {{ $fee->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $fee->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('admin.fees.edit', $fee) }}" class="btn btn-sm btn-outline-primary me-1"><i class="material-symbols-outlined fs-16">edit</i></a>
                            <form action="{{ route('admin.fees.destroy', $fee) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="material-symbols-outlined fs-16">delete</i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No fees configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
