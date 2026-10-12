<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Hostel Accommodation Permit - {{ $student->registration_number }}</title>
    <style>
        @page { margin: 18mm 16mm 16mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172b3a; font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.45; }
        .header { border-bottom: 2px solid #0a6b46; padding-bottom: 10px; text-align: center; }
        .header img { width: 58px; height: 58px; object-fit: contain; margin-bottom: 4px; }
        .header h1 { margin: 0; color: #0a6b46; font-size: 20px; letter-spacing: .4px; text-transform: uppercase; }
        .header h2 { margin: 2px 0 0; color: #34495e; font-size: 13px; text-transform: uppercase; }
        .header p { margin: 3px 0 0; color: #687b87; font-size: 10px; }
        .title { margin: 18px 0 14px; text-align: center; }
        .title h3 { display: inline-block; margin: 0; border: 1px solid #0a6b46; border-radius: 4px; padding: 7px 18px; color: #0a6b46; font-size: 14px; letter-spacing: .7px; text-transform: uppercase; }
        .intro { margin: 0 0 13px; text-align: justify; }
        .student-card { width: 100%; border: 1px solid #c9d8d1; border-radius: 5px; background: #f7fbf9; }
        .student-card td { padding: 7px 9px; vertical-align: top; }
        .student-card .photo-cell { width: 100px; text-align: center; }
        .student-photo { width: 82px; height: 96px; border: 1px solid #b9cbc2; object-fit: cover; }
        .label { width: 24%; color: #60756c; font-size: 9px; text-transform: uppercase; }
        .value { color: #132d21; font-weight: bold; }
        .section-heading { margin: 16px 0 6px; border-bottom: 1px solid #aac7b8; padding-bottom: 4px; color: #0a6b46; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .details { width: 100%; border-collapse: collapse; }
        .details th, .details td { border: 1px solid #c8d7d0; padding: 7px 8px; text-align: left; }
        .details th { width: 25%; background: #eef6f1; color: #476055; font-size: 9px; text-transform: uppercase; }
        .conditions { margin: 7px 0 0 16px; padding: 0; }
        .conditions li { margin: 4px 0; }
        .signature { margin-top: 36px; width: 48%; text-align: center; }
        .signature .line { border-top: 1px solid #486256; margin-bottom: 5px; }
        .signature p { margin: 0; font-size: 9px; }
        .footer { margin-top: 18px; border-top: 1px solid #d1ded7; padding-top: 7px; color: #71827a; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
@php
    $fullName = $student->full_name ?: trim(($student->surname ?? '') . ' ' . ($student->first_name ?? '') . ' ' . ($student->middle_name ?? ''));
    $logoPath = setting('logo');
    $logoFile = $logoPath && !str_starts_with((string) $logoPath, 'http')
        ? storage_path('app/public/' . ltrim((string) $logoPath, '/'))
        : public_path('assets/images/favicon.png');
    $logoSrc = is_file($logoFile) ? 'data:image/' . strtolower(pathinfo($logoFile, PATHINFO_EXTENSION)) . ';base64,' . base64_encode(file_get_contents($logoFile)) : null;
    $photoFile = $student->passport_photo ? storage_path('app/public/' . ltrim((string) $student->passport_photo, '/')) : null;
    $photoExt = $photoFile ? strtolower(pathinfo($photoFile, PATHINFO_EXTENSION)) : 'jpeg';
    $photoMime = in_array($photoExt, ['png', 'gif'], true) ? 'image/' . $photoExt : 'image/jpeg';
    $photoSrc = $photoFile && is_file($photoFile) ? 'data:' . $photoMime . ';base64,' . base64_encode(file_get_contents($photoFile)) : null;
    $sessionName = $student->academicSession?->name ?: 'Current Academic Session';
@endphp

<div class="header">
    @if($logoSrc)<img src="{{ $logoSrc }}" alt="University logo">@endif
    <h1>{{ setting('site_name', 'University of Maiduguri') }}</h1>
    <h2>School of Basic and Remedial Studies</h2>
    <p>Student Affairs Division &nbsp;|&nbsp; P.M.B. 1069, Maiduguri</p>
</div>

<div class="title"><h3>Student Accommodation Permit</h3></div>

<p class="intro">This permit confirms that the student named below has been allocated the stated bed space for the <strong>{{ $sessionName }}</strong> academic session. It should be presented to the Hall Officer when requested.</p>

<table class="student-card">
    <tr>
        <td class="photo-cell" rowspan="3">
            @if($photoSrc)<img class="student-photo" src="{{ $photoSrc }}" alt="Student photo">@else<div class="student-photo"></div>@endif
        </td>
        <td class="label">Student name</td><td class="value">{{ strtoupper($fullName ?: 'N/A') }}</td>
    </tr>
    <tr><td class="label">Registration number</td><td class="value">{{ $student->registration_number }}</td></tr>
    <tr><td class="label">Programme</td><td class="value">{{ $student->programme?->name ?: $student->programme_type }}</td></tr>
</table>

<div class="section-heading">Accommodation details</div>
<table class="details">
    <tr><th>Hall</th><td>{{ $reservation['hall'] ?? 'N/A' }}</td><th>Category</th><td>{{ $reservation['category'] ?? 'N/A' }}</td></tr>
    <tr><th>Block</th><td>{{ $reservation['block'] ?? 'N/A' }}</td><th>Room</th><td>{{ $reservation['room'] ?? 'N/A' }}</td></tr>
    <tr><th>Bed</th><td>{{ $reservation['bed'] ?? 'N/A' }}</td><th>Hostel fee</th><td>₦{{ number_format((float) ($reservation['amount'] ?? 0), 2) }}</td></tr>
    <tr><th>Payment status</th><td><strong>PAID</strong></td><th>Issued on</th><td>{{ now()->format('d M Y') }}</td></tr>
</table>

<div class="section-heading">Conditions of use</div>
<ol class="conditions">
    <li>This allocation is personal and must not be sold, transferred or occupied by another person.</li>
    <li>The student must comply with the hostel rules and the instructions of the Hall Officer.</li>
    <li>The allocation may be withdrawn for misconduct, false information or breach of the tenancy conditions.</li>
</ol>

<table style="width:100%; margin-top: 24px;"><tr><td>
    <div class="signature"><div class="line"></div><p><strong>Student signature</strong></p><p>{{ $fullName }}</p></div>
</td><td>
    <div class="signature"><div class="line"></div><p><strong>Authorised officer</strong></p><p>Student Affairs Division</p></div>
</td></tr></table>

<div class="footer">Keep this permit safe. It is valid only for the stated student, bed space and academic session.</div>
</body>
</html>
