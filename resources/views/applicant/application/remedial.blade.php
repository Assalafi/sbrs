@extends('layouts.applicant')
@section('title', 'Remedial Application Form')

@php
    $isSubmitted = $applicant->status === 'submitted';
    $disabled = $isSubmitted ? 'disabled' : '';
    $subjects = config('subjects', []);
    $states = config('states', []);
    $sections = $sections ?? $applicant->getSectionCompletion();
    $openTab = $openTab ?? 'personalInfo';
    $sectionOrder = ['personal', 'schools', 'results', 'sponsorship', 'referees'];
    $tabIds = ['personal' => 'personalInfo', 'schools' => 'institutionsSection', 'results' => 'resultsSection', 'sponsorship' => 'sponsorSection', 'referees' => 'refereesSection', 'declaration' => 'declarationSection'];
    $accessible = [];
    $allPreviousComplete = true;
    foreach ($sectionOrder as $key) {
        $accessible[$key] = $allPreviousComplete;
        if (!$sections[$key]) $allPreviousComplete = false;
    }
    $accessible['declaration'] = $allPreviousComplete;
    $allComplete = $allPreviousComplete;
    $completedCount = count(array_filter($sections));
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Remedial Application Form</h3>
    <div class="d-flex align-items-center gap-3">
        <span class="badge bg-{{ $allComplete ? 'success' : 'warning' }} fs-12">{{ $completedCount }}/5 Sections Complete</span>
        <a href="{{ route('applicant.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
    </div>
</div>

@if($isSubmitted)
<div class="alert alert-info"><i class="material-symbols-outlined align-middle">info</i> Your application has been submitted. You cannot make further changes.</div>
@endif

{{-- Progress Bar --}}
@if(!$isSubmitted)
<div class="mb-4">
    <div class="progress" style="height: 8px;">
        <div class="progress-bar bg-success" style="width: {{ ($completedCount / 5) * 100 }}%"></div>
    </div>
    <div class="d-flex justify-content-between mt-1">
        @foreach(['Personal', 'Institutions', 'O\'Level', 'Sponsor', 'Referees'] as $i => $label)
            @php $key = $sectionOrder[$i]; @endphp
            <small class="{{ $sections[$key] ? 'text-success fw-bold' : 'text-muted' }}">
                {!! $sections[$key] ? '<i class="material-symbols-outlined fs-14 align-middle">check_circle</i>' : '<i class="material-symbols-outlined fs-14 align-middle">radio_button_unchecked</i>' !!} {{ $label }}
            </small>
        @endforeach
    </div>
</div>
@endif

