# P03 — Settings, Branding / Logo, Site Texts ✅ Done

**Goal:** admin controls the logo, org identity, contact info and every UI label, in both languages.

> Admin login + roles were pulled forward from P04 so these admin screens are
> protected from day one (no unauthenticated admin pages ever existed).

## Tasks
### Admin access (from P04)
- [x] `admin_users` table + `admin` guard (the default `users` table/model is removed)
- [x] Roles/permissions (spatie, guard `admin`): super-admin (passes every check), membership-manager, content-manager, accounts, matrimonial-moderator, with 20 permissions
- [x] `/admin/login` (design from `admin/login.html`): generic error message, lockout after 5 failures for 15 min (per username + IP), route throttle, session regenerate, `last_login_at/ip`, inactive admins refused
- [x] Idle timeout (`ADMIN_IDLE_TIMEOUT`, 30 min) + deactivated-while-signed-in check
- [x] `ProductionSeeder`: roles + first super-admin from `.env` (refuses a password under 10 characters)
- [ ] Admin Users management screen and optional TOTP 2FA → **P04**

### Data
- [x] Migrations: `settings`, `branding_assets`, `site_texts`, `audit_logs`
- [ ] ~~`pages`, `page_sections`~~ → moved to **P08**, where the About/Contact/homepage content screens use them
- [x] `SettingsService`: one cached array, typed defaults, `setting()` / `setting_t()` helpers, flushed on save
- [x] `DatabaseOverrideLoader`: `site_texts` overrides on top of `lang/{en,hi}/web.php` and `enums.php`, cached per locale + group
- [x] `AuditLogger` (admin, action, subject, old/new values with secrets masked, IP, user agent)

### Admin → Branding & Logo
- [x] Header logo, footer/dark logo (also admin sidebar), receipt logo (PNG for PDF), favicon (+32px and 180px apple-touch generated), social share image
- [x] PNG/JPG/WebP only, ≤ 2 MB, 64–6000 px. **SVG refused** (it can carry scripts). Every upload is re-encoded with Intervention (strips EXIF/payloads) and resized
- [x] Versioned files + `?v=N` URLs, so a replaced logo shows instantly everywhere. Old files are deleted
- [x] Preview on light/navy backgrounds, remove → back to the default "KS" mark
- [x] `<x-logo>` used in header, footer, member header, admin sidebar and admin login. Favicons + default OG image in every layout

### Admin → Settings (design from `settings.html`, extended)
- [x] Organisation name / short name / tagline (EN + HI), founded year
- [x] Office name, address, office hours (EN + HI), phones, email, WhatsApp, Google Maps link, social links
- [x] Lifetime membership fee (one-time, no renewal), donor-name visibility, business re-approval toggle, show name beside logo
- [x] Optional primary/gold/accent colour overrides (strict `#rrggbb` validation) → inline `:root` variables
- [x] Topbar, footer, page titles and OG tags now read these settings. No contact details are hard-coded anymore
- [x] The demo-only "Reset Demo Data" danger zone was intentionally dropped

### Admin → Site Texts
- [x] Every key of `web.php` + `enums.php` grouped by section, with English + हिन्दी side by side
- [x] Search across all texts, "only texts without Hindi" filter, Edited / HI missing badges
- [x] Only differences from the default are stored. Clearing a box restores the default. Unknown keys/groups are ignored
- [x] Saves are audit-logged. Output is always escaped

### Admin dashboard (starter)
- [x] Quick links (permission-aware) + recent admin actions from the audit log. Module statistics arrive with each module (full dashboard in P12)

## Acceptance
- [x] Uploading a logo changes it on public, member and admin pages (tested). Receipts use `logo_receipt` in P06
- [x] Editing a Hindi site text shows on `/hi` (tested)
- [x] `composer test`: 49 tests pass, including lockout, idle timeout, permission 403s, upload rejection (PHP disguised as PNG, SVG, PDF), XSS escaping, audit records
