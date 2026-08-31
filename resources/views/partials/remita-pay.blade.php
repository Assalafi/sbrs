{{--
    Remita Inline Payment Widget
    Required variables:
    - $payment (Payment model with RRR)
    Optional:
    - $widgetKey (unique string used to namespace the JS function + form id when the
      partial is rendered multiple times on the same page, e.g. the payments list)
--}}
@php
    $widgetKey = $widgetKey ?? '';
    $remitaIsLive = (bool) setting('remita_live', false);
    $remitaJsUrl = $remitaIsLive
        ? 'https://login.remita.net/payment/v1/remita-pay-inline.bundle.js'
        : 'https://demo.remita.net/payment/v1/remita-pay-inline.bundle.js';
    $fnName = 'makePayment' . $widgetKey;
    $verifyFormId = 'verify-form' . $widgetKey;
@endphp
@push('scripts')
@once
<script src="{{ $remitaJsUrl }}"></script>
@endonce
<script>
    function {{ $fnName }}() {
        var paymentEngine = RmPaymentEngine.init({
            key: "{{ setting('remita_public_key', '') }}",
            processRrr: true,
            transactionId: Math.floor(Math.random() * 1101233),
            extendedData: {
                customFields: [{
                    name: "rrr",
                    value: "{{ $payment->rrr ?? '' }}"
                }]
            },
            onSuccess: function(response) {
                console.log('Payment Success:', response);
                alert('Payment successful! Click OK to verify your payment.');
                document.getElementById('{{ $verifyFormId }}').submit();
            },
            onError: function(response) {
                console.log('Payment Error:', response);
                alert('Payment failed: ' + (response.message || 'Please try again or use the RRR at a bank.'));
            },
            onClose: function() {
                console.log('Payment window closed');
            }
        });
        paymentEngine.showPaymentWidget();
    }
</script>
@endpush
