# Khandelwal Samaj Portal — Laravel Build Plan

This folder is the build plan for moving the finished HTML designs (`web/`, `admin/`)
onto a real **PHP Laravel** backend. Every page will be **bilingual (English / हिन्दी)**,
and all content will be **editable by admin in both languages**.

> **The HTML designs are final.** We do not redesign anything. Each HTML page becomes a
> Blade view that looks the same. We only swap the localStorage demo data
> (`shared/js/store.js`) for real database data, and the demo session for real
> server-side authentication.

## Documents

| # | File | What it covers |
|---|------|----------------|
| 01 | [01-architecture-and-stack.md](01-architecture-and-stack.md) | Stack, packages, folder structure, request flow |
| 02 | [02-database-design.md](02-database-design.md) | All tables, columns, indexes, relations |
| 03 | [03-bilingual-content.md](03-bilingual-content.md) | How EN/HI works for UI text and admin content |
| 04 | [04-design-system-and-html-conversion.md](04-design-system-and-html-conversion.md) | `:root` tokens, fonts, logo, HTML → Blade page map |
| 05 | [05-security.md](05-security.md) | Security controls checklist |
| 06 | [06-performance.md](06-performance.md) | Speed and optimization strategy |
| 07 | [07-open-questions.md](07-open-questions.md) | Decisions the client must confirm |
| — | [parts/](parts/) | **The build, divided into 13 parts** (do them in order) |

## Build parts (in order)

Each part is a vertical slice: migrations + models + admin screens + public/member
screens + tests for that module. Each part ends in a working, testable state.

| Part | Name | Depends on |
|------|------|-----------|
| [P01](parts/P01-foundation.md) ✅ | Foundation: Laravel setup, tooling, env, base config | — |
| [P02](parts/P02-design-system-layouts.md) ✅ | Design system, layouts, Blade components, bilingual engine | P01 |
| [P03](parts/P03-settings-branding-logo.md) ✅ | Site settings, **Branding / Logo manager**, translations manager | P02 |
| [P04](parts/P04-auth.md) ✅ | Admin auth + roles, member OTP auth, SMS abstraction | P03 |
| [P05](parts/P05-areas-members-profile.md) | Jaipur areas, member registration, profile, **family details**, verification | P04 |
| [P06](parts/P06-payments-membership-receipts.md) | Payment abstraction, lifetime membership, PDF receipts | P05 |
| [P07](parts/P07-donations-campaigns.md) | Donation campaigns, online/offline donations, live totals | P06 |
| [P08](parts/P08-homepage-banners-cms.md) | Homepage 3-column banner, carousel, page content CMS | P07 |
| [P09](parts/P09-business-directory.md) | Business directory + approval workflow | P05 |
| [P10](parts/P10-matrimonial.md) | Private matrimonial module + approval workflow | P05 |
| [P11](parts/P11-gallery-committee-announcements.md) | Gallery, committee members, announcements, contact | P03 |
| [P12](parts/P12-dashboard-reports-notifications.md) | Admin dashboard, reports, exports, notifications, audit logs | P06–P11 |
| [P13](parts/P13-hardening-performance-launch.md) | Security pass, performance pass, full testing, deployment | all |

## Key decisions (summary)

- **Laravel 12 on PHP 8.2** (matches the installed XAMPP PHP 8.2.4), MySQL/MariaDB.
- **Server-rendered Blade**, no SPA framework. The existing vanilla JS (carousel, nav,
  lightbox, modals) is kept and bundled with Vite. This is the fastest option for
  first load, the best for SEO, and needs the least JavaScript.
- **Keep the existing CSS.** `style.css` and `admin.css` move into `resources/css/`.
  All colors, fonts, radii, shadows and spacing stay as CSS custom properties on `:root`.
- **Bilingual:** static UI text uses Laravel `lang/en` and `lang/hi` files, and admin can
  override any of it from a DB "Site Texts" screen. Admin-managed content uses paired
  `*_en` / `*_hi` columns. Hindi URLs use the `/hi/...` prefix (good for SEO).
- **Logo manager** in Admin → Branding: header logo, footer/light logo, favicon,
  receipt/PDF logo, social share image. They are used everywhere through one `<x-logo>`
  component, with cache-busted URLs.
- **PDF receipts and exports are English only** (`barryvdh/laravel-dompdf`). Hindi applies
  to **website text only**: public pages, member area UI and admin-managed content.
- **Payment gateway** behind a `PaymentGateway` interface (Razorpay first). **SMS**
  behind an `SmsProvider` interface (MSG91 first, `log` driver for local development).

## Progress tracking

Tick the boxes inside each part file as tasks are completed. A part is "done" only
when its **Acceptance** section passes. Rendering pages alone is not enough
(see CLAUDE.md §36).
