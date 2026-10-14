# 04 — Design System & HTML → Blade Conversion

**Rule: the web and admin UI must look identical to the current HTML.** We change how
the markup is produced, not how it looks.

## CSS: one token source on `:root`

```
resources/css/
├── tokens.css   ← ALL :root variables (colors, fonts, radii, shadows, spacing, container)
├── base.css     ← reset, typography, focus, utilities (from style.css top)
├── web.css      ← public/member components (rest of style.css)
└── admin.css    ← admin chrome (current admin.css, unchanged look)
```
- `web` bundle = tokens + base + web. `admin` bundle = tokens + base + admin + the shared
  components (buttons, forms, pills, tables) that admin pages already borrow from style.css.
- **No hard-coded colors / fonts / sizes outside `tokens.css`.** While converting, any
  inline `style="..."` in the HTML (e.g. the membership CTA gradient, `color:#fff`) moves into
  a named class that uses tokens.
- Add font tokens for Hindi:
  ```css
  --font-heading: 'Tiro Devanagari Hindi', Georgia, serif;
  --font-body: 'Mukta', -apple-system, 'Segoe UI', Roboto, sans-serif;
  ```
  Self-hosted woff2 in `public/fonts`, `font-display: swap`, preload only the body regular weight.
- Optional (P03): admin can override the **primary/gold/saffron** colors from Branding. These
  are emitted as a tiny inline `<style>:root{--color-navy-900:…}</style>` from cached settings.
  The default is the current palette.

## JS

- `web/assets/js/main.js` and `admin/assets/js/admin.js` are split into small ES modules
  (`nav.js`, `carousel.js`, `lightbox.js`, `modal.js`, `tabs.js`, `toast.js`, `otp.js`, `table.js`).
- **All `KKStore` / `KKSession` calls are removed.** Data comes rendered from the server.
  JS only enhances (carousel, dialogs, filters submit, confirm prompts, OTP timer).
- Each page loads only the modules it needs (Vite entries: `web.js`, `admin.js`, plus page
  entries like `matrimonial.js`).

## Blade components (reusable, built from existing markup)

| Component | Source HTML pattern |
|-----------|---------------------|
| `<x-layouts.web>` | topbar + `site-header` + footer from `web/index.html` |
| `<x-layouts.member>` | member sidebar/tabs from `web/member/*.html` |
| `<x-layouts.admin>` | `admin-shell`, sidebar, topbar from `admin/*.html` |
| `<x-logo variant="header|footer|admin|mark">` | `brand` / `brand-mark` blocks → uses uploaded logo, falls back to "KS" mark |
| `<x-lang-toggle>` | `.lang-toggle` from `about.html` |
| `<x-card>`, `<x-pill :status>`, `<x-btn>` | `.card`, `.pill-*`, `.btn-*` |
| `<x-field>`, `<x-select>`, `<x-textarea>`, `<x-file>` | `.field` + label + helper + error |
| `<x-admin.translatable-field>` | new: EN / हिन्दी tabs using existing tab styles |
| `<x-admin.table>` + `<x-admin.filters>` + `<x-pagination>` | admin tables (cards on mobile, as now) |
| `<x-modal>`, `<x-confirm>` | existing dialog markup, accessible |
| `<x-empty-state>`, `<x-alert>`, `<x-toast>` | `.state-block`, `.form-alert` |
| `<x-donation-progress :campaign>` | progress bar used on home/donations |
| `<x-img>` | responsive `<picture>` with WebP `srcset`, `width/height`, `loading="lazy"` |

## Logo / Branding (Admin → Branding)

One screen manages every logo on the site:

| Asset | Used in |
|-------|---------|
| Header logo | Public header, member area header, login/register pages |
| Footer / light logo | Footer (navy background), admin sidebar |
| Admin login logo | `/admin/login` |
| Receipt logo | PDF receipts |
| Favicon (+ apple-touch icon generated) | All pages |
| Social share (OG) image | Default `og:image` |

- Upload accepts PNG/SVG/WebP with strict validation. SVG is **sanitized**; otherwise
  only raster formats are allowed. Resized variants (1x/2x) are generated.
- URLs include `?v={version}`, so a changed logo appears instantly everywhere despite long cache headers.
- Text fallbacks (org name EN/HI, tagline EN/HI) are edited on the same screen.

## Page map: HTML → route → Blade view

### Public (each also under `/hi/...`)
| HTML | Route | View |
|------|-------|------|
| `web/index.html` | `/` | `web/home.blade.php` |
| `web/about.html` | `/about` | `web/about` |
| `web/committee-members.html` | `/committee` | `web/committee` |
| `web/membership.html` | `/membership` | `web/membership` |
| `web/login.html` | `/login` | `web/auth/login` |
| `web/register.html` | `/register` | `web/auth/register` |
| `web/business-directory.html` | `/business-directory` | `web/business/index` |
| `web/business-detail.html` | `/business-directory/{slug}` | `web/business/show` |
| `web/donations.html` | `/donations`, `/donations/{slug}` | `web/donations/index`, `show` |
| `web/gallery.html` | `/gallery`, `/gallery/{slug}` | `web/gallery/index`, `show` |
| `web/contact.html` | `/contact` | `web/contact` |

### Member
| HTML | Route |
|------|-------|
| `member/dashboard.html` | `/member/dashboard` |
| `member/profile.html` | `/member/profile` |
| `member/membership.html` | `/member/membership` |
| `member/pay-membership.html` | `/member/membership/pay` |
| `member/business.html` | `/member/business` |
| `member/matrimonial.html` | `/member/matrimonial` (own profile) |
| `member/matrimonial-browse.html` | `/member/matrimonial/browse`, `/member/matrimonial/{ulid}` |
| `member/payments.html` | `/member/payments` (+ receipt download) |
| `member/notifications.html` | `/member/notifications` |
| *(new, member-area style)* | `/member/family` — add/edit family members |

### Admin
`admin/login.html` → `/admin/login`; `index.html` → `/admin`; and `members`, `areas`,
`memberships`, `businesses`, `matrimonial`, `donations`, `payments`, `banners`, `gallery`,
`reports`, `receipts`, `notifications`, `settings`, `audit-logs` → `/admin/{name}`.
**New admin screens** (same visual style as existing admin pages): `/admin/branding`,
`/admin/site-texts`, `/admin/pages`, `/admin/committee`, `/admin/announcements`,
`/admin/categories`, `/admin/contact-messages`, `/admin/admin-users`.

## Conversion checklist per page
1. Copy markup into Blade and swap the layout parts for components.
2. Replace every visible string with `__('web.key')` (UI) or `$model->t('field')` (content).
3. Replace JS-rendered lists (`innerHTML` builders) with server-side `@foreach` + empty state.
4. Move inline styles into token-based classes.
5. Do a visual diff against the original HTML at 360 / 768 / 1280 px. It must match.
