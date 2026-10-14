// Admin sidebar (off-canvas below 1024px).
export function initSidebar() {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const scrim = document.querySelector('[data-admin-scrim]');
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

// Modals use the native <dialog> element (built-in focus trap + Esc):
// <button data-modal-open="slide-form">…</button> <dialog id="slide-form" class="admin-modal">…</dialog>
export function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => document.getElementById(btn.dataset.modalOpen)?.showModal());
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
