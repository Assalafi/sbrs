@extends('layouts.admin')
@section('title', 'Programmes')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Programmes</h3>
    <a href="{{ route('admin.programmes.create') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
        <i class="material-symbols-outlined fs-16 align-middle">add</i> New Programme
    </a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Code</th><th>Name</th><th>Type</th><th>Combinations</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($programmes as $programme)
                    <tr>
                        <td class="fw-medium">{{ $programme->code }}</td>
                        <td>{{ $programme->name }}</td>
                        <td><span class="badge {{ $programme->type === 'IJMB' ? 'bg-success' : 'bg-danger' }}">{{ $programme->type }}</span></td>
                        <td>{{ $programme->subject_combinations_count }}</td>
                        <td><span class="badge {{ $programme->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $programme->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('admin.programmes.show', $programme) }}" class="btn btn-sm btn-outline-info me-1"><i class="material-symbols-outlined fs-16">visibility</i></a>
                            <a href="{{ route('admin.programmes.edit', $programme) }}" class="btn btn-sm btn-outline-primary me-1"><i class="material-symbols-outlined fs-16">edit</i></a>
                            <form action="{{ route('admin.programmes.destroy', $programme) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="material-symbols-outlined fs-16">delete</i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No programmes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
