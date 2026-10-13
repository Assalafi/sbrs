<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->reference ?: ($payment->rrr ?: $payment->id) }}</title>
    <style>
        @page { margin: 20mm 16mm 18mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #29323a; font-family: Helvetica, Arial, sans-serif; font-size: 11px; }
        .watermark { position: fixed; top: 35%; left: 18%; width: 64%; opacity: .045; }
        .container { position: relative; padding: 2px; }
        .header { border-bottom: 2px solid #0a6b46; padding-bottom: 12px; text-align: center; }
        .header img { width: 76px; height: 76px; object-fit: contain; margin-bottom: 5px; }
        .university { margin: 0; color: #0a6b46; font-size: 23px; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; }
        .unit { margin: 3px 0 0; color: #4e5c63; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .motto { margin: 2px 0 0; color: #c20707; font-size: 14px; font-style: italic; }
        .contact { margin: 5px 0 0; color: #68747b; font-size: 9px; }
        .heading { margin: 20px 0 4px; text-align: center; }
        .heading strong { color: #25333c; font-size: 20px; letter-spacing: .7px; text-transform: uppercase; }
        .receipt-no { margin: 5px 0 18px; color: #65747d; font-family: 'Courier New', monospace; font-size: 10px; letter-spacing: 1px; text-align: center; }
        .details, .items { width: 100%; border-collapse: collapse; }
        .details-wrap { overflow: hidden; border: 1px solid #dbe3ec; border-radius: 5px; }
        .details td { border-bottom: 1px solid #edf1f5; padding: 7px 12px; }
        .details tr:last-child td { border-bottom: 0; }
        .label { width: 34%; color: #64748b; font-size: 9px; font-weight: bold; }
        .value { color: #111827; text-align: right; font-weight: bold; }
        .items { margin-top: 20px; }
        .items th, .items td { border-bottom: 1px solid #e5e7eb; padding: 8px 10px; }
        .items th { background: #f8fafc; color: #475569; font-size: 10px; text-align: left; }
        .right { text-align: right; }
        .total td { border-top: 2px solid #0a6b46; color: #143b2b; font-size: 13px; font-weight: bold; }
        .words { margin-top: 16px; padding: 10px 12px; border: 1px solid #dbe3ec; border-radius: 5px; text-align: center; }
        .words strong { display: block; margin-bottom: 3px; color: #64748b; font-size: 9px; }
        .words em { color: #111827; font-weight: bold; text-transform: capitalize; }
        .status { display: inline-block; margin-top: 7px; padding: 4px 12px; color: #166534; background: #dcfce7; border-radius: 12px; font-size: 9px; font-weight: bold; }
        .footer { position: fixed; right: 0; bottom: 0; left: 0; border-top: 1px solid #dce2e4; padding-top: 7px; color: #7a8589; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
@php
    $payerName = $payer?->full_name ?: trim(($payer?->surname ?? '') . ' ' . ($payer?->first_name ?? '') . ' ' . ($payer?->middle_name ?? '') . ' ' . ($payer?->other_names ?? ''));
    $identifier = $payer?->registration_number ?: ($payer?->application_number ?: 'Not specified');
    $programme = $payer?->programme?->name ?: ($payer?->programme_type ?: 'Not specified');
    $programmeType = $payer?->programme_type ?: 'Not specified';
    $reference = trim((string) ($payment->reference ?: ''));
    $rrr = trim((string) ($payment->rrr ?: ''));
    $orderId = trim((string) ($payment->order_id ?: ''));
    $isOffline = !empty($payment->proof_path) || $payment->payment_method === 'offline';
    $paymentReference = $reference ?: ($rrr ?: ($orderId ?: 'System-generated receipt'));
    $transactionReference = $orderId ?: ($rrr ?: $paymentReference);
    $paymentMethod = trim((string) ($payment->payment_method ?: ''))
        ?: ($isOffline ? 'Offline payment' : (($rrr || $orderId) ? 'Remita' : 'Portal payment'));
    $paymentType = $payment->paymentType?->name ?: ucfirst(str_replace('_', ' ', (string) $payment->payment_type));
    $description = $payment->description ?: $paymentType;
    $receiptNumber = 'RN' . str_pad(substr((string) $payment->id, 0, 8), 8, '0', STR_PAD_LEFT);
    $paymentDate = $payment->paid_at?->format('d F, Y H:i') ?: $payment->updated_at?->format('d F, Y H:i');
@endphp

@if ($logoBase64)<img src="{{ $logoBase64 }}" class="watermark" alt="">@endif
<div class="container">
    <div class="header">
        @if($logoBase64)<img src="{{ $logoBase64 }}" alt="University logo">@endif
        <p class="university">{{ setting('site_name', 'University of Maiduguri') }}</p>
        <p class="unit">{{ setting('sbrs_school_name', 'School of Basic and Remedial Studies') }}</p>
        <p class="motto">...Centre of Excellence</p>
        <p class="contact">P.M.B. 1069, Maiduguri, Borno State, Nigeria</p>
    </div>

    <div class="heading"><strong>Payment Receipt</strong></div>
    <div class="receipt-no">{{ $receiptNumber }}</div>
    <div style="text-align:center"><span class="status">PAYMENT VERIFIED</span></div>

    <div class="details-wrap">
        <table class="details">
            <tr>
                <td><span class="label">Billed to</span><span class="value">{{ $payerName ?: 'Not specified' }}</span></td>
                <td><span class="label">Student / application ID</span><span class="value">{{ $identifier }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Programme</span><span class="value">{{ $programme }}</span></td>
                <td><span class="label">Programme type</span><span class="value">{{ $programmeType }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Academic session</span><span class="value">{{ $payment->academicSession?->name ?: ($payer?->academicSession?->name ?: 'Not specified') }}</span></td>
                <td><span class="label">Payment date</span><span class="value">{{ $paymentDate ?: 'Not specified' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Payment reference</span><span class="value">{{ $paymentReference }}</span></td>
                <td><span class="label">Remita RRR</span><span class="value">{{ $rrr ?: 'Not applicable' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Transaction reference</span><span class="value">{{ $transactionReference }}</span></td>
                <td><span class="label">Payment method</span><span class="value">{{ $paymentMethod }}</span></td>
            </tr>
            @if($payment->sponsor)
                <tr>
                    <td><span class="label">Sponsor</span><span class="value">{{ ucfirst((string) $payment->sponsor) }}</span></td>
                    <td><span class="label">Payment type</span><span class="value">{{ $paymentType }}</span></td>
                </tr>
            @endif
        </table>
    </div>

    <table class="items">
        <thead><tr><th>Description</th><th class="right">Amount</th></tr></thead>
        <tbody><tr><td>{{ $description }}</td><td class="right">NGN {{ number_format((float) $payment->amount, 2) }}</td></tr></tbody>
        <tfoot><tr class="total"><td>Total paid</td><td class="right">NGN {{ number_format((float) $payment->amount, 2) }}</td></tr></tfoot>
    </table>

    <div class="words"><strong>Amount in words</strong><em>{{ $amountInWords }}</em></div>
</div>
<div class="footer">This is a computer-generated receipt from the {{ setting('sbrs_school_name', 'School of Basic and Remedial Studies') }} portal and does not require a signature.</div>
</body>
</html>
