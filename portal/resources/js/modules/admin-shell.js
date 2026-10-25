// Admin sidebar (off-canvas below 1024px).
export function initSidebar() {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const scrim = document.querySelector('[data-admin-scrim]');
    if (sidebar) keepActiveItemInView(sidebar);
    if (!toggle || !sidebar) return;
    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        scrim?.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    };
    toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
    scrim?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setOpen(false);
    });
}

// The sidebar is taller than the screen: keep its scroll position between
// pages, and always bring the current page's item into view.
function keepActiveItemInView(sidebar) {
    const KEY = 'admin.sidebar.scroll';
    try {
        const saved = parseInt(sessionStorage.getItem(KEY) || '', 10);
        if (!Number.isNaN(saved)) sidebar.scrollTop = saved;
    } catch { /* storage blocked: fine */ }

    const active = sidebar.querySelector('.admin-nav a[aria-current="page"]');
    if (active) {
        const box = sidebar.getBoundingClientRect();
        const item = active.getBoundingClientRect();
        if (item.top < box.top + 70 || item.bottom > box.bottom - 70) {
            sidebar.scrollTop += item.top - box.top - (box.height - item.height) / 2;
        }
    }

    let timer;
    sidebar.addEventListener('scroll', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            try { sessionStorage.setItem(KEY, String(sidebar.scrollTop)); } catch { /* ignore */ }
        }, 100);
    }, { passive: true });
}

// Modals use the native <dialog> element (built-in focus trap + Esc):
// <button data-modal-open="slide-form">…</button> <dialog id="slide-form" class="admin-modal">…</dialog>
export function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dlg = document.getElementById(btn.dataset.modalOpen);
            if (!dlg) return;
            // One shared dialog per table: the clicked row supplies the form action + label.
            if (btn.dataset.formAction) dlg.querySelector('form')?.setAttribute('action', btn.dataset.formAction);
            const label = dlg.querySelector('[data-form-label-target]');
            if (label && btn.dataset.formLabel) label.textContent = btn.dataset.formLabel;
            dlg.showModal();
        });
    });
    document.querySelectorAll('dialog').forEach((dlg) => {
        dlg.querySelectorAll('[data-modal-close]').forEach((b) => b.addEventListener('click', () => dlg.close()));
        dlg.addEventListener('click', (e) => {
            if (e.target === dlg) dlg.close();
        });
        // Re-open a form dialog after a failed server validation.
        if (dlg.hasAttribute('data-open-on-load')) dlg.showModal();
    });
}
