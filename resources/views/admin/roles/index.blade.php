@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Roles & Permissions</h3>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
        <i class="material-symbols-outlined fs-16 align-middle">add</i> New Role
    </a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Role</th><th>Permissions</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td class="fw-medium">{{ $role->name }}</td>
                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ $role->permissions_count }} permissions</span></td>
                        <td>
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary me-1"><i class="material-symbols-outlined fs-16">edit</i></a>
                            @if($role->name !== 'Super Admin')
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="material-symbols-outlined fs-16">delete</i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
