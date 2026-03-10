@extends('layouts.student')
@section('title', 'Results')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">My Results</h3>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

@if($results->count())
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Academic Session</label>
                <select class="form-select form-select-sm" name="academic_session_id">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Semester</label>
                <select class="form-select form-select-sm" name="semester">
                    <option value="">All</option>
                    <option value="first" {{ request('semester') == 'first' ? 'selected' : '' }}>First</option>
                    <option value="second" {{ request('semester') == 'second' ? 'selected' : '' }}>Second</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('student.results.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Course Code</th><th>Course Title</th><th>Credits</th><th>CA</th><th>Exam</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead>
                <tbody>
                    @php $totalCredits = 0; $totalPoints = 0; @endphp
                    @foreach($results as $result)
                    @php
                        $credits = $result->course->credit_units ?? 0;
                        $gradePoints = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0];
                        $gp = $gradePoints[$result->grade] ?? 0;
                        $totalCredits += $credits;
                        $totalPoints += ($gp * $credits);
                    @endphp
                    <tr>
                        <td class="fw-medium">{{ $result->course->course_code ?? 'N/A' }}</td>
                        <td>{{ $result->course->course_title ?? 'N/A' }}</td>
                        <td>{{ $credits }}</td>
                        <td>{{ $result->ca_score }}</td>
                        <td>{{ $result->exam_score }}</td>
                        <td class="fw-bold">{{ $result->total_score }}</td>
                        <td><span class="badge bg-{{ in_array($result->grade, ['A','B','C']) ? 'success' : (in_array($result->grade, ['D','E']) ? 'warning' : 'danger') }}">{{ $result->grade }}</span></td>
                        <td><span class="text-{{ $result->remark === 'Pass' ? 'success' : 'danger' }}">{{ $result->remark }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="2">Summary</td>
                        <td>{{ $totalCredits }}</td>
                        <td colspan="3"></td>
                        <td colspan="2">GPA: {{ $totalCredits > 0 ? number_format($totalPoints / $totalCredits, 2) : 'N/A' }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@else
<div class="card border-0 rounded-3">
    <div class="card-body p-5 text-center">
        <i class="material-symbols-outlined text-muted" style="font-size:3rem;">assessment</i>
        <p class="text-muted mt-2">No results available yet.</p>
    </div>
</div>
@endif
@endsection
