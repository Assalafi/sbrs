@extends('layouts.admin')
@section('title', 'Create Role')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Create Role</h3>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>
<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Role Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium mb-2">Permissions</label>
                @foreach($permissions as $group => $perms)
                <div class="mb-3">
                    <h6 class="fw-semibold text-uppercase text-muted fs-12 mb-2">{{ $group }}</h6>
                    <div class="row">
                        @foreach($perms as $perm)
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}">
                                <label class="form-check-label small" for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">Create Role</button>
        </form>
    </div>
</div>
@endsection
