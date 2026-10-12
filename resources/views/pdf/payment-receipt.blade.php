<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->reference ?: ($payment->rrr ?: $payment->id) }}</title>
    <style>
        @page { margin: 20mm 16mm 18mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #29323a; font-family: Helvetica, Arial, sans-serif; font-size: 11px; }
        .watermark { position: fixed; top: 35%; left: 16%; width: 68%; opacity: .045; }
        .container { position: relative; padding: 2px; }
        .header { border-bottom: 2px solid #0a6b46; padding-bottom: 12px; text-align: center; }
        .header img { width: 72px; height: 72px; object-fit: contain; margin-bottom: 5px; }
        .university { margin: 0; color: #0a6b46; font-size: 23px; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; }
        .unit { margin: 3px 0 0; color: #4e5c63; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .motto { margin: 2px 0 0; color: #c20707; font-size: 14px; font-style: italic; }
        .contact { margin: 5px 0 0; color: #68747b; font-size: 9px; }
        .heading { margin: 20px 0 4px; text-align: center; }
        .heading strong { color: #25333c; font-size: 20px; letter-spacing: .7px; text-transform: uppercase; }
        .receipt-no { margin: 5px 0 18px; color: #65747d; font-family: 'Courier New', monospace; font-size: 10px; letter-spacing: 1px; text-align: center; }
        .details, .items { width: 100%; border-collapse: collapse; }
        .details-wrap { overflow: hidden; border: 1px solid #e1e6e8; border-radius: 7px; }
        .details td { border-bottom: 1px solid #edf0f1; padding: 7px 11px; }
        .details tr:last-child td { border-bottom: 0; }
        .label { width: 34%; color: #39454d; font-weight: bold; }
        .value { color: #505d64; text-align: right; }
        .items { margin-top: 20px; }
        .items th, .items td { border: 1px solid #dce2e4; padding: 9px 11px; }
        .items th { background: #f3f6f7; color: #35434b; text-align: left; }
        .right { text-align: right; }
        .total td { border-top: 2px solid #0a6b46; color: #142b20; font-size: 14px; font-weight: bold; }
        .words { margin-top: 17px; border: 1px solid #e0e5e6; border-radius: 7px; background: #f8faf9; padding: 12px; text-align: center; }
        .words strong { display: block; margin-bottom: 3px; color: #37474f; }
        .words em { color: #5d6a6e; text-transform: capitalize; }
        .signatures { width: 100%; margin-top: 45px; }
        .signatures td { width: 50%; padding: 0 25px; text-align: center; }
        .line { border-top: 1px solid #46545b; padding-top: 5px; }
        .footer { position: fixed; right: 0; bottom: 0; left: 0; border-top: 1px solid #dce2e4; padding-top: 7px; color: #7a8589; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
@php
    $logoPath = setting('logo');
    $logoFile = $logoPath && !str_starts_with((string) $logoPath, 'http')
        ? storage_path('app/public/' . ltrim((string) $logoPath, '/'))
        : public_path('assets/images/favicon.png');
    $logoSrc = is_file($logoFile) ? 'data:image/' . strtolower(pathinfo($logoFile, PATHINFO_EXTENSION)) . ';base64,' . base64_encode(file_get_contents($logoFile)) : null;
    $payerName = $payer?->full_name ?: trim(($payer?->surname ?? '') . ' ' . ($payer?->first_name ?? '') . ' ' . ($payer?->middle_name ?? '') . ' ' . ($payer?->other_names ?? ''));
    $identifier = $payer?->registration_number ?? $payer?->application_number ?? 'N/A';
    $programme = $payer?->programme?->name ?? $payer?->programme_type ?? 'N/A';
    $reference = $payment->reference ?: ($payment->rrr ?: $payment->id);
    $receiptNumber = 'RN' . str_pad((string) $payment->id, 8, '0', STR_PAD_LEFT);
@endphp

<img src="{{ $logoSrc ?: '' }}" class="watermark" alt="">
<div class="container">
    <div class="header">
        @if($logoSrc)<img src="{{ $logoSrc }}" alt="University logo">@endif
        <p class="university">University of Maiduguri</p>
        <p class="unit">School of Basic and Remedial Studies</p>
        <p class="motto">...Centre of Excellence</p>
        <p class="contact">School of Basic and Remedial Studies &nbsp;|&nbsp; P.M.B. 1069, Maiduguri, Borno State, Nigeria</p>
    </div>

    <div class="heading"><strong>Payment Receipt</strong></div>
    <div class="receipt-no">{{ $receiptNumber }}</div>

    <div class="details-wrap">
        <table class="details">
            <tr><td class="label">Billed to</td><td class="value">{{ $payerName ?: 'N/A' }}</td></tr>
            <tr><td class="label">Student/Application ID</td><td class="value">{{ $identifier }}</td></tr>
            <tr><td class="label">Programme</td><td class="value">{{ $programme }}</td></tr>
            <tr><td class="label">Academic session</td><td class="value">{{ $payment->academicSession?->name ?: 'N/A' }}</td></tr>
            <tr><td class="label">Reference</td><td class="value">{{ $reference }}</td></tr>
            <tr><td class="label">Payment method</td><td class="value">{{ $payment->payment_method ?: 'Online' }}</td></tr>
            @if($payment->sponsor)<tr><td class="label">Sponsor</td><td class="value">{{ ucfirst($payment->sponsor) }}</td></tr>@endif
            <tr><td class="label">Date paid</td><td class="value">{{ optional($payment->paid_at ?: $payment->updated_at)->format('d F, Y') }}</td></tr>
        </table>
    </div>

    <table class="items">
        <thead><tr><th>Description</th><th class="right">Amount</th></tr></thead>
        <tbody><tr><td>{{ $payment->description ?: ($payment->paymentType?->name ?: ucfirst($payment->payment_type)) }}</td><td class="right">NGN {{ number_format((float) $payment->amount, 2) }}</td></tr></tbody>
        <tfoot><tr class="total"><td>Total paid</td><td class="right">NGN {{ number_format((float) $payment->amount, 2) }}</td></tr></tfoot>
    </table>

    <div class="words"><strong>Amount in words</strong><em>{{ $amountInWords }}</em></div>

    <table class="signatures"><tr><td><div class="line">Cashier / Recording Officer</div></td><td><div class="line">For Bursar</div></td></tr></table>
</div>
<div class="footer">This receipt was generated from the University of Maiduguri SBRS portal. Keep it for your records.</div>
</body>
</html>
