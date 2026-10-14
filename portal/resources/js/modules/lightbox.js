// Gallery lightbox: accessible dialog with focus trap/return, Esc, arrow keys.
export function initLightbox() {
    const overlay = document.querySelector('[data-lightbox]');
    if (!overlay) return;
    const img = overlay.querySelector('[data-lightbox-img]');
    const title = overlay.querySelector('[data-lightbox-title]');
    const desc = overlay.querySelector('[data-lightbox-desc]');
    const closeBtn = overlay.querySelector('[data-lightbox-close]');
    const tiles = [...document.querySelectorAll('[data-gallery-tile]')];
    let current = -1;
    let lastFocused = null;

    function show(i) {
        current = (i + tiles.length) % tiles.length;
        const t = tiles[current];
        if (img) {
            img.src = t.dataset.img || '';
            img.alt = t.dataset.title || '';
        }
        if (title) title.textContent = t.dataset.title || '';
        if (desc) desc.textContent = t.dataset.desc || '';
    }
    function open(i) {
        lastFocused = document.activeElement;
        show(i);
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        closeBtn?.focus();
    }
    function close() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        img?.removeAttribute('src');
        lastFocused?.focus();
    }

    tiles.forEach((tile, i) => tile.addEventListener('click', () => open(i)));
    closeBtn?.addEventListener('click', close);
    overlay.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => show(current - 1));
    overlay.querySelector('[data-lightbox-next]')?.addEventListener('click', () => show(current + 1));
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) close();
    });
    document.addEventListener('keydown', (e) => {
        if (!overlay.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight') show(current + 1);
        if (e.key === 'ArrowLeft') show(current - 1);
        if (e.key === 'Tab') {
            const focusable = [...overlay.querySelectorAll('button')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
}
