# 01 — Architecture & Stack

## Stack

| Layer | Choice | Why |
|-------|--------|-----|
| Language | PHP 8.2 | Already installed (XAMPP 8.2.4) |
| Framework | Laravel 12 | LTS-style support, runs on PHP 8.2 |
| DB | MySQL 8 / MariaDB 10.6+ | Relational, FK support, XAMPP-ready |
| Views | Blade (server-rendered) | Fast first paint, SEO, minimal JS |
| JS | Existing vanilla JS (`main.js`, `admin.js`) as ES modules | No framework weight |
| Build | Vite (Laravel default) | Minified + hashed CSS/JS |
| DB note | Local XAMPP = MariaDB 10.4 (supported); production MySQL 8 recommended | |
| Cache | `file` locally, `redis` in production if available | Page/fragment/settings cache |
| Queue | `database` driver (+ `redis` optional) | SMS, notifications, image resizing, exports |
| Sessions | `database` driver | Server-side, revocable |

## Composer packages (minimal on purpose)

| Package | Use |
|---------|-----|
| `spatie/laravel-permission` | Admin roles & permissions |
| `intervention/image` (v3) | Resize, convert to WebP, strip EXIF on uploads |
| `barryvdh/laravel-dompdf` | PDF receipts (English only) |
| `spatie/simple-excel` | Streaming Excel/CSV exports (low memory) |
| `razorpay/razorpay` | Online payment gateway (behind interface) |
| `mews/purifier` | Sanitize admin rich-text (About page etc.) |
| Dev: `laravel/pint`, `pestphp/pest`, `barryvdh/laravel-debugbar` (local only) | Code style, tests, query profiling |

No Livewire, Inertia, Tailwind or jQuery. The design already exists in plain CSS/JS.

## Folder structure (Laravel project in `portal/` subfolder of repo)

```
kaaryakarni/
├── web/, admin/, shared/        ← original HTML designs (kept as reference, not served)
├── plan/                        ← this plan
└── portal/                      ← Laravel application
    ├── app/
    │   ├── Http/Controllers/{Web,Member,Admin,Webhooks}/
    │   ├── Http/Middleware/      SetLocale, SecurityHeaders, EnsureMember, EnsureActiveMember
    │   ├── Http/Requests/        FormRequest validation per action
    │   ├── Models/
    │   ├── Models/Concerns/HasTranslations.php
    │   ├── Policies/             ownership + admin permission checks
    │   ├── Services/
    │   │   ├── Payments/         PaymentGateway (interface), RazorpayGateway, OfflinePayment, PaymentService
    │   │   ├── Sms/              SmsProvider (interface), Msg91Provider, LogProvider
    │   │   ├── Otp/OtpService.php
    │   │   ├── Receipts/ReceiptService.php
    │   │   ├── Media/ImageService.php
    │   │   └── Settings/SettingsService.php
    │   ├── Notifications/        Laravel notifications → database + sms channel
    │   ├── Observers/            cache invalidation, audit logging
    │   └── View/Components/      x-logo, x-field, x-table, x-modal, x-pill, x-empty-state …
    ├── resources/
    │   ├── css/  tokens.css, style.css (web), admin.css
    │   ├── js/   main.js, admin.js, carousel.js, lightbox.js
    │   └── views/{layouts,components,web,member,admin,pdf,errors}
    ├── lang/en/*.php, lang/hi/*.php
    ├── routes/web.php, member.php, admin.php, webhooks.php
    └── database/{migrations,seeders,factories}
```

## Request flow

```
Browser → (optional /hi prefix) → SetLocale middleware → route
   → controller (thin) → FormRequest validation → Policy/Gate
   → Service (business rules, DB transaction) → Model
   → Blade view (escaped output, cached fragments) → response + security headers
```

Rules:
- Controllers stay thin. Business rules live in Services (one source of truth).
- Every status change (approve, verify, suspend) goes through a Service method that
  writes an `audit_logs` row and dispatches a notification.
- Status values are PHP backed **enums** (`MemberStatus`, `PaymentStatus`, …), never free strings.

## Route groups

| Group | Prefix | Middleware |
|-------|--------|-----------|
| Public | `/` and `/hi` | `web`, `setlocale`, `cache.headers` (public pages) |
| Member | `/member`, `/hi/member` | `auth:member`, `member.active` (matrimonial also `member.lifetime`) |
| Admin | `/admin` | `auth:admin`, `permission:*`; admin UI is English with EN/HI content tabs |
| Webhooks | `/webhooks/{provider}` | no CSRF, signature-verified, rate-limited |

Two auth guards: `member` (OTP, `members` table) and `admin` (password, `admin_users` table).
They are fully separate, so a member session can never reach admin.
