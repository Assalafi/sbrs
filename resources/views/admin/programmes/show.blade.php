@extends('layouts.admin')
@section('title', 'Programme Details')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">{{ $programme->name }}</h3>
    <a href="{{ route('admin.programmes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-3">Programme Info</h5>
                <p><strong>Code:</strong> {{ $programme->code }}</p>
                <p><strong>Type:</strong> <span class="badge {{ $programme->type === 'IJMB' ? 'bg-success' : 'bg-danger' }}">{{ $programme->type }}</span></p>
                <p><strong>Status:</strong> <span class="badge {{ $programme->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $programme->is_active ? 'Active' : 'Inactive' }}</span></p>
                <p><strong>Description:</strong> {{ $programme->description ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-semibold mb-0">Subject Combinations</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.programmes.add-combination', $programme) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small">Name</label>
                            <input type="text" class="form-control form-control-sm" name="name" required placeholder="e.g. Physics/Chemistry/Biology">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Code</label>
                            <input type="text" class="form-control form-control-sm" name="code" placeholder="PCB">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Subjects (comma separated)</label>
                            <input type="text" class="form-control form-control-sm" name="subjects" required placeholder="Physics, Chemistry, Biology">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100" style="background:#006633;border-color:#006633;">Add</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle table-sm">
                        <thead><tr><th>Name</th><th>Code</th><th>Subjects</th><th></th></tr></thead>
                        <tbody>
                            @forelse($programme->subjectCombinations as $combo)
                            <tr>
                                <td class="fw-medium">{{ $combo->name }}</td>
                                <td>{{ $combo->code ?? 'N/A' }}</td>
                                <td><small>{{ $combo->subjects }}</small></td>
                                <td>
                                    <form action="{{ route('admin.programmes.remove-combination', $combo) }}" method="POST" onsubmit="return confirm('Remove?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="material-symbols-outlined fs-16">close</i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No combinations added.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
