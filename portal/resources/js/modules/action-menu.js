// Admin table row actions: one "Actions" button that opens a menu (x-admin.actions).
// The menu is position: fixed so the table's scroll container never clips it,
// and it opens upwards when there is no room below.
const ITEM = 'a[href], button:not([disabled]):not(.action-menu-toggle)';

export function initActionMenus() {
    const menus = [...document.querySelectorAll('[data-action-menu]')];
    let open = null;

    menus.forEach((menu) => {
        const toggle = menu.querySelector('.action-menu-toggle');
        const list = menu.querySelector('.action-menu-list');
        const items = [...list.querySelectorAll(ITEM)];

        // Nothing to show → drop the menu; a single action → show it as a normal button.
        if (items.length === 0) {
            menu.remove();
            return;
        }
        if (items.length === 1) {
            const only = items[0].closest('form') || items[0];
            menu.replaceWith(only);
            return;
        }

        items.forEach((item) => item.setAttribute('role', 'menuitem'));
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            open === menu ? close() : show(menu);
        });
        list.addEventListener('keydown', (e) => {
            const i = items.indexOf(document.activeElement);
            if (e.key === 'ArrowDown') { e.preventDefault(); items[(i + 1) % items.length].focus(); }
            if (e.key === 'ArrowUp') { e.preventDefault(); items[(i - 1 + items.length) % items.length].focus(); }
            if (e.key === 'Home') { e.preventDefault(); items[0].focus(); }
            if (e.key === 'End') { e.preventDefault(); items[items.length - 1].focus(); }
            if (e.key === 'Tab') close(false);
        });
        // Choosing an item closes the menu (confirm dialogs / modals take over from there).
        list.addEventListener('click', (e) => {
            if (e.target.closest(ITEM)) close(false);
        });
    });

    function show(menu) {
        close(false);
        const toggle = menu.querySelector('.action-menu-toggle');
        const list = menu.querySelector('.action-menu-list');
        list.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        open = menu;
        place(toggle, list);
        list.querySelector(ITEM)?.focus();
    }

    function close(returnFocus = true) {
        if (!open) return;
        const toggle = open.querySelector('.action-menu-toggle');
        open.querySelector('.action-menu-list').hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (returnFocus) toggle.focus();
        open = null;
    }

    function place(toggle, list) {
        const r = toggle.getBoundingClientRect();
        const w = list.offsetWidth;
        const h = list.offsetHeight;
        const below = window.innerHeight - r.bottom;
        const top = below < h + 8 && r.top > h + 8 ? r.top - h - 4 : r.bottom + 4;
        const left = Math.min(Math.max(8, r.right - w), window.innerWidth - w - 8);
        list.style.top = `${Math.round(top)}px`;
        list.style.left = `${Math.round(left)}px`;
    }

    document.addEventListener('click', (e) => {
        if (open && !open.contains(e.target)) close(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && open) close();
    });
    window.addEventListener('resize', () => close(false));
    document.addEventListener('scroll', () => close(false), true);
}
