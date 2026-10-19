<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->reference ?: ($payment->rrr ?: $payment->id) }}</title>
    <style>
        @page { margin: 15mm 15mm 19mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #27343b; font-family: Helvetica, Arial, sans-serif; font-size: 11px; }
        .watermark { position: fixed; top: 42%; left: 21%; width: 58%; opacity: .045; z-index: -1; }
        .container { position: relative; padding: 0 2px; }
        .header { border-bottom: 2px solid #0a6b46; padding-bottom: 13px; text-align: center; }
        .header img { width: 88px; height: 88px; object-fit: contain; margin-bottom: 7px; }
        .university { margin: 0; color: #000080; font-size: 25px; font-weight: bold; letter-spacing: .25px; text-transform: uppercase; }
        .unit { margin: 4px 0 0; color: #39474e; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .motto { margin: 3px 0 0; color: #c20707; font-size: 15px; font-style: italic; font-weight: bold; }
        .contact { margin: 5px 0 0; color: #66737a; font-size: 10px; }
        .heading { margin: 21px 0 18px; text-align: center; }
        .heading strong { display: inline-block; padding-bottom: 6px; color: #333; border-bottom: 1px solid #c8ced2; font-size: 23px; letter-spacing: .75px; text-transform: uppercase; }
        .receipt-no { margin: 6px 0 0; color: #65747d; font-family: 'Courier New', monospace; font-size: 10px; letter-spacing: 1px; text-align: center; }
        .details, .items { width: 100%; border-collapse: collapse; }
        .details-wrap { overflow: hidden; border: 1px solid #e3e8ec; border-radius: 7px; }
        .details td { border-bottom: 1px solid #edf1f3; padding: 8px 14px; vertical-align: top; }
        .details tr:last-child td { border-bottom: 0; }
        .details .label { width: 38%; color: #475569; font-size: 10px; font-weight: bold; }
        .details .value { color: #17212a; text-align: right; font-size: 11px; }
        .items { margin-top: 25px; }
        .items th, .items td { border: 1px solid #dce2e5; padding: 10px 13px; text-align: left; }
        .items th { background: #f3f6f7; color: #33434b; font-size: 10px; }
        .right { text-align: right !important; }
        .total td { border-top: 2px solid #0a6b46; color: #143b2b; font-size: 14px; font-weight: bold; }
        .words { margin-top: 19px; padding: 12px 14px; border: 1px solid #e0e5e6; border-radius: 7px; background: #f8faf9; text-align: center; }
        .words strong { color: #37474f; font-weight: bold; }
        .words em { color: #4c5b61; font-style: italic; text-transform: capitalize; }
        .status { display: inline-block; margin-top: 9px; padding: 4px 12px; color: #166534; background: #dcfce7; border-radius: 12px; font-size: 9px; font-weight: bold; letter-spacing: .25px; }
        .note { margin: 18px 0 0; color: #68757b; font-size: 9px; line-height: 1.45; text-align: center; }
        .footer { position: fixed; right: 15mm; bottom: 7mm; left: 15mm; border-top: 1px solid #dce2e4; padding-top: 7px; color: #7a8589; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
@php
    $payerName = $payer?->full_name ?: trim(implode(' ', array_filter([
        $payer?->surname,
        $payer?->first_name,
        $payer?->middle_name,
        $payer?->other_names,
    ])));
    $identifier = $payer?->registration_number ?: ($payer?->application_number ?: 'Not provided');
    $programme = $payer?->programme?->name ?: ($payer?->programme_type ?: 'Not provided');
    $programmeType = $payer?->programme_type ?: 'Not provided';
    $session = $payment->academicSession?->name ?: ($payer?->academicSession?->name ?: 'Not provided');
    $reference = trim((string) ($payment->reference ?: ''));
    $rrr = trim((string) ($payment->rrr ?: ''));
    $orderId = trim((string) ($payment->order_id ?: ''));
    $paymentReference = $reference ?: ($rrr ?: ($orderId ?: 'System-generated receipt'));
    $transactionReference = $orderId && !in_array($orderId, [$reference, $rrr], true) ? $orderId : null;
    $paymentType = $payment->paymentType?->name ?: ucfirst(str_replace('_', ' ', (string) $payment->payment_type));
    $paymentType = trim($paymentType) ?: 'Payment';
    $description = trim((string) ($payment->description ?: $paymentType)) ?: $paymentType;
    $receiptNumber = 'RN' . str_pad(substr((string) $payment->id, 0, 8), 8, '0', STR_PAD_LEFT);
    $paidAt = $payment->paid_at ?: ($payment->verified_at ?: $payment->updated_at);
    $paymentDate = $paidAt?->format('d F, Y h:i A');
    $sponsor = trim((string) ($payment->sponsor ?: ''));
    $universityName = setting('receipt_university_name', 'University of Maiduguri');
    $unitName = setting('sbrs_school_name', 'School of Basic and Remedial Studies');
@endphp

@if ($logoBase64)<img src="{{ $logoBase64 }}" class="watermark" alt="">@endif
<div class="container">
    <div class="header">
        @if($logoBase64)<img src="{{ $logoBase64 }}" alt="University logo">@endif
        <p class="university">{{ $universityName }}</p>
        <p class="unit">{{ $unitName }}</p>
        <p class="motto">...Centre of Excellence</p>
        <p class="contact">P.M.B. 1069, Maiduguri, Borno State, Nigeria</p>
    </div>

    <div class="heading"><strong>Payment Receipt</strong></div>
    <div class="receipt-no">{{ $receiptNumber }}</div>
    <div style="text-align:center"><span class="status">PAYMENT VERIFIED</span></div>

    <div class="details-wrap">
        <table class="details">
            <tr><td class="label">Billed to</td><td class="value">{{ $payerName ?: 'Not provided' }}</td></tr>
            <tr><td class="label">Student / application number</td><td class="value">{{ $identifier }}</td></tr>
            <tr><td class="label">Programme</td><td class="value">{{ $programme }}</td></tr>
            <tr><td class="label">Programme type</td><td class="value">{{ $programmeType }}</td></tr>
            <tr><td class="label">Academic session</td><td class="value">{{ $session }}</td></tr>
            <tr><td class="label">Payment type</td><td class="value">{{ $paymentType }}</td></tr>
            @if ($sponsor)<tr><td class="label">Sponsor</td><td class="value">{{ ucfirst($sponsor) }}</td></tr>@endif
            <tr><td class="label">Payment date</td><td class="value">{{ $paymentDate ?: 'Not provided' }}</td></tr>
            <tr><td class="label">Payment reference</td><td class="value">{{ $paymentReference }}</td></tr>
            @if ($rrr)<tr><td class="label">Remita RRR</td><td class="value">{{ $rrr }}</td></tr>@endif
            @if ($transactionReference)<tr><td class="label">Transaction / order ID</td><td class="value">{{ $transactionReference }}</td></tr>@endif
        </table>
    </div>

    <table class="items">
        <thead><tr><th>Description</th><th class="right">Amount</th></tr></thead>
        <tbody><tr><td>{{ $description }}</td><td class="right">NGN {{ number_format((float) $payment->amount, 2) }}</td></tr></tbody>
        <tfoot><tr class="total"><td>Total paid</td><td class="right">NGN {{ number_format((float) $payment->amount, 2) }}</td></tr></tfoot>
    </table>

    <div class="words"><strong>Amount in words:</strong> <em>{{ $amountInWords }}</em></div>
    <p class="note">This receipt confirms a successful payment recorded in the School of Basic and Remedial Studies portal. Keep it for your records; no signature is required.</p>
</div>
<div class="footer">This is a computer-generated receipt from the {{ $unitName }} portal.</div>
</body>
</html>
