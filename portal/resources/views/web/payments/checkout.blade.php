{{-- Online checkout: opens the Razorpay widget. The result is POSTed to our callback,
     which verifies the HMAC signature server-side before confirming anything. --}}
<x-layouts.web :title="__('web.payments.checkout_title')" noindex>
    <section class="section auth-section">
        <div class="container">
            <div class="auth-shell text-center" data-razorpay-checkout
                 data-options='@json($checkout)'
                 data-callback-form="rzp-callback" data-cancel-form="rzp-cancel">
                <h1 class="checkout-title">{{ __('web.payments.checkout_title') }}</h1>
                <p class="muted">{{ $checkout['description'] }} — <strong>{{ inr($payment->amount) }}</strong></p>
                <p class="muted">{{ __('web.payments.checkout_intro') }}</p>
                <button type="button" class="btn btn-primary btn-block" data-razorpay-open>{{ __('web.payments.open_checkout') }}</button>

                <form id="rzp-callback" method="POST" action="{{ localized_route('payments.callback', ['payment' => $payment->ulid]) }}" hidden>
                    @csrf
                    <input type="hidden" name="razorpay_payment_id">
                    <input type="hidden" name="razorpay_order_id">
                    <input type="hidden" name="razorpay_signature">
                </form>
                <form id="rzp-cancel" method="POST" action="{{ localized_route('payments.cancel', ['payment' => $payment->ulid]) }}" class="checkout-cancel">
                    @csrf
                    <button type="submit" class="link-button">{{ __('web.common.cancel') }}</button>
                </form>
                <p class="muted secure-note"><span aria-hidden="true">&#128274;</span> {{ __('web.pay.secure_note') }}</p>
            </div>
        </div>
    </section>
</x-layouts.web>
