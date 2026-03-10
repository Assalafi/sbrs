@extends('layouts.student')
@section('title', 'Course Registration')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Course Registration</h3>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

@if(!$student->is_registered)
<div class="alert alert-warning">
    <i class="material-symbols-outlined align-middle">warning</i>
    You must complete registration before registering for courses. <a href="{{ route('student.registration.index') }}">Register now</a>.
</div>
@else

@if($registeredCourses->count())
<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-semibold mb-0">Registered Courses</h5>
        <div>
            <span class="badge bg-primary me-2">{{ $registeredCourses->count() }} courses | {{ $registeredCourses->sum(function($r) { return $r->course->credit_units ?? 0; }) }} credit units</span>
            <a href="{{ route('student.courses.print') }}" target="_blank" class="btn btn-sm btn-outline-success"><i class="material-symbols-outlined fs-16 align-middle">print</i> Print Course Form</a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Code</th><th>Title</th><th>Credits</th><th>Semester</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($registeredCourses as $reg)
                    <tr>
                        <td class="fw-medium">{{ $reg->course->course_code ?? 'N/A' }}</td>
                        <td>{{ $reg->course->course_title ?? 'N/A' }}</td>
                        <td>{{ $reg->course->credit_units ?? 0 }}</td>
                        <td>{{ ucfirst($reg->semester) }}</td>
                        <td><span class="badge bg-success">Registered</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($availableCourses->count())
<div class="card border-0 rounded-3">
    <div class="card-header bg-white pt-4 px-4">
        <h5 class="fw-semibold mb-0">Available Courses</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('student.courses.register') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th><input type="checkbox" id="selectAll"></th><th>Code</th><th>Title</th><th>Credits</th><th>Semester</th></tr></thead>
                    <tbody>
                        @foreach($availableCourses as $course)
                        <tr>
                            <td><input type="checkbox" name="course_ids[]" value="{{ $course->id }}" class="course-checkbox"></td>
                            <td class="fw-medium">{{ $course->course_code }}</td>
                            <td>{{ $course->course_title }}</td>
                            <td>{{ $course->credit_units }}</td>
                            <td>{{ ucfirst($course->semester) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary mt-3" style="background:#006633;border-color:#006633;">
                <i class="material-symbols-outlined fs-16 align-middle">app_registration</i> Register Selected Courses
            </button>
        </form>
    </div>
</div>
@elseif(!$registeredCourses->count())
<div class="card border-0 rounded-3">
    <div class="card-body p-5 text-center">
        <i class="material-symbols-outlined text-muted" style="font-size:3rem;">menu_book</i>
        <p class="text-muted mt-2">No courses available for registration at this time.</p>
    </div>
</div>
@endif

@endif
@endsection

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.course-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
