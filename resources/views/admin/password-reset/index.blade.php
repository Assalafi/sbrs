@extends('layouts.admin')
@section('title', 'Password Reset')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0"><i class="material-symbols-outlined align-middle me-1">lock_reset</i> Password Reset</h3>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <h6 class="fw-semibold mb-3">Search for a user to reset their password</h6>
        <form action="{{ route('admin.password-reset.search') }}" method="POST">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-medium">User Type</label>
                    <select class="form-select" name="type" required>
                        <option value="applicant" {{ ($type ?? '') === 'applicant' ? 'selected' : '' }}>Applicant</option>
                        <option value="student" {{ ($type ?? '') === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="staff" {{ ($type ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Search (Name, Email, ID)</label>
                    <input type="text" class="form-control" name="search" value="{{ $search ?? '' }}" placeholder="Enter name, email, registration/application number..." required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                        <i class="material-symbols-outlined fs-16 align-middle">search</i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(isset($results))
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <h6 class="fw-semibold mb-3">Search Results <span class="badge bg-secondary">{{ $results->count() }}</span></h6>
        @if($results->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="material-symbols-outlined fs-1">search_off</i>
                <p class="mt-2">No users found matching "<strong>{{ $search }}</strong>" in {{ ucfirst($type) }}s.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>ID/Number</th>
                            <th>Role/Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $user)
                        <tr>
                            <td class="fw-medium">{{ $user['name'] }}</td>
                            <td>{{ $user['email'] }}</td>
                            <td><code>{{ $user['identifier'] }}</code></td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $user['role'] }}</span></td>
                            <td><span class="badge bg-{{ $user['status'] === 'Active' || $user['status'] === 'Approved' ? 'success' : 'secondary' }}">{{ $user['status'] }}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal{{ $loop->index }}">
                                    <i class="material-symbols-outlined fs-16 align-middle">lock_reset</i> Reset
                                </button>
                            </td>
                        </tr>

                        <!-- Reset Modal -->
                        <div class="modal fade" id="resetModal{{ $loop->index }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.password-reset.reset') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                        <input type="hidden" name="user_type" value="{{ $user['type'] }}">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="material-symbols-outlined align-middle me-1">lock_reset</i> Reset Password</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info mb-3">
                                                <strong>{{ $user['name'] }}</strong><br>
                                                <small>{{ $user['email'] }} &bull; {{ $user['identifier'] }}</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-medium">New Password</label>
                                                <input type="password" class="form-control" name="new_password" required minlength="6">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-medium">Confirm Password</label>
                                                <input type="password" class="form-control" name="new_password_confirmation" required minlength="6">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-warning btn-sm"><i class="material-symbols-outlined fs-16 align-middle">lock_reset</i> Reset Password</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif
@endsection
