# P01 — Foundation ✅ Done

**Goal:** an empty but production-shaped Laravel app in `portal/` that builds and runs.

## Tasks
- [x] `composer create-project laravel/laravel portal` (Laravel 12.70, PHP 8.2)
- [x] MySQL DB `kaaryakarni` + test DB `kaaryakarni_test` (utf8mb4_unicode_ci) on MariaDB 10.4 (XAMPP). `.env` + `.env.example`
- [x] `.env.example` keys: app/timezone (Asia/Kolkata), DB, sessions (encrypted, secure-cookie flag), SMS + OTP limits, Razorpay, admin seed, cache/queue
- [x] `config/portal.php`: locales, OTP limits, SMS/payment drivers, upload limits, admin seed
- [x] Packages: spatie/laravel-permission, intervention/image, barryvdh/laravel-dompdf, spatie/simple-excel, razorpay/razorpay, mews/purifier; dev: pest + pest-plugin-laravel, debugbar, pint
- [x] Route files `web.php`, `member.php`, `admin.php`, `webhooks.php` registered in `bootstrap/app.php`
- [x] `Route::localized()` macro: each public/member route exists as `/x` (name `x`) and `/hi/x` (name `hi.x`)
- [x] Middleware: `SetLocale` (URL-driven + remembered cookie), `SecurityHeaders` (nosniff, DENY framing, referrer, permissions, HSTS on HTTPS, nonce CSP incl. Razorpay)
- [ ] ~~`EnsureMemberActive`~~ → moved to **P04**, where the `member` guard exists
- [x] Disks: `public` (site images), `private` (matrimonial, proofs, receipts; `serve` disabled)
- [x] Enums with EN/HI labels (`lang/{en,hi}/enums.php`) + pill colors: Gender, MemberStatus, MembershipStatus (no expiry/renewal), PaymentStatus/Mode/Purpose, OfflineMethod, DonationStatus, BusinessStatus, MatrimonialStatus, FamilyRelation
- [x] `Model::shouldBeStrict()` outside production. Production boot guard blocks `SMS_DRIVER=log` and `APP_DEBUG=true`
- [x] Vite: separate `web` and `admin` bundles. Tailwind/axios removed. `tokens.css` holds the `:root` tokens from the final design
- [x] Pint config + Pest (MySQL test DB) + `composer test` / `composer lint`
- [x] Git init at repo root, `.gitignore`, `.gitattributes` (LF). `.env`, vendor and node_modules are not tracked
- [x] Root README: setup and commands

## Acceptance
- [x] `npm run build` passes
- [x] `composer test`: 7 tests pass (EN home, `/hi` home + cookie, route names, security headers, CSP, EN+HI label for every enum case, no expiry state on membership)
- [x] `.env` not tracked

## Notes for later parts
- The default `users` migration is still present. P04 replaces it with `admin_users` + `members` guards.
- `welcome.blade.php` is a temporary page and is replaced by the real homepage in P02/P08.
