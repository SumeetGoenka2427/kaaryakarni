# P03 — Settings, Branding / Logo, Site Texts

**Goal:** admin controls the logo, org identity, contact info and every UI label, in both languages.

## Tasks
### Data
- [ ] Migrations: `settings`, `branding_assets`, `site_texts`, `pages`, `page_sections`
- [ ] `SettingsService` (typed getters, cached array, cleared on save)
- [ ] DB translation loader that merges `site_texts` over `lang/*` files (cached)

### Admin → Branding (new page, admin visual style)
- [ ] Upload/replace: header logo, footer/light logo, admin logo, receipt logo, favicon, OG image
- [ ] Live preview on light and navy backgrounds, plus a "reset to default KS mark" option
- [ ] Validation (PNG/WebP/SVG, ≤ 2 MB, minimum dimensions), SVG sanitizing, 1x/2x variants, auto apple-touch-icon
- [ ] Org name EN/HI, tagline EN/HI
- [ ] Optional primary/gold/saffron color overrides → inline `:root` style
- [ ] `<x-logo>` used in header, footer, member layout, admin sidebar, admin login, receipts, emails

### Admin → Settings (existing `settings.html` design, extended)
- [ ] Org contact: address EN/HI, phones, email, office hours EN/HI, map link, social links
- [ ] Membership fee, donor-name public toggle, business re-approval-on-edit toggle
- [ ] Footer text EN/HI, copyright line

### Admin → Site Texts (new)
- [ ] Grouped list (Header, Footer, Home, About, Membership, Directory, Donations, Gallery, Contact, Auth, Member area, Validation)
- [ ] Search, inline EN/HI edit, "HI missing" filter, reset key to default
- [ ] Every save is audit-logged

## Acceptance
Changing the logo in admin updates it on every public, member and admin page and on receipts
instantly (cache-busted). Editing a Hindi site text shows on `/hi`.
