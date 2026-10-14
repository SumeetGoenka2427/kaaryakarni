// Razorpay checkout loader. Loads checkout.js only on the checkout page,
// opens the widget, and POSTs the result to our server for HMAC verification.
// Nothing here decides whether a payment succeeded — the server does.
const SCRIPT = 'https://checkout.razorpay.com/v1/checkout.js';

function loadScript() {
    return new Promise((resolve, reject) => {
        if (window.Razorpay) return resolve();
        const s = document.createElement('script');
        s.src = SCRIPT;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

export function initRazorpayCheckout() {
    const root = document.querySelector('[data-razorpay-checkout]');
    if (!root) return;

    const options = JSON.parse(root.dataset.options || '{}');
    const callback = document.getElementById(root.dataset.callbackForm);
    const cancel = document.getElementById(root.dataset.cancelForm);
    const button = root.querySelector('[data-razorpay-open]');

    const open = () => loadScript().then(() => {
        const rzp = new window.Razorpay({
            ...options,
            theme: { color: getComputedStyle(document.documentElement).getPropertyValue('--color-navy-800').trim() || '#0e2a52' },
            handler: (res) => {
                callback.querySelector('[name=razorpay_payment_id]').value = res.razorpay_payment_id;
                callback.querySelector('[name=razorpay_order_id]').value = res.razorpay_order_id;
                callback.querySelector('[name=razorpay_signature]').value = res.razorpay_signature;
                callback.submit();
            },
            modal: { ondismiss: () => cancel?.submit() },
        });
        rzp.open();
    }).catch(() => button?.removeAttribute('disabled'));

    button?.addEventListener('click', open);
    open();
}
