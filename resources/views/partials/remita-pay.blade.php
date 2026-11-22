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
    // Keep the admin setting as the source of truth, but fall back to the
    // environment-backed config used by the other payment pages when an older
    // installation has not copied the public key into the settings table yet.
    $remitaPublicKey = setting('remita_public_key', config('services.remita.public_key', ''));
    $fnName = 'makePayment' . $widgetKey;
    $verifyFormId = 'verify-form' . $widgetKey;
@endphp
@push('scripts')
@once
<script>
    window.__sbrsRemitaScriptError = false;
</script>
<script src="{{ $remitaJsUrl }}" onerror="window.__sbrsRemitaScriptError = true;"></script>
@endonce
<script>
    window[@json($fnName)] = function () {
        var startedAt = Date.now();
        var openWidget = function () {
            if (window.__sbrsRemitaScriptError) {
                window.alert('Online payment is temporarily unavailable. Please try again later. Your payment has not been changed.');
                return;
            }
            if (!window.RmPaymentEngine || typeof window.RmPaymentEngine.init !== 'function') {
                if (Date.now() - startedAt < 15000) {
                    window.setTimeout(openWidget, 250);
                    return;
                }
                window.alert('Online payment is taking longer than expected to load. Please try again later. Your payment has not been changed.');
                return;
            }

            try {
                var paymentEngine = window.RmPaymentEngine.init({
                    key: @json($remitaPublicKey),
                    processRrr: true,
                    // Keep every supported channel available, but open Remita on Card
                    // first so the most-used option is immediately visible.
                    channels: 'card,bank,branch,ussd,qr,ibank,paywithremita,buyoncredit,wallet,phonenumber,transfer,enaira',
                    transactionId: Math.floor(Math.random() * 1101233),
                    extendedData: {
                        customFields: [{name: 'rrr', value: @json($payment->rrr ?? '')}]
                    },
                    onSuccess: function() {
                        var verifyForm = document.getElementById(@json($verifyFormId));
                        if (verifyForm) verifyForm.submit();
                    },
                    onError: function(response) {
                        var message = response && (response.message || response.error);
                        window.alert('Payment was not completed. ' + (message || 'Please try again or use your RRR at a bank.'));
                    },
                    onClose: function() {}
                });
                if (!paymentEngine || typeof paymentEngine.showPaymentWidget !== 'function') {
                    throw new Error('Remita did not return a payment window.');
                }
                paymentEngine.showPaymentWidget();
            } catch (error) {
                window.alert('Online payment is temporarily unavailable. Please try again later. Your payment has not been changed.');
            }
        };

        openWidget();
    }
</script>
@endpush
