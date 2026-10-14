# P02 — Design System, Layouts & Bilingual Engine ✅ Done

**Goal:** the existing look running inside Laravel layouts, with EN/HI switching working.
See [04](../04-design-system-and-html-conversion.md) and [03](../03-bilingual-content.md).

## Tasks
### CSS / fonts
- [x] `style.css` split into `tokens.css` / `base.css` / `components.css`. `admin.css` moved to `admin-shell.css`. Bundles: `web.css`, `admin.css`
- [x] No hex colours outside `tokens.css` (added `--color-white`, `--color-skeleton-mid`)
- [x] Inline styles from the HTML moved into token-based classes (`cta-band`, `brand-on-dark`, `media-rounded`, …)
- [x] Self-hosted **Mukta + Tiro Devanagari Hindi, Devanagari subset only**. English keeps the original Georgia / system fonts (visually identical to the design), and Hindi glyphs use the Hindi fonts. The font files download only on pages with Hindi text
- [x] Site-wide EN | हिं toggle styled from `about.html`, placed in the header

### JS (ES modules, ~3 KB gzipped on public pages)
- [x] `nav` (hamburger + More dropdown), `carousel` (autoplay, pause, keys, swipe, reduced-motion), `lightbox` (focus trap/return), `forms` (hints, loading state, double-submit guard, `data-confirm`, payment toggle, OTP boxes with paste + resend countdown), `ui` (tabs, accordion, toasts, flash), `admin-shell` (sidebar, native `<dialog>` modals)
- [x] All `KKStore` / `KKSession` / mock OTP code removed

### Blade
- [x] Layouts: `x-layouts.web`, `x-layouts.member`, `x-layouts.admin`, `x-layouts.admin-guest`
- [x] Components: `seo`, `logo`, `lang-toggle`, `flash`, `pill` (enum-aware), `field`, `empty-state` (SVG icons, no emoji), `page-hero`, `section-head`, `admin.translatable-field`
- [x] Menus in `config/navigation.php`. Items appear only once their route exists (no dead links during the build). Admin items also respect permissions
- [x] Error pages 403/404/419/429/500/503, bilingual (404 under `/hi` shows Hindi)
- [ ] `x-img` responsive image component → moved to **P08**, where the image upload pipeline exists

### Bilingual
- [x] `SetLocale` + `/hi` prefix + `Route::localized()` + cookie
- [x] Helpers: `localized_route()`, `switch_locale_url()`, `base_route_name()`, `route_is_current()`, `inr()` (₹4,82,600 format)
- [x] `lang/{en,hi}/web.php` (chrome, home, errors, common) + `enums.php` + Hindi `validation.php`
- [x] `HasTranslations` trait (`t()` with English fallback, "HI missing" detection)
- [x] `hreflang` + canonical + localized OG meta in `<x-seo>`

## Acceptance
- [x] Homepage renders in the layout. Screenshot at 1280px matches `web/index.html` (header, typography, colours, cards, footer)
- [x] EN ↔ HI toggle switches all chrome text and links to the same page
- [x] `composer test`: 25 tests pass
- Bug found and fixed by tests: `Route::view()` defaults were leaking `?view=…&status=…` into language-switch URLs

## Notes
- Contact phone/email/address in topbar/footer are still from the design. They become admin-managed in **P03** (Settings).
- The homepage shows only About + Membership CTA for now. Banner, stats, campaigns, businesses, events and gallery sections are added by P07–P11 as their data exists.
- Headless Chrome can't render below ~500px wide, so true 360/390px checks use device emulation in P13.
