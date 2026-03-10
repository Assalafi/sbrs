@extends('layouts.admin')
@section('title', 'Student Details')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Student: {{ $student->registration_number }}</h3>
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
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
                <div class="mb-3">
                    <span class="badge bg-{{ $student->is_active ? 'success' : 'danger' }}">{{ $student->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="badge bg-{{ $student->is_registered ? 'success' : 'warning' }}">{{ $student->is_registered ? 'Registered' : 'Not Registered' }}</span>
                </div>
                <hr>
                <div class="text-start">
                    <p class="mb-1"><strong>Email:</strong> {{ $student->email }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $student->phone }}</p>
                    <p class="mb-1"><strong>Programme:</strong> {{ $student->programme->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Type:</strong> {{ $student->programme_type }}</p>
                    <p class="mb-1"><strong>Combination:</strong> {{ $student->subjectCombination->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Session:</strong> {{ $student->academicSession->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>DOB:</strong> {{ $student->date_of_birth ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>State:</strong> {{ $student->state_of_origin ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>LGA:</strong> {{ $student->lga ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Guardian:</strong> {{ $student->guardian_name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Address:</strong> {{ $student->home_address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        @if($student->courseRegistrations && $student->courseRegistrations->count())
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Course Registrations</h5></div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Code</th><th>Title</th><th>Credits</th><th>Semester</th></tr></thead>
                        <tbody>
                            @foreach($student->courseRegistrations as $reg)
                            <tr>
                                <td class="fw-medium">{{ $reg->course->course_code ?? 'N/A' }}</td>
                                <td>{{ $reg->course->course_title ?? 'N/A' }}</td>
                                <td>{{ $reg->course->credit_units ?? 0 }}</td>
                                <td>{{ ucfirst($reg->semester) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($student->results && $student->results->count())
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Results</h5></div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Course</th><th>CA</th><th>Exam</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead>
                        <tbody>
                            @foreach($student->results as $result)
                            <tr>
                                <td class="fw-medium">{{ $result->course->course_code ?? 'N/A' }}</td>
                                <td>{{ $result->ca_score }}</td>
                                <td>{{ $result->exam_score }}</td>
                                <td class="fw-bold">{{ $result->total_score }}</td>
                                <td><span class="badge bg-{{ in_array($result->grade, ['A','B','C']) ? 'success' : (in_array($result->grade, ['D','E']) ? 'warning' : 'danger') }}">{{ $result->grade }}</span></td>
                                <td>{{ $result->remark }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($student->payments && $student->payments->count())
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Payments</h5></div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Type</th><th>Amount</th><th>RRR</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach($student->payments as $payment)
                            <tr>
                                <td>{{ ucfirst($payment->payment_type) }}</td>
                                <td>&#8358;{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->rrr ?? 'N/A' }}</td>
                                <td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($payment->status) }}</span></td>
                                <td class="fs-13">{{ $payment->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
