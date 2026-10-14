# 05 — Security

## Authentication
- **Member OTP**: 6-digit code from `random_int`, stored **hashed** (`Hash::make`), 5-minute
  expiry, single use, max 5 verify attempts per code, resend cooldown 60 s, max 5 sends per
  mobile per hour and 20 per IP per hour (`RateLimiter`). The same response is returned whether
  the number exists or not (no user enumeration).
- OTP is never logged and never shown in the UI. In local development with `SMS_DRIVER=log`,
  it goes to `storage/logs/otp.log` only. Production boot **refuses** `SMS_DRIVER=log`.
- **Admin**: username + bcrypt password, 5 attempts / 15 min lockout, optional TOTP 2FA,
  `session()->regenerate()` on login, idle timeout of 30 min.
- Sessions: `database` driver, `secure`, `httponly`, `same_site=lax`, encrypted cookies.
  Logout invalidates the session and regenerates the token.

## Authorization
- Separate `member` and `admin` guards.
- Admin roles (spatie/permission): **Super Admin, Membership Manager, Content Manager,
  Accounts (payments/donations), Matrimonial Moderator.** Every admin route has a `can:` check.
- **Policies on every member-owned model** (profile, business, matrimonial, payment, receipt).
  Queries are always scoped `->whereBelongsTo($member)`, which blocks ID manipulation.
- Matrimonial: only logged-in members with **active lifetime membership** can browse. The
  module is `noindex` and excluded from the sitemap. Photos and bio-data are served from the
  **private disk** through an authorizing controller (never a public URL).
- Status changes (approve, verify, mark paid) only happen in admin Services. Request input
  for `status` from members is never mass-assigned (`$fillable` whitelists + FormRequests).

## Input / output
- FormRequest validation for every write, with Indian mobile regex `^[6-9]\d{9}$`.
- Blade `{{ }}` escaping everywhere. `{!! !!}` is used only for Purifier-cleaned admin rich text.
- Uploads: whitelist MIME + extension, check magic bytes, size limits (images 5 MB, PDF 5 MB),
  **re-encode images** with Intervention (removes EXIF/GPS and embedded payloads), random
  filenames, no execution in `storage` (web server rule). SVG logos are sanitized.
- CSRF on all forms (Laravel default). Webhook routes are the only exception and are signature-verified.

## Payments
- An online payment is marked successful **only** by server-side verification: Razorpay
  signature check on callback **and** webhook (`X-Razorpay-Signature` HMAC). Payments are
  idempotent on `provider_payment_id`, and the amount is re-checked against the DB order.
- The frontend redirect never changes status by itself.
- Offline payments: member status `submitted` → only Accounts admin can set `verified` → receipt.

## Headers & transport
`SecurityHeaders` middleware: HSTS, `Content-Security-Policy` (self + Razorpay checkout
domains, nonce for inline scripts), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
`Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`. HTTPS is forced in production.

## Secrets & ops
- `.env` only, with `.env.example` committed. `APP_DEBUG=false` in production. `/admin` can be IP-allowlisted optionally.
- Audit log of every admin create/update/delete/approve/verify/export, with old/new values and IP.
- Data exports require a permission and are audit-logged.
- `composer audit` and `npm audit` run before each deploy.
- Daily DB backup (mysqldump + private uploads), kept for 14 days.

## Privacy
- Public business pages show phone/email only if the owner's `show_*` flag is on.
- Donor names are public only if global setting **and** the donor's `show_name_publicly` allow it. Default display is "A Samaj member".
- Member mobile numbers are never shown publicly.
