<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hostel Accommodation Permit - {{ $student->registration_number }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #f5f6f7; color: #232f3b; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; line-height: 1.35; }
        .page { width: 210mm; height: 280mm; min-height: 280mm; margin: 0 auto; padding: 8mm; position: relative; overflow: hidden; background: #fff; border: .35mm solid #cdd2d6; }
        .gold-bar { height: 2mm; margin: -8mm -8mm 7mm; background: #bc913d; }
        .header { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .header-logo { width: 21mm; height: 22mm; object-fit: contain; }
        .header-copy { padding: 1mm 0 0 5mm; }
        .university { margin: 0; color: #23303b; font-size: 19px; line-height: 1.15; letter-spacing: .15px; }
        .centre { margin: 1mm 0 0; color: #ae4341; font-size: 11px; font-style: italic; font-weight: bold; }
        .division { margin: 1mm 0 0; color: #687078; font-size: 8px; }
        .reference { width: 42mm; padding: 3mm 2mm 2mm; border: .25mm solid #ddcca6; background: #f8f5ed; text-align: center; }
        .reference-label { color: #916b28; font-size: 7px; font-weight: bold; }
        .reference-number { margin: 1.2mm 0; color: #39362f; font-size: 11px; font-weight: bold; }
        .reference-session { color: #706959; font-size: 6.8px; }
        .rule { height: .25mm; margin: 5mm 0 6mm; background: #dadee1; }
        .title-row { width: 100%; border-collapse: collapse; }
        .title-row td { vertical-align: top; }
        .title { margin: 0; color: #23303b; font-size: 16px; line-height: 1.2; }
        .subtitle { margin: 1.2mm 0 0; color: #687078; font-size: 8.2px; }
        .paid { width: 40mm; padding: 3mm 1.5mm; background: #2a704f; color: #fff; font-size: 7.5px; font-weight: bold; text-align: center; }
        .blue-rule { height: 1.2mm; margin: 2mm 0 7mm; background: #284862; }
        .panel { width: 100%; border: .25mm solid #dadee1; border-collapse: collapse; }
        .panel td { vertical-align: top; }
        .panel-title { padding: 2.5mm 3mm; background: #f8f8f6; color: #23303b; font-size: 8.5px; font-weight: bold; }
        .identity { height: 61mm; }
        .identity-copy { width: 75%; padding: 4mm 3mm 2mm; }
        .identity-photo { width: 25%; padding: 4mm 3mm 1mm 0; text-align: center; }
        .name { margin: 0; color: #23303b; font-size: 14px; font-weight: bold; line-height: 1.2; }
        .registration { margin: 2mm 0 0; color: #976f29; font-size: 9.5px; font-weight: bold; }
        .identity-rule { height: .2mm; margin: 2mm 0 3mm; background: #e4e6e7; }
        .field-grid { width: 100%; border-collapse: collapse; }
        .field-grid td { width: 50%; padding: 0 2mm 3mm 0; }
        .label { display: block; color: #676f76; font-size: 7px; text-transform: uppercase; }
        .value { display: block; margin-top: 1mm; color: #23303b; font-size: 8.7px; font-weight: bold; word-wrap: break-word; }
        .photo-frame { width: 34mm; height: 44mm; padding: 1mm; border: .25mm solid #b6bdc2; background: #f4f4f2; }
        .photo-frame img { width: 100%; height: 100%; object-fit: cover; }
        .photo-caption { margin: 1mm 0 0; color: #676f76; font-size: 6.8px; text-align: center; }
        .section { margin: 8mm 0 0; border: .25mm solid #d8dcdf; }
        .section-head { padding: 2.5mm 3mm; border-bottom: .25mm solid #e1e3e4; background: #f7f7f4; color: #23303b; font-size: 8.5px; font-weight: bold; }
        .section-head .accent { display: inline-block; width: 1.3mm; height: 5mm; margin-right: 2mm; vertical-align: middle; background: #bc913d; }
        .allocation { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .allocation td { width: 25%; padding: 2.5mm 2mm 3mm; border-right: .25mm solid #e2e4e5; vertical-align: top; }
        .allocation td:last-child { border-right: 0; }
        .allocation .label { font-size: 7px; text-align: center; }
        .allocation .value { min-height: 9mm; font-size: 10px; text-align: center; word-wrap: break-word; }
        .allocation td:first-child .label, .allocation td:first-child .value { text-align: left; }
        .lower { width: 100%; margin-top: 8mm; border-collapse: separate; border-spacing: 6mm 0; }
        .lower td { width: 60%; padding: 3mm; border: .25mm solid #dedfe1; background: #f9f9f7; vertical-align: top; }
        .lower td:last-child { width: 40%; background: #fff; text-align: center; }
        .lower-title { margin: 0 0 2mm; color: #23303b; font-size: 8px; font-weight: bold; }
        .lower-copy { margin: 0; color: #535e66; font-size: 7.5px; line-height: 1.45; }
        .authorised { margin: 0 0 1mm; color: #676f76; font-size: 7px; }
        .signature { width: 42mm; height: 9mm; object-fit: contain; }
        .signature-line { height: .2mm; margin: 1mm 2mm 1mm; background: #5c656c; }
        .signatory { margin: 0; color: #23303b; font-size: 7.8px; font-weight: bold; }
        .signatory-role { margin: 1mm 0 0; color: #676f76; font-size: 6.6px; }
        .footer { position: absolute; right: 8mm; bottom: 6mm; left: 8mm; padding-top: 3mm; border-top: .25mm solid #dadee1; color: #687078; font-size: 7px; text-align: center; }
        .footer em { display: block; margin-top: 1mm; font-size: 6.7px; }
    </style>
</head>
<body>
@php
    $fullName = trim((string) ($student->full_name ?: trim(($student->surname ?? '') . ' ' . ($student->first_name ?? '') . ' ' . ($student->middle_name ?? ''))));
    $registration = trim((string) $student->registration_number);
    $sessionName = $student->academicSession?->name ?: 'Current academic session';
    $permitNumber = 'HP-' . str_pad((string) ($reservation['id'] ?? substr(md5($registration), 0, 6)), 6, '0', STR_PAD_LEFT);
    $logoFile = public_path('assets/images/unimaid-logo.png');
    $signatureFile = public_path('assets/images/hostel-signature.png');
    $photoFile = $student->passport_photo ? storage_path('app/public/' . ltrim((string) $student->passport_photo, '/')) : null;
    $toDataUri = static function (?string $file, string $fallbackMime = 'image/jpeg'): ?string {
        if (!$file || !is_file($file)) return null;
        $mime = function_exists('mime_content_type') ? (mime_content_type($file) ?: $fallbackMime) : $fallbackMime;
        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($file));
    };
    $logoSrc = $toDataUri($logoFile, 'image/png');
    $signatureSrc = $toDataUri($signatureFile, 'image/png');
    $photoSrc = $toDataUri($photoFile, 'image/jpeg');
    $gender = strtoupper(trim((string) ($student->gender ?? '')));
    $genderLabel = in_array($gender, ['M', 'MALE', '1'], true) ? 'Male' : (in_array($gender, ['F', 'FEMALE', '2'], true) ? 'Female' : 'Not provided');
    $programme = trim((string) ($student->programme?->name ?: $student->programme_type ?: 'School of Basic and Remedial Studies'));
    $value = static fn ($key, $fallback = '-') => trim((string) ($reservation[$key] ?? '')) !== '' ? (string) $reservation[$key] : $fallback;
@endphp

<div class="page">
    <div class="gold-bar"></div>
    <table class="header"><tr>
        <td style="width: 14%;">@if($logoSrc)<img class="header-logo" src="{{ $logoSrc }}" alt="University logo">@endif</td>
        <td class="header-copy" style="width: 64%;"><h1 class="university">UNIVERSITY OF MAIDUGURI</h1><p class="centre">...Centre of Excellence</p><p class="division">STUDENT AFFAIRS DIVISION</p></td>
        <td class="reference"><div class="reference-label">PERMIT REFERENCE</div><div class="reference-number">{{ $permitNumber }}</div><div class="reference-session">{{ $sessionName }}</div></td>
    </tr></table>
    <div class="rule"></div>
    <table class="title-row"><tr><td><h1 class="title">HOSTEL ACCOMMODATION PERMIT</h1><p class="subtitle">Official authorisation for residence in a University hostel</p></td><td style="width: 42mm;"><div class="paid">PAID &amp; CONFIRMED</div></td></tr></table>
    <div class="blue-rule"></div>

    <table class="panel identity"><tr><td colspan="2" class="panel-title">STUDENT IDENTIFICATION</td></tr><tr>
        <td class="identity-copy"><p class="name">{{ strtoupper($fullName ?: 'Not provided') }}</p><p class="registration">{{ $registration ?: 'Not provided' }}</p><div class="identity-rule"></div><table class="field-grid"><tr><td><span class="label">Programme / school</span><span class="value">{{ $programme }}</span></td><td><span class="label">Category</span><span class="value">SBRS / Remedial</span></td></tr><tr><td><span class="label">Gender</span><span class="value">{{ $genderLabel }}</span></td><td><span class="label">Issued</span><span class="value">{{ now()->format('d F Y') }}</span></td></tr></table></td>
        <td class="identity-photo"><div class="photo-frame">@if($photoSrc)<img src="{{ $photoSrc }}" alt="Student photograph">@endif</div><p class="photo-caption">STUDENT PHOTOGRAPH</p></td>
    </tr></table>

    <div class="section"><div class="section-head"><span class="accent"></span>ALLOCATED BED SPACE <span style="float:right;color:#676f76;font-weight:normal;">HOSTEL LOCATION</span></div><table class="allocation"><tr><td><span class="label">Hall</span><span class="value">{{ strtoupper($value('hall')) }}</span></td><td><span class="label">Block</span><span class="value">{{ strtoupper($value('block')) }}</span></td><td><span class="label">Room</span><span class="value">{{ $value('room') }}</span></td><td><span class="label">Bed</span><span class="value">{{ $value('bed') }}</span></td></tr></table></div>

    <table class="lower"><tr><td><p class="lower-title">IMPORTANT</p><p class="lower-copy">This permit is personal and cannot be sold, transferred, altered, or used by another person. Hostel rules remain applicable.</p></td><td><p class="authorised">AUTHORISED BY</p>@if($signatureSrc)<img class="signature" src="{{ $signatureSrc }}" alt="Authorisation signature">@endif<div class="signature-line"></div><p class="signatory">Prof. Amadu M. Brono</p><p class="signatory-role">Dean, Student Affairs</p></td></tr></table>
    <div class="footer">Verify the student identity and allocated bed before admission into the hostel.<em>Generated from the University of Maiduguri Student Portal</em></div>
</div>
</body>
</html>
