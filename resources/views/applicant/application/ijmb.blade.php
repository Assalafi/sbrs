@extends('layouts.applicant')
@section('title', 'IJMB Application Form')

@php
    $isSubmitted = $applicant->status === 'submitted';
    $disabled = $isSubmitted ? 'disabled' : '';
    $subjects = config('subjects', []);
    $states = config('states', []);
    $sections = $sections ?? $applicant->getSectionCompletion();
    $openTab = $openTab ?? 'personalInfo';
    $sectionOrder = ['personal', 'schools', 'results', 'sponsorship', 'referees'];
    $tabIds = ['personal' => 'personalInfo', 'schools' => 'schoolsSection', 'results' => 'resultsSection', 'sponsorship' => 'sponsorSection', 'referees' => 'refereesSection', 'declaration' => 'declarationSection'];
    // A section is accessible if all previous sections are complete (or it's the first incomplete one)
    $accessible = [];
    $allPreviousComplete = true;
    foreach ($sectionOrder as $key) {
        $accessible[$key] = $allPreviousComplete;
        if (!$sections[$key]) $allPreviousComplete = false;
    }
    $accessible['declaration'] = $allPreviousComplete; // all 5 must be complete
    $allComplete = $allPreviousComplete;
    $completedCount = count(array_filter($sections));
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">IJMB Application Form</h3>
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
        @foreach(['Personal', 'Schools', 'O\'Level', 'Sponsor', 'Referees'] as $i => $label)
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
                                @foreach(['Single','Married','Divorced','Widowed'] as $ms)
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
                            <label class="form-label fw-medium">Permanent Address</label>
                            <input type="text" class="form-control" name="permanent_address" value="{{ $application->permanent_address }}" {{ $disabled }}>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Extracurricular Activities</label>
                            <input type="text" class="form-control" name="extracurricular_activities" value="{{ $application->extracurricular_activities }}" placeholder="e.g. Football, Chess, Debate" {{ $disabled }}>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Disability or Sickness (if any)</label>
                            <input type="text" class="form-control" name="disability_or_sickness" value="{{ $application->disability_or_sickness }}" placeholder="Leave blank if none" {{ $disabled }}>
                        </div>
                    </div>
                    <h6 class="fw-semibold mt-3 mb-2"><i class="material-symbols-outlined me-1 align-middle fs-16">family_restroom</i> Next of Kin</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Name</label>
                            <input type="text" class="form-control" name="nok_name" value="{{ $application->nok_name }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Phone</label>
                            <input type="text" class="form-control" name="nok_phone" value="{{ $application->nok_phone }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Relationship</label>
                            <input type="text" class="form-control" name="nok_relationship" value="{{ $application->nok_relationship }}" {{ $disabled }}>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-medium">Address</label>
                            <input type="text" class="form-control" name="nok_address" value="{{ $application->nok_address }}" {{ $disabled }}>
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

    <!-- Section 2: Schools Attended -->
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['schools'] && !$isSubmitted ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'schoolsSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#schoolsSection" {{ !$accessible['schools'] && !$isSubmitted ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">school</i> 2. Schools Attended
                @if(!$accessible['schools'] && !$isSubmitted)<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete previous</span>@elseif($sections['schools'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="schoolsSection" class="accordion-collapse collapse {{ $openTab === 'schoolsSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <form action="{{ route('applicant.application.schools') }}" method="POST">
                    @csrf
                    <div id="schoolsContainer">
                        @forelse($application->schoolsAttended ?? [] as $i => $school)
                        <div class="row school-row mb-2 border-bottom pb-2">
                            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="schools[{{ $i }}][school_name]" value="{{ $school->school_name }}" placeholder="School Name" {{ $disabled }}></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="schools[{{ $i }}][from_year]" value="{{ $school->from_year }}" placeholder="From" {{ $disabled }}></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="schools[{{ $i }}][to_year]" value="{{ $school->to_year }}" placeholder="To" {{ $disabled }}></div>
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="schools[{{ $i }}][qualification]" value="{{ $school->qualification }}" placeholder="Qualification" {{ $disabled }}></div>
                            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-row" {{ $disabled }}><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @empty
                        <div class="row school-row mb-2">
                            <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="schools[0][school_name]" placeholder="School Name"></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="schools[0][from_year]" placeholder="From"></div>
                            <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="schools[0][to_year]" placeholder="To"></div>
                            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="schools[0][qualification]" placeholder="Qualification"></div>
                            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="material-symbols-outlined fs-16">close</i></button></div>
                        </div>
                        @endforelse
                    </div>
                    @if(!$isSubmitted)
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addSchool"><i class="material-symbols-outlined fs-16 align-middle">add</i> Add School</button>
                    <button type="submit" class="btn btn-primary btn-sm mt-2" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Section 3: O'Level Results -->
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['results'] && !$isSubmitted ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'resultsSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#resultsSection" {{ !$accessible['results'] && !$isSubmitted ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">grading</i> 3. O'Level Results <span class="text-danger">*</span>
                @if(!$accessible['results'] && !$isSubmitted)<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete previous</span>@elseif($sections['results'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="resultsSection" class="accordion-collapse collapse {{ $openTab === 'resultsSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                @php
                    $olevelResults = $application->olevelResults ?? collect();
                    $firstSitting = $olevelResults->get(0);
                    $secondSitting = $olevelResults->get(1);
                    $hasSecondSitting = $secondSitting !== null;
                @endphp
                <form action="{{ route('applicant.application.results') }}" method="POST">
                    @csrf
                    <!-- First Sitting -->
                    <div class="border rounded p-3 mb-3">
                        <h6 class="fw-semibold mb-2">First Sitting</h6>
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label class="form-label small">Exam Type</label>
                                <select class="form-select form-select-sm" name="olevel_results[0][exam_type]" {{ $disabled }}>
                                    @foreach(['WAEC','NECO','NABTEB','GCE','Other'] as $type)
                                        <option value="{{ $type }}" {{ ($firstSitting->exam_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label small">Exam Number</label><input type="text" class="form-control form-control-sm" name="olevel_results[0][exam_number]" value="{{ $firstSitting->exam_number ?? '' }}" {{ $disabled }}></div>
                            <div class="col-md-2"><label class="form-label small">Year</label><input type="text" class="form-control form-control-sm" name="olevel_results[0][exam_year]" value="{{ $firstSitting->exam_year ?? '' }}" {{ $disabled }}></div>
                            <div class="col-md-4"><label class="form-label small">Exam Centre</label><input type="text" class="form-control form-control-sm" name="olevel_results[0][exam_centre]" value="{{ $firstSitting->exam_centre ?? '' }}" placeholder="e.g. Maiduguri" {{ $disabled }}></div>
                        </div>
                        <table class="table table-sm"><thead><tr><th>Subject</th><th>Grade</th></tr></thead><tbody>
                            @for($si = 0; $si < 9; $si++)
                            @php $subj = ($firstSitting->subjects ?? collect())->get($si); @endphp
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm select2-subject" name="olevel_results[0][subjects][{{ $si }}][subject]" {{ $disabled }}>
                                        <option value="">-- Select Subject --</option>
                                        @foreach($subjects as $s)
                                            <option value="{{ $s }}" {{ ($subj->subject ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><select class="form-select form-select-sm" name="olevel_results[0][subjects][{{ $si }}][grade]" {{ $disabled }}>
                                    <option value="">--</option>
                                    @foreach(['A1','B2','B3','C4','C5','C6','D7','E8','F9'] as $g)
                                        <option value="{{ $g }}" {{ ($subj->grade ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select></td>
                            </tr>
                            @endfor
                        </tbody></table>
                    </div>

                    <!-- Second Sitting Checkbox -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="hasSecondSitting" {{ $hasSecondSitting ? 'checked' : '' }} {{ $disabled }}>
                        <label class="form-check-label fw-medium" for="hasSecondSitting">I have a Second Sitting</label>
                    </div>

                    <!-- Second Sitting (hidden by default) -->
                    <div id="secondSittingBlock" class="border rounded p-3 mb-3" style="{{ $hasSecondSitting ? '' : 'display:none;' }}">
                        <h6 class="fw-semibold mb-2">Second Sitting</h6>
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label class="form-label small">Exam Type</label>
                                <select class="form-select form-select-sm" name="olevel_results[1][exam_type]" {{ $disabled }}>
                                    <option value="">--</option>
                                    @foreach(['WAEC','NECO','NABTEB','GCE','Other'] as $type)
                                        <option value="{{ $type }}" {{ ($secondSitting->exam_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label small">Exam Number</label><input type="text" class="form-control form-control-sm" name="olevel_results[1][exam_number]" value="{{ $secondSitting->exam_number ?? '' }}" {{ $disabled }}></div>
                            <div class="col-md-2"><label class="form-label small">Year</label><input type="text" class="form-control form-control-sm" name="olevel_results[1][exam_year]" value="{{ $secondSitting->exam_year ?? '' }}" {{ $disabled }}></div>
                            <div class="col-md-4"><label class="form-label small">Exam Centre</label><input type="text" class="form-control form-control-sm" name="olevel_results[1][exam_centre]" value="{{ $secondSitting->exam_centre ?? '' }}" placeholder="e.g. Maiduguri" {{ $disabled }}></div>
                        </div>
                        <table class="table table-sm"><thead><tr><th>Subject</th><th>Grade</th></tr></thead><tbody>
                            @for($si = 0; $si < 9; $si++)
                            @php $subj2 = ($secondSitting->subjects ?? collect())->get($si); @endphp
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm select2-subject-2" name="olevel_results[1][subjects][{{ $si }}][subject]" {{ $disabled }}>
                                        <option value="">-- Select Subject --</option>
                                        @foreach($subjects as $s)
                                            <option value="{{ $s }}" {{ ($subj2->subject ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><select class="form-select form-select-sm" name="olevel_results[1][subjects][{{ $si }}][grade]" {{ $disabled }}>
                                    <option value="">--</option>
                                    @foreach(['A1','B2','B3','C4','C5','C6','D7','E8','F9'] as $g)
                                        <option value="{{ $g }}" {{ ($subj2->grade ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select></td>
                            </tr>
                            @endfor
                        </tbody></table>
                    </div>

                    @if(!$isSubmitted)
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Section 4: Sponsorship -->
    <div class="accordion-item border-0 rounded-3 mb-3 {{ !$accessible['sponsorship'] && !$isSubmitted ? 'opacity-50' : '' }}">
        <h2 class="accordion-header">
            <button class="accordion-button fw-semibold {{ $openTab !== 'sponsorSection' ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sponsorSection" {{ !$accessible['sponsorship'] && !$isSubmitted ? 'disabled' : '' }}>
                <i class="material-symbols-outlined me-2">volunteer_activism</i> 4. Sponsorship
                @if(!$accessible['sponsorship'] && !$isSubmitted)<span class="badge bg-dark ms-auto me-2"><i class="material-symbols-outlined fs-14 align-middle">lock</i> Complete previous</span>@elseif($sections['sponsorship'])<span class="badge bg-success ms-auto me-2">Complete</span>@else<span class="badge bg-secondary ms-auto me-2">Incomplete</span>@endif
            </button>
        </h2>
        <div id="sponsorSection" class="accordion-collapse collapse {{ $openTab === 'sponsorSection' ? 'show' : '' }}" data-bs-parent="#applicationAccordion">
            <div class="accordion-body">
                <form action="{{ route('applicant.application.sponsorship') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-medium">Who will pay your fees?</label>
                            <select class="form-select" name="sponsor_type" {{ $disabled }}>
                                <option value="">--</option>
                                @foreach(['State Government','Non-Governmental Organization','Individual','Self'] as $st)
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
                    @if(!$isSubmitted)
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;"><i class="material-symbols-outlined fs-16 align-middle">save</i> Save & Continue</button>
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
    // Initialize Select2
    $('.select2-state').select2({ theme: 'bootstrap-5', placeholder: '-- Select State --', allowClear: true });
    $('.select2-lga').select2({ theme: 'bootstrap-5', placeholder: '-- Select LGA --', allowClear: true });
    $('.select2-subject').select2({ theme: 'bootstrap-5', placeholder: '-- Select Subject --', allowClear: true, width: '100%' });
    $('.select2-subject-2').select2({ theme: 'bootstrap-5', placeholder: '-- Select Subject --', allowClear: true, width: '100%' });

    // Dynamic LGA based on state
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

    // Second sitting checkbox toggle
    $('#hasSecondSitting').on('change', function() {
        if ($(this).is(':checked')) {
            $('#secondSittingBlock').slideDown(200);
        } else {
            $('#secondSittingBlock').slideUp(200);
            // Clear second sitting fields
            $('#secondSittingBlock').find('input[type="text"]').val('');
            $('#secondSittingBlock').find('select').val('').trigger('change');
        }
    });
});

let schoolIndex = {{ count($application->schoolsAttended ?? []) ?: 1 }};
document.getElementById('addSchool')?.addEventListener('click', function() {
    const container = document.getElementById('schoolsContainer');
    container.insertAdjacentHTML('beforeend', `<div class="row school-row mb-2"><div class="col-md-4"><input type="text" class="form-control form-control-sm" name="schools[${schoolIndex}][school_name]" placeholder="School Name"></div><div class="col-md-2"><input type="text" class="form-control form-control-sm" name="schools[${schoolIndex}][from_year]" placeholder="From"></div><div class="col-md-2"><input type="text" class="form-control form-control-sm" name="schools[${schoolIndex}][to_year]" placeholder="To"></div><div class="col-md-3"><input type="text" class="form-control form-control-sm" name="schools[${schoolIndex}][qualification]" placeholder="Qualification"></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="material-symbols-outlined fs-16">close</i></button></div></div>`);
    schoolIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-row')) { e.target.closest('.school-row, .row').remove(); }
});
</script>
@endpush
