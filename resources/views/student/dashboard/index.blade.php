@extends('layouts.student')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Welcome, {{ $student->first_name }}!</h3>
    <span class="badge bg-primary fs-13 px-3 py-2">{{ $student->registration_number }}</span>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                @if($student->passport_photo)
                    <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Photo" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3 text-white fw-bold" style="width:100px;height:100px;font-size:2rem;">
                        {{ strtoupper(substr($student->surname, 0, 1)) }}{{ strtoupper(substr($student->first_name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $student->surname }} {{ $student->first_name }} {{ $student->other_names }}</h5>
                <p class="text-muted small mb-2">{{ $student->email }}</p>
                <span class="badge bg-{{ $student->programme_type === 'IJMB' ? 'success' : 'danger' }} mb-2">{{ $student->programme_type }}</span>
                <div class="text-start mt-3">
                    <p class="mb-1"><strong>Programme:</strong> {{ $student->programme->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Combination:</strong> {{ $student->subjectCombination->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Session:</strong> {{ $student->academicSession->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Status:</strong>
                        <span class="badge {{ $student->is_registered ? 'bg-success' : 'bg-warning' }}">{{ $student->is_registered ? 'Registered' : 'Not Registered' }}</span>
                    </p>
                    <p class="mb-1"><strong>Screening:</strong>
                        <span class="badge bg-{{ $student->screening_status === 'approved' ? 'success' : ($student->screening_status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($student->screening_status) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="row mb-4">
            <div class="col-sm-4 mb-3">
                <div class="card border-0 rounded-3 bg-primary bg-opacity-10 h-100">
                    <div class="card-body p-3 text-center">
                        <i class="material-symbols-outlined text-primary mb-1" style="font-size:2rem;">menu_book</i>
                        <h5 class="fw-bold mb-0">{{ $student->courseRegistrations->count() }}</h5>
                        <small class="text-muted">Courses Registered</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                <div class="card border-0 rounded-3 bg-success bg-opacity-10 h-100">
                    <div class="card-body p-3 text-center">
                        <i class="material-symbols-outlined text-success mb-1" style="font-size:2rem;">payments</i>
                        <h5 class="fw-bold mb-0">{{ $student->payments->where('status', 'successful')->count() }}</h5>
                        <small class="text-muted">Payments Made</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-3">
                <div class="card border-0 rounded-3 bg-info bg-opacity-10 h-100">
                    <div class="card-body p-3 text-center">
                        <i class="material-symbols-outlined text-info mb-1" style="font-size:2rem;">grade</i>
                        <h5 class="fw-bold mb-0">{{ $student->results->count() }}</h5>
                        <small class="text-muted">Results Available</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 rounded-3">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Quick Actions</h5></div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @if(!$student->is_registered)
                    <div class="col-md-6">
                        <a href="{{ route('student.registration.index') }}" class="btn btn-primary w-100" style="background:#006633;border-color:#006633;">
                            <i class="material-symbols-outlined fs-16 align-middle">how_to_reg</i> Complete Registration
                        </a>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <a href="{{ route('student.biodata.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">person</i> Update Bio-data
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">menu_book</i> Course Registration
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.results.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">assessment</i> View Results
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.exam.index') }}" class="btn btn-outline-primary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">receipt_long</i> Exam Fee
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('student.password') }}" class="btn btn-outline-secondary w-100">
                            <i class="material-symbols-outlined fs-16 align-middle">lock</i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