<div class="accordion" id="applicationAccordion">
    <!-- Section 1: Personal Information -->
    <div class="accordion-item border-0 rounded-3 mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'personalInfo' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#personalInfo">
                <i class="material-symbols-outlined me-2">person</i> 1. Personal Information & Programme
                @if($sections['personal'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="personalInfo" class="accordion-collapse collapse {{ $openTab === 'personalInfo' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <form action="{{ route('applicant.application.personal') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Programme <span class="text-danger">*</span></label>
                            <select class="form-select" name="programme_id" id="programme_id" required {{ $disabled }}>
                                <option value="">-- Select --</option>
                                @foreach($programmes as $prog)
                                    <option value="{{ $prog->id }}" {{ $applicant->programme_id == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Passport Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="passport_photo" accept="image/jpeg,image/png" {{ $disabled }}>
                            <small class="text-muted">Max: 500KB (JPG, PNG)</small>
                            @if($applicant->passport_photo)
                                <img src="{{ asset('storage/' . $applicant->passport_photo) }}" alt="Photo" class="mt-2 rounded" style="width:60px;height:60px;object-fit:cover;">
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" value="{{ $application->date_of_birth }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Gender</label>
                            <select class="form-select" name="gender" {{ $disabled }}>
                                <option value="">--</option>
                                <option value="Male" {{ $application->gender === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $application->gender === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Marital Status</label>
                            <select class="form-select" name="marital_status" {{ $disabled }}>
                                <option value="">--</option>
                                @foreach(['Single','Married'] as $ms)
                                    <option value="{{ $ms }}" {{ $application->marital_status === $ms ? 'selected' : '' }}>{{ $ms }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Nationality</label>
                            <input type="text" class="form-control" name="nationality" value="{{ $application->nationality ?? 'Nigerian' }}" {{ $disabled }}>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">State of Origin</label>
                            <select class="form-select select2-state" name="state_of_origin" id="state_of_origin" {{ $disabled }}>
                                <option value="">-- Select State --</option>
                                @foreach($states as $state)
                                    <option value="{{ $state }}" {{ $application->state_of_origin === $state ? 'selected' : '' }}>{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">LGA</label>
                            <select class="form-select select2-lga" name="lga" id="lga_select" {{ $disabled }}>
                                <option value="">-- Select State First --</option>
                                @if($application->state_of_origin && $application->lga)
                                    @foreach(config('lgas.' . $application->state_of_origin, []) as $lga)
                                        <option value="{{ $lga }}" {{ $application->lga === $lga ? 'selected' : '' }}>{{ $lga }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Correspondence Address</label>
                            <input type="text" class="form-control" name="correspondence_address" value="{{ $application->correspondence_address }}" {{ $disabled }}>
                        </div>
                    </div>
                    <h6 class="fw-semibold mt-3 mb-2"><i class="material-symbols-outlined me-1 align-middle fs-16">family_restroom</i> Parent / Guardian Information</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Name</label>
                            <input type="text" class="form-control" name="guardian_name" value="{{ $application->guardian_name }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Phone</label>
                            <input type="text" class="form-control" name="guardian_phone" value="{{ $application->guardian_phone }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" class="form-control" name="guardian_email" value="{{ $application->guardian_email }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Address</label>
                            <input type="text" class="form-control" name="guardian_address" value="{{ $application->guardian_address }}" {{ $disabled }}>
                        </div>
                    </div>
                    <h6 class="fw-semibold mt-3 mb-2"><i class="material-symbols-outlined me-1 align-middle fs-16">home</i> Permanent Contact Address</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Address</label>
                            <input type="text" class="form-control" name="permanent_address" value="{{ $application->permanent_address }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Phone</label>
                            <input type="text" class="form-control" name="permanent_phone" value="{{ $application->permanent_phone }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" class="form-control" name="permanent_email" value="{{ $application->permanent_email }}" {{ $disabled }}>
                        </div>
                    </div>
                    <h6 class="fw-semibold mt-3 mb-2"><i class="material-symbols-outlined me-1 align-middle fs-16">menu_book</i> Primary Education</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">School Name</label>
                            <input type="text" class="form-control" name="primary_school_name" value="{{ $application->primary_school_name }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">From (Year)</label>
                            <input type="text" class="form-control" name="primary_school_from" value="{{ $application->primary_school_from }}" placeholder="e.g. 2010" {{ $disabled }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">To (Year)</label>
                            <input type="text" class="form-control" name="primary_school_to" value="{{ $application->primary_school_to }}" placeholder="e.g. 2016" {{ $disabled }}>
                        </div>
                    </div>
                    <h6 class="fw-semibold mt-3 mb-2"><i class="material-symbols-outlined me-1 align-middle fs-16">upload_file</i> Document Uploads <small class="text-muted fw-normal">(Max 500KB each, JPG/PNG/PDF)</small></h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Indigene Certificate <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" name="indigene_cert" accept=".jpg,.jpeg,.png,.pdf" {{ $disabled }}>
                            @if($applicant->indigene_cert)
                                <small class="text-success"><i class="material-symbols-outlined fs-16 align-middle">check_circle</i> Uploaded</small>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Primary Certificate <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" name="primary_cert" accept=".jpg,.jpeg,.png,.pdf" {{ $disabled }}>
                            @if($applicant->primary_cert)
                                <small class="text-success"><i class="material-symbols-outlined fs-16 align-middle">check_circle</i> Uploaded</small>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">SSCE Certificate <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" name="ssce_cert" accept=".jpg,.jpeg,.png,.pdf" {{ $disabled }}>
                            @if($applicant->ssce_cert)
                                <small class="text-success"><i class="material-symbols-outlined fs-16 align-middle">check_circle</i> Uploaded</small>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Birth Certificate <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" name="birth_cert" accept=".jpg,.jpeg,.png,.pdf" {{ $disabled }}>
                            @if($applicant->birth_cert)
                                <small class="text-success"><i class="material-symbols-outlined fs-16 align-middle">check_circle</i> Uploaded</small>
                            @endif
                        </div>
                    </div>
                    @if(!$isSubmitted)
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Section 2: Post Primary Institutions Attended -->
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['schools'] && !$isSubmitted ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'institutionsSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#institutionsSection" {{ !$accessible['schools'] && !$isSubmitted ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">school</i> 2. Post Primary Institutions Attended
                @if(!$accessible['schools'] && !$isSubmitted)<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete previous</span>@elseif($sections['schools'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="institutionsSection" class="accordion-collapse collapse {{ $openTab === 'institutionsSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <form action="{{ route('applicant.application.schools') }}" method="POST">
                    @csrf
                    <div id="institutionsContainer">
                        @forelse($application->institutions ?? [] as $i => $inst)
                        <div class="row inst-row mb-2 border-bottom pb-2">
                            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="institutions[{{ $i }}][institution_name]" value="{{ $inst->institution_name }}" placeholder="Institution Name" {{ $disabled }}></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="institutions[{{ $i }}][from_year]" value="{{ $inst->from_year }}" placeholder="From" {{ $disabled }}></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="institutions[{{ $i }}][to_year]" value="{{ $inst->to_year }}" placeholder="To" {{ $disabled }}></div>
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="institutions[{{ $i }}][qualification]" value="{{ $inst->qualification }}" placeholder="Qualification" {{ $disabled }}></div>
                            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-row" {{ $disabled }}><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @empty
                        <div class="row inst-row mb-2">
                            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="institutions[0][institution_name]" placeholder="Institution Name"></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="institutions[0][from_year]" placeholder="From"></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="institutions[0][to_year]" placeholder="To"></div>
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="institutions[0][qualification]" placeholder="Qualification"></div>
                            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @endforelse
                    </div>
                    @if(!$isSubmitted)
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addInstitution"><i class="material-symbols-outlined fs-16 align-middle">add</i> Add Institution</button>
                    <button type="submit" class="btn btn-primary btn-sm mt-2" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Section 3: Exam Results -->
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['results'] && !$isSubmitted ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'resultsSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#resultsSection" {{ !$accessible['results'] && !$isSubmitted ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">grading</i> 3. Examination Results (O'Level) <span class="text-danger">*</span>
                @if(!$accessible['results'] && !$isSubmitted)<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete previous</span>@elseif($sections['results'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="resultsSection" class="accordion-collapse collapse {{ $openTab === 'resultsSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <form action="{{ route('applicant.application.results') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Date of Examination</label>
                            <input type="text" class="form-control" name="exam_date" value="{{ $application->exam_date }}" placeholder="e.g. May/June 2023" {{ $disabled }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Examination Centre</label>
                            <input type="text" class="form-control" name="exam_centre" value="{{ $application->exam_centre }}" placeholder="e.g. Maiduguri" {{ $disabled }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Examination Number</label>
                            <input type="text" class="form-control" name="exam_number" value="{{ $application->exam_number }}" {{ $disabled }}>
                        </div>
                    </div>
                    <h6 class="fw-semibold mb-2">Subjects & Grades</h6>
                    <div id="resultsContainer">
                        @forelse($application->examResults ?? [] as $i => $result)
                        <div class="row result-row mb-2 border-bottom pb-2">
                            <div class="col-md-5">
                                <select class="form-select form-select-sm select2-subject" name="exam_results[{{ $i }}][subject]" {{ $disabled }}>
                                    <option value="">-- Select Subject --</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s }}" {{ $result->subject === $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <select class="form-select form-select-sm" name="exam_results[{{ $i }}][grade]" {{ $disabled }}>
                                    <option value="">--</option>
                                    @foreach(['A1','B2','B3','C4','C5','C6','D7','E8','F9'] as $g)
                                        <option value="{{ $g }}" {{ $result->grade === $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger remove-row" {{ $disabled }}><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @empty
                        @for($i = 0; $i < 9; $i++)
                        <div class="row result-row mb-2">
                            <div class="col-md-5">
                                <select class="form-select form-select-sm select2-subject" name="exam_results[{{ $i }}][subject]">
                                    <option value="">-- Select Subject --</option>
                                    @foreach($subjects as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-5"><select class="form-select form-select-sm" name="exam_results[{{ $i }}][grade]"><option value="">--</option>@foreach(['A1','B2','B3','C4','C5','C6','D7','E8','F9'] as $g)<option value="{{ $g }}">{{ $g }}</option>@endforeach</select></div>
                            <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @endfor
                        @endforelse
                    </div>
                    @if(!$isSubmitted)
                    <button type="submit" class="btn btn-primary btn-sm mt-2" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Section 4: Sponsorship & Employment -->
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['sponsorship'] && !$isSubmitted ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'sponsorSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sponsorSection" {{ !$accessible['sponsorship'] && !$isSubmitted ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">volunteer_activism</i> 4. Sponsorship, Activities & Employment
                @if(!$accessible['sponsorship'] && !$isSubmitted)<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete previous</span>@elseif($sections['sponsorship'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="sponsorSection" class="accordion-collapse collapse {{ $openTab === 'sponsorSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <form action="{{ route('applicant.application.sponsorship') }}" method="POST">
                    @csrf
                    <h6 class="fw-semibold mb-2">Who will pay your fees?</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Sponsor Type</label>
                            <select class="form-select" name="sponsor_type" {{ $disabled }}>
                                <option value="">--</option>
                                @foreach(['State Government','Non-Governmental Organization','Any Other Government','Individual','Self'] as $st)
                                    <option value="{{ $st }}" {{ $application->sponsor_type === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Sponsor Name</label>
                            <input type="text" class="form-control" name="sponsor_name" value="{{ $application->sponsor_name }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Sponsor Address</label>
                            <input type="text" class="form-control" name="sponsor_address" value="{{ $application->sponsor_address }}" {{ $disabled }}>
                        </div>
                    </div>
                    <h6 class="fw-semibold mt-3 mb-2">Extracurricular Activities</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Games</label>
                            <input type="text" class="form-control" name="games" value="{{ $application->games }}" placeholder="e.g. Football, Basketball" {{ $disabled }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Hobbies</label>
                            <input type="text" class="form-control" name="hobbies" value="{{ $application->hobbies }}" placeholder="e.g. Reading, Coding" {{ $disabled }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Other Activities</label>
                            <input type="text" class="form-control" name="other_activities" value="{{ $application->other_activities }}" {{ $disabled }}>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-medium">Positions of Responsibility Held</label>
                            <textarea class="form-control" name="positions_held" rows="2" placeholder="List positions held in school or after leaving school" {{ $disabled }}>{{ $application->positions_held }}</textarea>
                        </div>
                    </div>
                    <h6 class="fw-semibold mt-3 mb-2">Record of Employment (Optional)</h6>
                    <div id="employmentContainer">
                        @forelse($application->employmentRecords ?? [] as $i => $emp)
                        <div class="row emp-row mb-2 border-bottom pb-2">
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="employment[{{ $i }}][employer]" value="{{ $emp->employer }}" placeholder="Employer" {{ $disabled }}></div>
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="employment[{{ $i }}][post]" value="{{ $emp->post }}" placeholder="Post" {{ $disabled }}></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="employment[{{ $i }}][from_date]" value="{{ $emp->from_date }}" placeholder="From" {{ $disabled }}></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="employment[{{ $i }}][to_date]" value="{{ $emp->to_date }}" placeholder="To" {{ $disabled }}></div>
                            <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger remove-row" {{ $disabled }}><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @empty
                        <div class="row emp-row mb-2">
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="employment[0][employer]" placeholder="Employer"></div>
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="employment[0][post]" placeholder="Post"></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="employment[0][from_date]" placeholder="From"></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="employment[0][to_date]" placeholder="To"></div>
                            <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @endforelse
                    </div>
                    @if(!$isSubmitted)
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addEmployment"><i class="material-symbols-outlined fs-16 align-middle">add</i> Add Employment</button>
                    <button type="submit" class="btn btn-primary btn-sm mt-2" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Section 5: Referees -->
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['referees'] && !$isSubmitted ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'refereesSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#refereesSection" {{ !$accessible['referees'] && !$isSubmitted ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">group</i> 5. Referees (3 Required)
                @if(!$accessible['referees'] && !$isSubmitted)<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete previous</span>@elseif($sections['referees'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="refereesSection" class="accordion-collapse collapse {{ $openTab === 'refereesSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <p class="text-muted small mb-3">Including your last Head of Department or Principal. Relations cannot serve as referees.</p>
                <form action="{{ route('applicant.application.referees') }}" method="POST">
                    @csrf
                    @for($i = 0; $i < 3; $i++)
                    @php $ref = ($application->referees ?? collect())->get($i); @endphp
                    <h6 class="fw-semibold mb-2">Referee {{ $i + 1 }}</h6>
                    <div class="row mb-3">
                        <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="referees[{{ $i }}][name]" value="{{ $ref->name ?? '' }}" placeholder="Full Name" {{ $disabled }}></div>
                        <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="referees[{{ $i }}][address]" value="{{ $ref->address ?? '' }}" placeholder="Address" {{ $disabled }}></div>
                    </div>
                    @endfor
                    @if(!$isSubmitted)
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Section 6: Declaration & Submit -->
    @if(!$isSubmitted)
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['declaration'] ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'declarationSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#declarationSection" {{ !$accessible['declaration'] ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">fact_check</i> 6. Declaration & Submit
                @if(!$accessible['declaration'])<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete all sections</span>@else<span class="badge bg-info ms-auto me-2">Ready to Submit</span>@endif
            </button>
        </h2>
        <div id="declarationSection" class="accordion-collapse collapse {{ $openTab === 'declarationSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <form action="{{ route('applicant.application.submit') }}" method="POST" onsubmit="return confirm('Are you sure? You cannot edit after submission.')">
                    @csrf
                    <div class="alert alert-warning">
                        <strong>Important:</strong> Once submitted, you will not be able to edit your application. Please review all sections before submitting.
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="declaration_confirmed" name="declaration_confirmed" value="1" required>
                        <label class="form-check-label" for="declaration_confirmed">
                            I hereby declare that all information provided in this application is true and correct. I understand that any false information may lead to disqualification.
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Full Name (as declaration)</label>
                        <input type="text" class="form-control" name="declaration_name" required placeholder="Type your full name">
                    </div>
                    <button type="submit" class="btn btn-success"><i class="material-symbols-outlined fs-16 align-middle">send</i> Submit Application</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-state').select2({ theme: 'bootstrap-5', placeholder: '-- Select State --', allowClear: true });
    $('.select2-lga').select2({ theme: 'bootstrap-5', placeholder: '-- Select LGA --', allowClear: true });
    $('.select2-subject').select2({ theme: 'bootstrap-5', placeholder: '-- Select Subject --', allowClear: true, width: '100%' });

    $('#state_of_origin').on('change', function() {
        var state = $(this).val();
        var lgaSelect = $('#lga_select');
        lgaSelect.empty().append('<option value="">Loading...</option>').trigger('change');
        if (state) {
            $.getJSON('/applicant/application/lgas/' + encodeURIComponent(state), function(data) {
                lgaSelect.empty().append('<option value="">-- Select LGA --</option>');
                $.each(data, function(i, lga) {
                    lgaSelect.append('<option value="' + lga + '">' + lga + '</option>');
                });
                lgaSelect.trigger('change');
            });
        } else {
            lgaSelect.empty().append('<option value="">-- Select State First --</option>').trigger('change');
        }
    });
});

let instIndex = {{ count($application->institutions ?? []) ?: 1 }};
document.getElementById('addInstitution')?.addEventListener('click', function() {
    const container = document.getElementById('institutionsContainer');
    container.insertAdjacentHTML('beforeend', `<div class="row inst-row mb-2"><div class="col-md-4"><input type="text" class="form-control form-control-sm" name="institutions[${instIndex}][institution_name]" placeholder="Institution Name"></div><div class="col-md-2"><input type="text" class="form-control form-control-sm" name="institutions[${instIndex}][from_year]" placeholder="From"></div><div class="col-md-2"><input type="text" class="form-control form-control-sm" name="institutions[${instIndex}][to_year]" placeholder="To"></div><div class="col-md-3"><input type="text" class="form-control form-control-sm" name="institutions[${instIndex}][qualification]" placeholder="Qualification"></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="material-symbols-outlined fs-16">close</i></button></div></div>`);
    instIndex++;
});

let empIndex = {{ count($application->employmentRecords ?? []) ?: 1 }};
document.getElementById('addEmployment')?.addEventListener('click', function() {
    const container = document.getElementById('employmentContainer');
    container.insertAdjacentHTML('beforeend', `<div class="row emp-row mb-2"><div class="col-md-3"><input type="text" class="form-control form-control-sm" name="employment[${empIndex}][employer]" placeholder="Employer"></div><div class="col-md-3"><input type="text" class="form-control form-control-sm" name="employment[${empIndex}][post]" placeholder="Post"></div><div class="col-md-2"><input type="text" class="form-control form-control-sm" name="employment[${empIndex}][from_date]" placeholder="From"></div><div class="col-md-2"><input type="text" class="form-control form-control-sm" name="employment[${empIndex}][to_date]" placeholder="To"></div><div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="material-symbols-outlined fs-16">close</i></button></div></div>`);
    empIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-row')) { e.target.closest('.inst-row, .result-row, .emp-row, .row').remove(); }
});
</script>
@endpush
