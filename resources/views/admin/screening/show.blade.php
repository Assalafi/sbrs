@extends('layouts.admin')
@section('title', 'Screening Details')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Screening: {{ $student->registration_number }}</h3>
    <a href="{{ route('admin.screening.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                @if($student->passport_photo)
                    <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Photo" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3 text-white fw-bold" style="width:120px;height:120px;font-size:2.5rem;">
                        {{ strtoupper(substr($student->surname, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold">{{ $student->surname }} {{ $student->first_name }} {{ $student->other_names }}</h5>
                <p class="text-muted mb-1">{{ $student->registration_number }}</p>
                <span class="badge bg-{{ $student->screening_status === 'approved' ? 'success' : ($student->screening_status === 'rejected' ? 'danger' : 'warning') }} mb-3">
                    Screening: {{ ucfirst($student->screening_status) }}
                </span>
                <hr>
                <div class="text-start">
                    <p class="mb-1"><strong>Email:</strong> {{ $student->email }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $student->phone }}</p>
                    <p class="mb-1"><strong>Programme:</strong> {{ $student->programme->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Combination:</strong> {{ $student->subjectCombination->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Session:</strong> {{ $student->academicSession->name ?? 'N/A' }}</p>
                </div>

                @if($student->screening_status === 'pending')
                <hr>
                <form action="{{ route('admin.screening.approve', $student) }}" method="POST" class="mb-2" onsubmit="return confirm('Approve screening?')">
                    @csrf
                    <button class="btn btn-success btn-sm w-100"><i class="material-symbols-outlined fs-16 align-middle">check</i> Approve Screening</button>
                </form>
                <form action="{{ route('admin.screening.reject', $student) }}" method="POST" onsubmit="return confirm('Reject screening?')">
                    @csrf
                    <div class="mb-2">
                        <textarea class="form-control form-control-sm" name="remarks" placeholder="Rejection reason (required)" rows="2" required></textarea>
                    </div>
                    <button class="btn btn-danger btn-sm w-100"><i class="material-symbols-outlined fs-16 align-middle">close</i> Reject Screening</button>
                </form>
                @endif

                @if($student->screening_remarks)
                <hr>
                <div class="text-start">
                    <p class="mb-0"><strong>Remarks:</strong> {{ $student->screening_remarks }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-4">
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Student Information</h5></div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Date of Birth:</strong><br>{{ $student->date_of_birth ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>Gender:</strong><br>{{ $student->gender ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>State:</strong><br>{{ $student->state_of_origin ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>LGA:</strong><br>{{ $student->lga ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>Nationality:</strong><br>{{ $student->nationality ?? 'Nigerian' }}</div>
                    <div class="col-md-4"><strong>Marital Status:</strong><br>{{ $student->marital_status ?? 'N/A' }}</div>
                </div>
                <p><strong>Home Address:</strong> {{ $student->home_address ?? 'N/A' }}</p>
                <p><strong>Guardian:</strong> {{ $student->guardian_name ?? 'N/A' }} - {{ $student->guardian_phone ?? '' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
