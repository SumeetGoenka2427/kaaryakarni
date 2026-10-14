// Client-side hints only. The server (FormRequest) is always the real validator.
const rules = {
    mobile: (v) => /^[6-9]\d{9}$/.test(v.trim()),
    required: (v) => v.trim().length > 0,
    email: (v) => !v.trim() || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()),
    otp: (v) => /^\d{6}$/.test(v.trim()),
};

function check(input) {
    const rule = rules[input.dataset.rule];
    if (!rule) return true;
    const ok = rule(input.value || '');
    input.closest('.field')?.classList.toggle('has-error', !ok);
    input.setAttribute('aria-invalid', String(!ok));
    return ok;
}

export function initForms() {
    // <select data-autosubmit> submits its form on change (no inline handlers: CSP).
    document.querySelectorAll('[data-autosubmit]').forEach((el) => {
        el.addEventListener('change', () => el.form?.requestSubmit());
    });

    // Confirm before destructive actions: <form data-confirm="Delete this album?">
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });
    });

    document.querySelectorAll('form[data-validate]').forEach((form) => {
        const inputs = [...form.querySelectorAll('[data-rule]')];
        inputs.forEach((i) => i.addEventListener('blur', () => check(i)));
        form.addEventListener('submit', (e) => {
            const bad = inputs.filter((i) => !check(i));
            if (bad.length) {
                e.preventDefault();
                bad[0].focus();
            }
        });
    });

    // Loading state + double-submit protection on every form.
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;
            const btn = e.submitter || form.querySelector('[type="submit"]');
            if (btn) {
                btn.classList.add('is-loading');
                btn.disabled = true;
            }
        });
    });

    // Online / offline payment mode switcher.
    document.querySelectorAll('[data-payment-toggle]').forEach((root) => {
        const buttons = [...root.querySelectorAll('[data-mode]')];
        const panels = [...document.querySelectorAll('[data-mode-panel]')];
        buttons.forEach((btn) => btn.addEventListener('click', () => {
            buttons.forEach((b) => {
                b.classList.toggle('btn-primary', b === btn);
                b.classList.toggle('btn-outline', b !== btn);
                b.setAttribute('aria-pressed', String(b === btn));
            });
            panels.forEach((p) => {
                p.hidden = p.dataset.modePanel !== btn.dataset.mode;
            });
        }));
    });
}

// 6-box OTP input: digits only, auto-advance, backspace, paste a full code.
// The boxes fill a hidden <input name="otp"> that is actually submitted.
export function initOtpInputs() {
    document.querySelectorAll('.otp-inputs').forEach((group) => {
        const inputs = [...group.querySelectorAll('input')];
        const hidden = group.parentElement.querySelector('input[name="otp"]');
        const sync = () => {
            if (hidden) hidden.value = inputs.map((i) => i.value).join('');
        };
        inputs.forEach((input, i) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 1);
                if (input.value && inputs[i + 1]) inputs[i + 1].focus();
                sync();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && inputs[i - 1]) inputs[i - 1].focus();
            });
            input.addEventListener('paste', (e) => {
                const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, inputs.length);
                if (!digits) return;
                e.preventDefault();
                inputs.forEach((box, j) => {
                    box.value = digits[j] || '';
                });
                inputs[Math.min(digits.length, inputs.length) - 1].focus();
                sync();
            });
        });
    });

    // Resend countdown: <button data-resend data-seconds="60">… <span data-resend-timer></span></button>
    document.querySelectorAll('[data-resend]').forEach((btn) => {
        let left = parseInt(btn.dataset.seconds || '0', 10);
        const label = btn.querySelector('[data-resend-timer]');
        if (left <= 0) return;
        btn.disabled = true;
        const timer = setInterval(() => {
            left -= 1;
            if (label) label.textContent = left > 0 ? ' (' + left + 's)' : '';
            if (left <= 0) {
                clearInterval(timer);
                btn.disabled = false;
            }
        }, 1000);
    });
}
