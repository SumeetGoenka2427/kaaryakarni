// Mobile hamburger nav + "More" dropdowns (site header).
export function initNav() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-main-nav]');
    if (toggle && nav) {
        const setOpen = (open) => {
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        };
        toggle.addEventListener('click', () => setOpen(!nav.classList.contains('is-open')));
        nav.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setOpen(false)));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                setOpen(false);
                toggle.focus();
            }
        });
    }

    document.querySelectorAll('[data-nav-dropdown]').forEach((dropdown) => {
        const btn = dropdown.querySelector('[data-nav-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-nav-dropdown-menu]');
        if (!btn || !menu) return;
        const setOpen = (open) => {
            menu.hidden = !open;
            btn.setAttribute('aria-expanded', String(open));
        };
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            setOpen(menu.hidden);
        });
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) setOpen(false);
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !menu.hidden) {
                setOpen(false);
                btn.focus();
            }
        });
    });
}
