@extends('layouts.admin')
@section('title', 'Upload Results')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Upload Results</h3>
    <a href="{{ route('admin.results.index') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back</a>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form id="filterForm" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Academic Session <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="academic_session_id" id="academic_session_id" required>
                    <option value="">-- Select --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Programme <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="programme_id" id="programme_id" required>
                    <option value="">-- Select --</option>
                    @foreach($programmes as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->name }} ({{ $prog->type }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Course <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="course_id" id="course_id" required>
                    <option value="">-- Select Programme first --</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Semester <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="semester" id="semester" required>
                    <option value="first">First</option>
                    <option value="second">Second</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary btn-sm w-100" id="loadStudents" style="background:#006633;border-color:#006633;">Load</button>
            </div>
        </form>
    </div>
</div>

<div id="resultsForm" style="display:none;">
    <form action="{{ route('admin.results.upload') }}" method="POST">
        @csrf
        <input type="hidden" name="academic_session_id" id="hidden_session">
        <input type="hidden" name="course_id" id="hidden_course">
        <input type="hidden" name="semester" id="hidden_semester">

        <div class="card border-0 rounded-3">
            <div class="card-header bg-white pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-semibold mb-0">Enter Results</h5>
                <span class="badge bg-primary" id="studentCount">0 students</span>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle" id="resultsTable">
                        <thead><tr><th>Reg No</th><th>Name</th><th>CA (0-40)</th><th>Exam (0-60)</th><th>Total</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary mt-3" style="background:#006633;border-color:#006633;">
                    <i class="material-symbols-outlined fs-16 align-middle">upload</i> Save Results
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('programme_id').addEventListener('change', function() {
    const id = this.value;
    const courseSelect = document.getElementById('course_id');
    courseSelect.innerHTML = '<option value="">Loading...</option>';
    if (id) {
        fetch('/admin/results/courses?programme_id=' + id)
            .then(r => r.json())
            .then(data => {
                courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                data.forEach(c => { courseSelect.innerHTML += '<option value="' + c.id + '">' + c.course_code + ' - ' + c.course_title + '</option>'; });
            });
    }
});

document.getElementById('loadStudents').addEventListener('click', function() {
    const sessionId = document.getElementById('academic_session_id').value;
    const courseId = document.getElementById('course_id').value;
    const semester = document.getElementById('semester').value;
    if (!sessionId || !courseId) { alert('Please select session and course.'); return; }

    document.getElementById('hidden_session').value = sessionId;
    document.getElementById('hidden_course').value = courseId;
    document.getElementById('hidden_semester').value = semester;

    fetch('/admin/results/students?course_id=' + courseId + '&academic_session_id=' + sessionId)
        .then(r => r.json())
        .then(students => {
            const tbody = document.querySelector('#resultsTable tbody');
            tbody.innerHTML = '';
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No students registered for this course.</td></tr>';
                document.getElementById('studentCount').textContent = '0 students';
            } else {
                students.forEach((s, i) => {
                    const existing = s.results && s.results.length > 0 ? s.results[0] : null;
                    tbody.innerHTML += `<tr>
                        <td class="fw-medium">${s.registration_number}</td>
                        <td>${s.surname} ${s.first_name}</td>
                        <td><input type="hidden" name="results[${i}][student_id]" value="${s.id}"><input type="number" class="form-control form-control-sm ca-input" name="results[${i}][ca_score]" min="0" max="40" value="${existing ? existing.ca_score : ''}" data-row="${i}"></td>
                        <td><input type="number" class="form-control form-control-sm exam-input" name="results[${i}][exam_score]" min="0" max="60" value="${existing ? existing.exam_score : ''}" data-row="${i}"></td>
                        <td class="fw-bold total-cell" id="total_${i}">${existing ? existing.total_score : ''}</td>
                    </tr>`;
                });
                document.getElementById('studentCount').textContent = students.length + ' students';
                document.querySelectorAll('.ca-input, .exam-input').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const row = this.dataset.row;
                        const ca = parseFloat(document.querySelector(`.ca-input[data-row="${row}"]`).value) || 0;
                        const exam = parseFloat(document.querySelector(`.exam-input[data-row="${row}"]`).value) || 0;
                        document.getElementById('total_' + row).textContent = ca + exam;
                    });
                });
            }
            document.getElementById('resultsForm').style.display = 'block';
        });
});
</script>
@endpush
