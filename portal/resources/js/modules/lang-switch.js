// One English / हिन्दी switch per admin form instead of tabs on every
// bilingual field (x-admin.translatable-field). The per-field tabs stay in
// the HTML as a no-JS fallback and are hidden once the form switch exists.
export function initLangSwitches() {
    const fields = [...document.querySelectorAll('.translatable-field')];
    const scopes = new Map();
    fields.forEach((field) => {
        const scope = field.closest('form') || field.closest('.admin-modal') || document.body;
        if (!scopes.has(scope)) scopes.set(scope, []);
        scopes.get(scope).push(field);
    });

    scopes.forEach((group) => {
        const panels = (lang) => group.flatMap((field) => {
            const name = field.querySelector('[data-tabs]')?.dataset.tabs;
            return [...field.querySelectorAll(`[data-tab-panel="${lang}"][data-tabs-group="${name}"]`)];
        });
        const en = panels('en');
        const hi = panels('hi');
        const missing = group.filter((f) => f.querySelector('[data-tab="hi"] .count')).length;
        const hasError = (list) => list.some((p) => p.querySelector('.has-error, [aria-invalid="true"]'));
        const enError = hasError(en);
        const hiError = hasError(hi);

        const bar = document.createElement('div');
        bar.className = 'lang-switch';
        bar.innerHTML = `
            <span class="lang-switch-label" id="ls-${Math.random().toString(36).slice(2, 8)}">Content language</span>
            <div class="lang-switch-buttons" role="tablist">
                <button type="button" role="tab" class="lang-switch-btn" data-lang="en">English${enError ? ' <span class="lang-switch-error" title="Has errors">!</span>' : ''}</button>
                <button type="button" role="tab" class="lang-switch-btn" data-lang="hi" lang="hi">हिन्दी${hiError ? ' <span class="lang-switch-error" title="Has errors">!</span>' : ''}${missing ? ` <span class="lang-switch-missing" title="Hindi text missing on ${missing} field(s) — English is shown on Hindi pages">${missing} missing</span>` : ''}</button>
            </div>
            <span class="lang-switch-hint">Switches all English / हिन्दी fields in this form. Hindi is optional — empty fields show English on Hindi pages.</span>`;
        const label = bar.querySelector('.lang-switch-label');
        bar.querySelector('[role=tablist]').setAttribute('aria-labelledby', label.id);

        const buttons = [...bar.querySelectorAll('.lang-switch-btn')];
        const show = (lang) => {
            buttons.forEach((b) => {
                const on = b.dataset.lang === lang;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-selected', String(on));
            });
            en.forEach((p) => { p.hidden = lang !== 'en'; });
            hi.forEach((p) => { p.hidden = lang !== 'hi'; });
        };
        buttons.forEach((b) => b.addEventListener('click', () => show(b.dataset.lang)));

        group.forEach((field) => field.classList.add('is-synced'));
        group[0].before(bar);
        show(hiError && !enError ? 'hi' : 'en');

        // A field that fails a client-side check while its language is hidden:
        // switch to that language so the error is visible.
        const form = group[0].closest('form');
        const reveal = (input) => {
            const lang = input.closest('[data-tab-panel]')?.dataset.tabPanel;
            if (lang && input.closest('[data-tab-panel]').hidden) {
                show(lang);
                input.focus();
            }
        };
        form?.addEventListener('invalid', (e) => reveal(e.target), true);
        form?.addEventListener('submit', (e) => {
            if (!e.defaultPrevented) return;
            const bad = form.querySelector('.translatable-field [aria-invalid="true"]');
            if (bad) reveal(bad);
        });
    });
}
