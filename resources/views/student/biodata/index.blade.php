@extends('layouts.student')
@section('title', 'Bio-data')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Bio-data</h3>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('student.biodata.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Surname</label>
                    <input type="text" class="form-control" value="{{ $student->surname }}" disabled>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">First Name</label>
                    <input type="text" class="form-control" value="{{ $student->first_name }}" disabled>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Other Names</label>
                    <input type="text" class="form-control" value="{{ $student->other_names }}" disabled>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Email</label>
                    <input type="email" class="form-control" value="{{ $student->email }}" disabled>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone', $student->phone) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Date of Birth</label>
                    <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth) }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Gender</label>
                    <select class="form-select" name="gender">
                        <option value="">--</option>
                        <option value="Male" {{ $student->gender === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $student->gender === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Marital Status</label>
                    <select class="form-select" name="marital_status">
                        <option value="">--</option>
                        @foreach(['Single','Married','Divorced','Widowed'] as $ms)
                            <option value="{{ $ms }}" {{ $student->marital_status === $ms ? 'selected' : '' }}>{{ $ms }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Nationality</label>
                    <input type="text" class="form-control" name="nationality" value="{{ old('nationality', $student->nationality ?? 'Nigerian') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-medium">Religion</label>
                    <select class="form-select" name="religion">
                        <option value="">--</option>
                        @foreach(['Islam','Christianity','Traditional','Other'] as $rel)
                            <option value="{{ $rel }}" {{ $student->religion === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">State of Origin</label>
                    <select class="form-select select2-state" name="state_of_origin" id="state_of_origin">
                        <option value="">-- Select State --</option>
                        @foreach(config('states', []) as $state)
                            <option value="{{ $state }}" {{ $student->state_of_origin === $state ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">LGA</label>
                    <select class="form-select select2-lga" name="lga" id="lga_select">
                        <option value="">-- Select State First --</option>
                        @if($student->state_of_origin && $student->lga)
                            @foreach(config('lgas.' . $student->state_of_origin, []) as $lga)
                                <option value="{{ $lga }}" {{ $student->lga === $lga ? 'selected' : '' }}>{{ $lga }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Home Address</label>
                    <input type="text" class="form-control" name="home_address" value="{{ old('home_address', $student->home_address) }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Guardian Name</label>
                    <input type="text" class="form-control" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Guardian Phone</label>
                    <input type="text" class="form-control" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-medium">Guardian Address</label>
                    <input type="text" class="form-control" name="guardian_address" value="{{ old('guardian_address', $student->guardian_address) }}">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Passport Photo</label>
                @if($student->passport_photo)
                    <div class="mb-2"><img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Photo" class="rounded" style="width:80px;height:80px;object-fit:cover;"></div>
                @endif
                <input type="file" class="form-control" name="passport_photo" accept="image/jpeg,image/png">
            </div>
            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">
                <i class="material-symbols-outlined fs-16 align-middle">save</i> Update Bio-data
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-state').select2({ theme: 'bootstrap-5', placeholder: '-- Select State --', allowClear: true });
    $('.select2-lga').select2({ theme: 'bootstrap-5', placeholder: '-- Select LGA --', allowClear: true });

    $('#state_of_origin').on('change', function() {
        var state = $(this).val();
        var lgaSelect = $('#lga_select');
        lgaSelect.empty().append('<option value="">Loading...</option>').trigger('change');
        if (state) {
            $.getJSON('/student/lgas/' + encodeURIComponent(state), function(data) {
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
</script>
@endpush
