<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Letter - {{ $applicant->application_number }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; margin: 30px 40px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 5px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 22px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .header .subtitle { font-size: 11px; margin: 0; }
        .header h2 { font-size: 15px; margin: 2px 0; text-transform: uppercase; font-weight: bold; }
        .header .pmb { font-size: 12px; margin: 2px 0; }
        .header .director { font-size: 11px; margin: 3px 0 0; }
        .contacts { font-size: 10px; margin: 5px 0 0; }
        .ref-block { margin: 15px 0 10px; font-size: 12px; }
        .ref-block p { margin: 1px 0; }
        .title { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 15px 0; text-decoration: underline; }
        .body-text { font-size: 12px; text-align: justify; }
        .body-text p { margin: 8px 0; }
        .admission-details { margin: 10px 0; padding-left: 15px; font-size: 12px; }
        .admission-details p { margin: 2px 0; }
        .conditions { margin: 8px 0 8px 20px; font-size: 12px; }
        .conditions p { margin: 2px 0; text-indent: -15px; padding-left: 15px; }
        .registration { margin: 10px 0; font-size: 12px; }
        .registration p { margin: 2px 0; text-indent: -8px; padding-left: 8px; }
        .signature { margin-top: 30px; font-size: 12px; }
        .signature p { margin: 1px 0; }
        .date-right { float: right; font-size: 12px; }
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

    <div class="header">
        <h1>University of Maiduguri</h1>
        <p class="subtitle">(OFFICE OF THE VICE-CHANCELLOR)</p>
        <h2>School of Basic and Remedial Studies</h2>
        <p class="pmb">P.M.B. 1069</p>
        <p class="director"><strong>{{ setting('director_name', 'PROFESSOR ABDULKARIM ISHAQ') }}</strong>, {{ setting('director_title', 'B.A Ed, M.Ed (Curriculum & Instruction), Ph.D (Curriculum & Instruction).') }}</p>
        <p class="contacts">
            &#9993; {{ setting('director_email', 'sbrs@unimaid.edu.ng') }} &nbsp;&nbsp;
            &#9742; {{ setting('director_phone', '+2348035837228') }}
        </p>
    </div>

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
            <p>2. Submission of original and certified copies of academic documents (O'Level,)</p>
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
        <br><br>
        <p><strong>{{ setting('director_name', 'Professor Abdulkarim Ishaq') }}</strong></p>
        <p><em>Director</em></p>
    </div>
</body>
</html>
