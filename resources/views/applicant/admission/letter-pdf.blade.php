<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Letter - {{ $applicant->application_number }}</title>
    <style>
        @page { margin: 68mm 17mm 22mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; color: #000; margin: 0; line-height: 1.25; }
        .letterhead { position: fixed; top: -68mm; left: -17mm; width: 210mm; height: auto; z-index: -2; }
        .letterhead-footer { position: fixed; top: 213.09mm; left: -17mm; width: 210mm; height: auto; z-index: -2; }
        .page { position: relative; }
        .watermark { position: fixed; top: 35mm; left: 28mm; width: 120mm; height: auto; opacity: .06; z-index: -1; }
        .page > .header, .page > .ref-block, .page > .title, .page > .body-text, .page > .signature, .page > div:last-child { position: relative; z-index: 1; }
        .ref-block { margin: 0 0 10pt; }
        .ref-block p { margin: 1px 0; }
        .title { text-align: center; font-size: 11pt; font-weight: bold; text-transform: uppercase; margin: 10pt 0; text-decoration: underline; }
        .body-text { text-align: justify; }
        .body-text p { margin: 6pt 0; }
        .admission-details { margin: 5pt 0; padding-left: 12pt; }
        .admission-details p { margin: 2px 0; }
        .conditions { margin: 5pt 0 5pt 15pt; }
        .conditions p { margin: 2px 0; text-indent: -15px; padding-left: 15px; }
        .registration { margin: 5pt 0; }
        .registration p { margin: 2px 0; text-indent: -8px; padding-left: 8px; }
        .signature { margin-top: 10pt; page-break-inside: avoid; }
        .signature-image { display: block; width: 40mm; height: auto; margin: 2mm 0 1mm; }
        .signature p { margin: 1px 0; }
    </style>
</head>
<body>
    @php
        $session = $applicant->academicSession;
        $student = $applicant->student;
        $progName = $applicant->programme_type === 'IJMB'
            ? 'One-Year IJMB Programme'
            : 'One-Year Remedial Programme (Arts/Science)';
        $dateStr = now()->format('jS M. Y');
        $year2 = $session ? substr($session->name, 2, 2) : substr(date('Y'), 2, 2);
    @endphp

    @if(!empty($letterheadDataUri))
        <img class="letterhead" src="{{ $letterheadDataUri }}" alt="University of Maiduguri - School of Basic and Remedial Studies letterhead">
    @endif
    @if(!empty($letterheadFooterDataUri))
        <img class="letterhead-footer" src="{{ $letterheadFooterDataUri }}" alt="">
    @endif
    <div class="page">
    @if(!empty($logoDataUri))
        <img class="watermark" src="{{ $logoDataUri }}" alt="">
    @endif
    <div class="ref-block">
        <p style="float:right;">{{ $dateStr }}</p>
        <p style="clear:left;"><strong>SBRS.{{ $year2 }}/VOL.II</strong></p>
        <p>{{ strtoupper($applicant->surname) }} {{ strtoupper($applicant->first_name) }} {{ strtoupper($applicant->other_names) }}</p>
        @if($student)
        <p><strong>{{ $student->registration_number }}</strong></p>
        @endif
    </div>

    <div class="title">Provisional Admission Into {{ $progName }}</div>

    <div class="body-text">
        <p>I am pleased to inform you that you have been provisionally admitted into the <strong>{{ $progName }}</strong> in {{ $applicant->programme->name ?? 'Science' }} at the School of Basic and Remedial Studies, University of Maiduguri, for the <strong>{{ $session->name ?? '' }}</strong> Academic Session, subject to fulfillment of the conditions outlined below.</p>

        <p><strong><u>Admission Details:</u></strong></p>
        <div class="admission-details">
            <p>- Programme: {{ $progName }}</p>
            <p>- School: Basic and Remedial Studies</p>
            <p>- Duration: One Academic Session</p>
            <p>- Entry Requirements: O'Level results with at least 5 credits, including English and Mathematics</p>
        </div>

        <p><strong><u>Conditions for Admission:</u></strong></p>
        <div class="conditions">
            <p>1. Payment of Acceptance Fee/ Tuition Fees as stipulated by the School of Basic and Remedial Studies</p>
            <p>2. Submission of original and certified copies of academic documents (O'Level)</p>
            <p>3. Primary school certificate</p>
            <p>4. Indigene letter/ Certificate of Birth</p>
            <p>5. Compliance with other University regulations.</p>
        </div>

        <p><strong><u>Registration:</u></strong></p>
        <div class="registration">
            <p>Registration for the programme will take place immediately after the conclusion of admissions. You are required to report with the following documents:</p>
            <p>- Admission letter</p>
            <p>- Evidence of payment/original certificates</p>
            <p>- JAMB result slip if any</p>
        </div>

        <p>Please confirm your acceptance and comply with the admission conditions at the earliest convenience. Congratulations on your admission. We look forward to your registration.</p>
    </div>

    <div class="signature">
        <p>Yours Sincerely,</p>
        @if(!empty($signatureDataUri))
            <img class="signature-image" src="{{ $signatureDataUri }}" alt="Signature">
        @endif
        <p><strong>{{ setting('director_name', 'Professor Abdulkarim Ishaq') }}</strong></p>
        <p><em>Director</em></p>
    </div>
    </div>
</body>
</html>
