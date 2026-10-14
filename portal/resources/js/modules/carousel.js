// Homepage center carousel: autoplay, pause on hover/focus, arrows, dots,
// keyboard, swipe, and no autoplay for users who prefer reduced motion.
export function initCarousels() {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('[data-carousel]').forEach((root) => {
        const slides = [...root.querySelectorAll('.carousel-slide')];
        if (!slides.length) return;
        const dotsWrap = root.querySelector('[data-carousel-dots]');
        const label = root.dataset.dotLabel || 'Slide';
        const delay = parseInt(root.dataset.autoplay || '5500', 10);
        let index = Math.max(0, slides.findIndex((s) => s.classList.contains('is-active')));
        let timer = null;

        const dots = slides.map((_, i) => {
            if (!dotsWrap) return null;
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.setAttribute('aria-label', label + ' ' + (i + 1));
            dot.addEventListener('click', () => {
                goTo(i);
                start();
            });
            dotsWrap.appendChild(dot);
            return dot;
        });

        function render() {
            slides.forEach((s, i) => {
                const active = i === index;
                s.classList.toggle('is-active', active);
                s.setAttribute('aria-hidden', String(!active));
                if (s.tagName === 'A') s.tabIndex = active ? 0 : -1;
            });
            dots.forEach((d, i) => d && d.classList.toggle('is-active', i === index));
        }
        function goTo(i) {
            index = (i + slides.length) % slides.length;
            render();
        }
        function stop() {
            clearInterval(timer);
            timer = null;
        }
        function start() {
            stop();
            if (slides.length > 1 && !reduceMotion) timer = setInterval(() => goTo(index + 1), delay);
        }

        root.querySelector('[data-carousel-prev]')?.addEventListener('click', () => {
            goTo(index - 1);
            start();
        });
        root.querySelector('[data-carousel-next]')?.addEventListener('click', () => {
            goTo(index + 1);
            start();
        });
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', start);
        root.tabIndex = 0;
        root.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') { goTo(index + 1); start(); }
            if (e.key === 'ArrowLeft') { goTo(index - 1); start(); }
        });

        let startX = null;
        root.addEventListener('touchstart', (e) => { startX = e.changedTouches[0].clientX; }, { passive: true });
        root.addEventListener('touchend', (e) => {
            if (startX === null) return;
            const dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 40) {
                goTo(index + (dx < 0 ? 1 : -1));
                start();
            }
            startX = null;
        }, { passive: true });

        render();
        start();
    });
}
