// Gallery lightbox: accessible dialog with focus trap/return, Esc, arrow keys and touch swipe.
// Tiles ([data-gallery-tile]) inside one [data-gallery-group] (an album) browse together.
// Tiles are links to the full image, so without JS they still open the photo.
export function initLightbox() {
    const overlay = document.querySelector('[data-lightbox]');
    if (!overlay) return;
    const img = overlay.querySelector('[data-lightbox-img]');
    const title = overlay.querySelector('[data-lightbox-title]');
    const desc = overlay.querySelector('[data-lightbox-desc]');
    const count = overlay.querySelector('[data-lightbox-count]');
    const prevBtn = overlay.querySelector('[data-lightbox-prev]');
    const nextBtn = overlay.querySelector('[data-lightbox-next]');
    const closeBtn = overlay.querySelector('[data-lightbox-close]');
    let group = [];
    let current = -1;
    let lastFocused = null;

    function show(i) {
        current = (i + group.length) % group.length;
        const t = group[current];
        if (img) {
            img.removeAttribute('src');
            if (t.dataset.srcset) img.srcset = t.dataset.srcset;
            else img.removeAttribute('srcset');
            img.src = t.dataset.img || t.href || '';
            img.alt = t.querySelector('img')?.alt || t.dataset.title || '';
        }
        if (title) title.textContent = t.dataset.title || '';
        if (desc) desc.textContent = t.dataset.desc || '';
        if (count) count.textContent = group.length > 1 ? `${current + 1} / ${group.length}` : '';
        [prevBtn, nextBtn].forEach((b) => b && (b.hidden = group.length < 2));
    }

    function open(tile) {
        const scope = tile.closest('[data-gallery-group]') || document;
        group = [...scope.querySelectorAll('[data-gallery-tile]')];
        lastFocused = document.activeElement;
        show(group.indexOf(tile));
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        closeBtn?.focus();
    }

    function close() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        img?.removeAttribute('src');
        img?.removeAttribute('srcset');
        lastFocused?.focus();
    }

    document.querySelectorAll('[data-gallery-tile]').forEach((tile) => tile.addEventListener('click', (e) => {
        e.preventDefault();
        open(tile);
    }));
    closeBtn?.addEventListener('click', close);
    prevBtn?.addEventListener('click', () => show(current - 1));
    nextBtn?.addEventListener('click', () => show(current + 1));
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) close();
    });

    // Swipe left / right on touch screens.
    let startX = null;
    overlay.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
    overlay.addEventListener('touchend', (e) => {
        if (startX === null || group.length < 2) return;
        const dx = e.changedTouches[0].clientX - startX;
        startX = null;
        if (Math.abs(dx) > 50) show(current + (dx < 0 ? 1 : -1));
    });

    document.addEventListener('keydown', (e) => {
        if (!overlay.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight' && group.length > 1) show(current + 1);
        if (e.key === 'ArrowLeft' && group.length > 1) show(current - 1);
        if (e.key === 'Tab') {
            const focusable = [...overlay.querySelectorAll('button')].filter((b) => !b.hidden);
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
