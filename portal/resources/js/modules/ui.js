// Accordions, tabs, toasts, server flash messages, progress bars.
export function initAccordions() {
    document.querySelectorAll('.accordion-trigger').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const expanded = trigger.getAttribute('aria-expanded') === 'true';
            trigger.setAttribute('aria-expanded', String(!expanded));
            document.getElementById(trigger.getAttribute('aria-controls'))?.setAttribute('data-open', String(!expanded));
        });
    });
}

export function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach((root) => {
        const group = root.dataset.tabs;
        const buttons = [...root.querySelectorAll('.tab-btn')];
        const panels = [...document.querySelectorAll('[data-tab-panel][data-tabs-group="' + group + '"]')];
        buttons.forEach((btn) => btn.addEventListener('click', () => {
            buttons.forEach((b) => {
                b.classList.toggle('is-active', b === btn);
                b.setAttribute('aria-selected', String(b === btn));
            });
            panels.forEach((p) => {
                p.hidden = p.dataset.tabPanel !== btn.dataset.tab;
            });
        }));
    });
}

export function toast(message, type = '') {
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'toast-stack';
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
    }
    const el = document.createElement('div');
    el.className = 'toast' + (type ? ' toast-' + type : '');
    el.setAttribute('role', 'status');
    el.textContent = message;
    stack.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

// Server flash messages rendered by <x-flash> as <template data-toast="..." data-toast-type="success">
export function initFlashToasts() {
    document.querySelectorAll('[data-toast]').forEach((el) => toast(el.dataset.toast, el.dataset.toastType));
}

export function initProgressBars() {
    document.querySelectorAll('.progress-fill[data-target-width]').forEach((bar) => {
        requestAnimationFrame(() => {
            bar.style.width = bar.dataset.targetWidth + '%';
        });
    });
}
