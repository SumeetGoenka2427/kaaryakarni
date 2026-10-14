# Khandelwal Samaj Membership Portal — Jaipur

Bilingual (English / हिन्दी) membership portal and admin panel for the Khandelwal
Samaj, Jaipur, built with **Laravel 12** + MySQL.

| Folder | What it is |
|--------|-----------|
| `portal/` | **The application** (Laravel). Everything runs from here. |
| `plan/` | Build plan, divided into parts P01–P13. Start with [plan/README.md](plan/README.md). |
| `web/`, `admin/`, `shared/` | The **final HTML designs** (static, localStorage demo). Reference only: they are converted into Blade views part by part and are not served by the app. |
| `CLAUDE.md` | Product requirements |

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `gd`, `intl`, `zip`, `fileinfo`, `bcmath`, `exif`
- Composer 2
- MySQL 8 or MariaDB 10.4+ (XAMPP works)
- Node.js 20+ and npm

## Local setup

```bash
cd portal
composer install
npm install
cp .env.example .env           # then set DB_* and ADMIN_SEED_PASSWORD
php artisan key:generate
```

Create the databases (utf8mb4 is required for Hindi text):

```sql
CREATE DATABASE kaaryakarni      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE kaaryakarni_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then:

```bash
php artisan migrate --seed       # creates roles + first super-admin from ADMIN_SEED_* in .env
php artisan storage:link        # serves uploaded logos/images
npm run build                  # or `npm run dev` while editing CSS/JS
php artisan serve              # http://localhost:8000  (Hindi: /hi)
```

`composer dev` runs the server, queue worker, log viewer and Vite together.

## Admin panel

Open `/admin` and log in with `ADMIN_SEED_USERNAME` / `ADMIN_SEED_PASSWORD` from `.env`
(change the password after the first login). Five wrong attempts lock the username for
15 minutes, and sessions end after 30 idle minutes.

## Member login (local)

Open `/login` (or `/register`), enter any valid 10-digit mobile number and read the OTP from
`portal/storage/logs/otp.log` (only while `SMS_DRIVER=log`). New numbers continue to the
registration form. For real SMS set `SMS_DRIVER=msg91`, `MSG91_AUTH_KEY` and each template's
DLT/MSG91 template id.

## Commands

| Command | Does |
|---------|------|
| `composer test` | Runs the Pest test suite against the `kaaryakarni_test` database |
| `composer lint` | Checks code style (Laravel Pint) |
| `./vendor/bin/pint` | Fixes code style |
| `npm run build` | Builds minified, hashed CSS/JS into `public/build` |

## Development notes

- **OTP in local development:** with `SMS_DRIVER=log`, OTPs are written to
  `storage/logs/otp.log` and never shown on screen. The app refuses to start in
  production with `SMS_DRIVER=log` or `APP_DEBUG=true`.
- **Languages:** English pages have no URL prefix and Hindi pages live under `/hi`.
  Register public routes with `Route::localized(...)` so both exist.
- **Never commit `.env`.** All secrets (SMS, Razorpay, admin seed password) come from environment variables.

## Build progress

See [plan/README.md](plan/README.md). Completed: **P01 Foundation**, **P02 Design system, layouts & bilingual engine**, **P03 Settings, Branding/Logo, Site Texts + admin login**, **P04 Member OTP login/registration, SMS layer, Admin Users**.
