<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Course Registration Form - {{ $student->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 14px; color: #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .header img { height: 70px; margin-bottom: 5px; }
        .header h2 { font-size: 18px; text-transform: uppercase; margin-bottom: 2px; }
        .header h3 { font-size: 16px; margin-bottom: 2px; }
        .header h4 { font-size: 14px; font-weight: normal; }
        .student-info { margin-bottom: 15px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 4px 8px; }
        .student-info .label { font-weight: bold; width: 180px; }
        .courses-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .courses-table th, .courses-table td { border: 1px solid #000; padding: 8px 10px; text-align: left; }
        .courses-table th { background: #f0f0f0; font-weight: bold; }
        .courses-table .sn { width: 40px; text-align: center; }
        .courses-table .code { width: 120px; }
        .courses-table .credits { width: 60px; text-align: center; }
        .courses-table .semester { width: 80px; text-align: center; }
        .courses-table .signature { width: 160px; }
        .courses-table .date-col { width: 100px; }
        .summary { margin-bottom: 20px; font-weight: bold; }
        .signatures { margin-top: 40px; }
        .signatures table { width: 100%; }
        .signatures td { padding: 30px 10px 5px; width: 33%; }
        .sig-line { border-top: 1px solid #000; text-align: center; padding-top: 5px; font-size: 12px; }
        .print-btn { position: fixed; top: 10px; right: 10px; background: #006633; color: #fff; border: none; padding: 10px 20px; font-size: 14px; cursor: pointer; border-radius: 5px; }
        .print-btn:hover { background: #004d26; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print Form</button>

    <div class="header">
        @php $logoPath = setting('logo'); @endphp
        @if($logoPath)
            <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo">
        @endif
        <h2>{{ setting('site_name', 'University of Maiduguri') }}</h2>
        <h3>School of Basic and Remedial Studies</h3>
        <h4>Course Registration Form</h4>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Name:</td>
                <td>{{ $student->name }}</td>
                <td class="label">Reg. Number:</td>
                <td>{{ $student->registration_number ?? $student->matric_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Programme:</td>
                <td>{{ $student->programme->name ?? 'N/A' }}</td>
                <td class="label">Session:</td>
                <td>{{ $session->name ?? 'N/A' }}</td>
            </tr>
            @if($student->subjectCombination)
            <tr>
                <td class="label">Subject Combination:</td>
                <td colspan="3">{{ $student->subjectCombination->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="courses-table">
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th class="code">Course Code</th>
                <th>Course Title</th>
                <th class="credits">Credits</th>
                <th class="semester">Semester</th>
                <th class="signature">Coordinator Signature</th>
                <th class="date-col">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registeredCourses as $index => $reg)
            <tr>
                <td class="sn">{{ $index + 1 }}</td>
                <td class="code">{{ $reg->course->course_code ?? 'N/A' }}</td>
                <td>{{ $reg->course->course_title ?? 'N/A' }}</td>
                <td class="credits">{{ $reg->course->credit_units ?? 0 }}</td>
                <td class="semester">{{ ucfirst($reg->semester) }}</td>
                <td class="signature"></td>
                <td class="date-col"></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total Courses: {{ $registeredCourses->count() }} |
        Total Credit Units: {{ $registeredCourses->sum(function($r) { return $r->course->credit_units ?? 0; }) }}
    </div>

    <div class="signatures">
        <table>
            <tr>
                <td><div class="sig-line">Student's Signature & Date</div></td>
                <td><div class="sig-line">Head of Department</div></td>
                <td><div class="sig-line">Dean / Director</div></td>
            </tr>
        </table>
    </div>
</body>
</html>
