@extends('layouts.admin')
@section('title', 'Application Details')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Application: {{ $application->application_number }}</h3>
    <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4 text-center">
                @if($application->passport_photo)
                    <img src="{{ asset('storage/' . $application->passport_photo) }}" alt="Photo" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3 text-white fw-bold" style="width:120px;height:120px;font-size:2.5rem;">
                        {{ strtoupper(substr($application->surname, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold">{{ $application->surname }} {{ $application->first_name }} {{ $application->other_names }}</h5>
                <p class="text-muted mb-1">{{ $application->application_number }}</p>
                <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'primary') }} mb-3">
                    {{ ucfirst(str_replace('_',' ',$application->status)) }}
                </span>

                <hr>
                <div class="text-start">
                    <p class="mb-1"><strong>Email:</strong> {{ $application->email }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $application->phone }}</p>
                    <p class="mb-1"><strong>Type:</strong> {{ $application->programme_type }}</p>
                    <p class="mb-1"><strong>Programme:</strong> {{ $application->programme->name ?? 'Not selected' }}</p>
                    <p class="mb-1"><strong>Combination:</strong> {{ $application->subjectCombination->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Session:</strong> {{ $application->academicSession->name ?? 'N/A' }}</p>
                </div>

                @php
                    $docs = [
                        'indigene_cert' => 'Indigene Cert',
                        'primary_cert' => 'Primary Cert',
                        'ssce_cert' => 'SSCE Cert',
                        'birth_cert' => 'Birth Cert',
                    ];
                    $hasAnyDoc = collect($docs)->keys()->contains(fn($k) => $application->$k);
                @endphp
                @if($hasAnyDoc)
                <hr>
                <h6 class="fw-semibold mb-2">Uploaded Documents</h6>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @foreach($docs as $field => $label)
                        @if($application->$field)
                            <a href="{{ asset('storage/' . $application->$field) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="material-symbols-outlined fs-16 align-middle">description</i> {{ $label }}
                            </a>
                        @else
                            <span class="btn btn-outline-secondary btn-sm disabled">{{ $label }} <small>(missing)</small></span>
                        @endif
                    @endforeach
                </div>
                @endif

                @if(in_array($application->status, ['submitted', 'under_review']))
                <hr>
                <div class="d-flex gap-2">
                    <form action="{{ route('admin.applications.approve', $application) }}" method="POST" class="flex-fill" onsubmit="return confirm('Approve this application?')">
                        @csrf
                        <button class="btn btn-success btn-sm w-100"><i class="material-symbols-outlined fs-16 align-middle">check</i> Approve</button>
                    </form>
                    <form action="{{ route('admin.applications.reject', $application) }}" method="POST" class="flex-fill" onsubmit="return confirm('Reject this application?')">
                        @csrf
                        <button class="btn btn-danger btn-sm w-100"><i class="material-symbols-outlined fs-16 align-middle">close</i> Reject</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white pt-4 px-4">
                <h5 class="fw-semibold mb-0">Application Details</h5>
            </div>
            <div class="card-body p-4">
                @if($application->programme_type === 'IJMB' && $application->ijmbApplication)
                    @php $ijmb = $application->ijmbApplication; @endphp
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Date of Birth:</strong><br>{{ $ijmb->date_of_birth ?? 'N/A' }}</div>
                        <div class="col-md-4"><strong>Gender:</strong><br>{{ $ijmb->gender ?? 'N/A' }}</div>
                        <div class="col-md-4"><strong>Marital Status:</strong><br>{{ $ijmb->marital_status ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Nationality:</strong><br>{{ $ijmb->nationality ?? 'N/A' }}</div>
                        <div class="col-md-4"><strong>State:</strong><br>{{ $ijmb->state_of_origin ?? 'N/A' }}</div>
                        <div class="col-md-4"><strong>LGA:</strong><br>{{ $ijmb->lga ?? 'N/A' }}</div>
                    </div>
                    <p><strong>Address:</strong> {{ $ijmb->permanent_address ?? 'N/A' }}</p>
                    <p><strong>Next of Kin:</strong> {{ $ijmb->nok_name ?? 'N/A' }} ({{ $ijmb->nok_relationship ?? '' }}) - {{ $ijmb->nok_phone ?? '' }}</p>
                    <p><strong>Sponsor:</strong> {{ $ijmb->sponsor_name ?? 'N/A' }} ({{ $ijmb->sponsor_type ?? '' }})</p>

                    @if($ijmb->schoolsAttended && $ijmb->schoolsAttended->count())
                    <h6 class="fw-semibold mt-4 mb-2">Schools Attended</h6>
                    <div class="table-responsive">
                        <table class="table table-sm"><thead><tr><th>School</th><th>From</th><th>To</th><th>Qualification</th></tr></thead>
                        <tbody>
                            @foreach($ijmb->schoolsAttended as $school)
                            <tr><td>{{ $school->school_name }}</td><td>{{ $school->from_year }}</td><td>{{ $school->to_year }}</td><td>{{ $school->qualification ?? 'N/A' }}</td></tr>
                            @endforeach
                        </tbody></table>
                    </div>
                    @endif

                    @if($ijmb->olevelResults && $ijmb->olevelResults->count())
                    <h6 class="fw-semibold mt-4 mb-2">O'Level Results</h6>
                    @foreach($ijmb->olevelResults as $result)
                    <div class="border rounded p-3 mb-2">
                        <p class="mb-1"><strong>{{ $result->exam_type }}</strong> - No: {{ $result->exam_number }} ({{ $result->exam_year }})</p>
                        @if($result->subjects && $result->subjects->count())
                        <table class="table table-sm mb-0"><thead><tr><th>Subject</th><th>Grade</th></tr></thead>
                        <tbody>
                            @foreach($result->subjects as $subj)
                            <tr><td>{{ $subj->subject }}</td><td>{{ $subj->grade }}</td></tr>
                            @endforeach
                        </tbody></table>
                        @endif
                    </div>
                    @endforeach
                    @endif

                    @if($ijmb->referees && $ijmb->referees->count())
                    <h6 class="fw-semibold mt-4 mb-2">Referees</h6>
                    @foreach($ijmb->referees as $ref)
                    <p class="mb-1">{{ $ref->name }} - {{ $ref->phone ?? '' }} - {{ $ref->address ?? '' }}</p>
                    @endforeach
                    @endif

                @elseif($application->programme_type === 'Remedial' && $application->remedialApplication)
                    @php $rem = $application->remedialApplication; @endphp
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Date of Birth:</strong><br>{{ $rem->date_of_birth ?? 'N/A' }}</div>
                        <div class="col-md-4"><strong>Gender:</strong><br>{{ $rem->gender ?? 'N/A' }}</div>
                        <div class="col-md-4"><strong>Marital Status:</strong><br>{{ $rem->marital_status ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>State:</strong><br>{{ $rem->state_of_origin ?? 'N/A' }}</div>
                        <div class="col-md-6"><strong>LGA:</strong><br>{{ $rem->lga ?? 'N/A' }}</div>
                    </div>
                    <p><strong>Address:</strong> {{ $rem->correspondence_address ?? 'N/A' }}</p>
                    <p><strong>Guardian:</strong> {{ $rem->guardian_name ?? 'N/A' }} - {{ $rem->guardian_phone ?? '' }}</p>
                    <p><strong>Sponsor:</strong> {{ $rem->sponsor_name ?? 'N/A' }} ({{ $rem->sponsor_type ?? '' }})</p>

                    @if($rem->institutions && $rem->institutions->count())
                    <h6 class="fw-semibold mt-4 mb-2">Institutions Attended</h6>
                    <div class="table-responsive">
                        <table class="table table-sm"><thead><tr><th>Institution</th><th>From</th><th>To</th><th>Qualification</th></tr></thead>
                        <tbody>
                            @foreach($rem->institutions as $inst)
                            <tr><td>{{ $inst->institution_name }}</td><td>{{ $inst->from_year ?? 'N/A' }}</td><td>{{ $inst->to_year ?? 'N/A' }}</td><td>{{ $inst->qualification ?? 'N/A' }}</td></tr>
                            @endforeach
                        </tbody></table>
                    </div>
                    @endif

                    @if($rem->examResults && $rem->examResults->count())
                    <h6 class="fw-semibold mt-4 mb-2">Exam Results</h6>
                    <div class="table-responsive">
                        <table class="table table-sm"><thead><tr><th>Subject</th><th>Grade</th></tr></thead>
                        <tbody>
                            @foreach($rem->examResults as $er)
                            <tr><td>{{ $er->subject }}</td><td>{{ $er->grade }}</td></tr>
                            @endforeach
                        </tbody></table>
                    </div>
                    @endif

                    @if($rem->referees && $rem->referees->count())
                    <h6 class="fw-semibold mt-4 mb-2">Referees</h6>
                    @foreach($rem->referees as $ref)
                    <p class="mb-1">{{ $ref->name }} - {{ $ref->phone ?? '' }} - {{ $ref->address ?? '' }}</p>
                    @endforeach
                    @endif
                @else
                    <p class="text-muted">No application form data found.</p>
                @endif
            </div>
        </div>

        @if($application->payments && $application->payments->count())
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white pt-4 px-4"><h5 class="fw-semibold mb-0">Payments</h5></div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Type</th><th>Amount</th><th>RRR</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach($application->payments as $payment)
                            <tr>
                                <td>{{ ucfirst($payment->payment_type) }}</td>
                                <td>&#8358;{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->rrr ?? 'N/A' }}</td>
                                <td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($payment->status) }}</span></td>
                                <td class="fs-13">{{ $payment->created_at->format('M d, Y H:i') }}</td>
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
