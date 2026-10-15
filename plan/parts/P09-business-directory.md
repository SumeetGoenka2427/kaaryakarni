# P09 — Business Directory ✅ Done

## Data
- [x] `business_categories` (EN/HI, slug, active, order) + 12 default categories (`BusinessCategorySeeder`, production)
- [x] `business_profiles`: one per member (design "My Business Profile"), category, area, name, description, address, phone / WhatsApp / email with **per-field public toggles**, website, hours, logo (responsive WebP), status, featured, admin note, submitted/approved dates, sample flag, soft deletes. Indexes on status/category/area + **FULLTEXT** (name, description)

## Workflow (`BusinessService`, the only place status changes)
- [x] Verified members only. Submit → **Pending** → admin **Approve** / **Reject (reason)** / **Request changes (note)**. Approved ↔ **Hidden**. **Feature** toggle (approved only)
- [x] Member edits of name / category / description / photo on an approved listing send it back to Pending when Settings → "re-approval on edit" is on. Minor edits (hours, phone…) stay live
- [x] Status / featured never accepted from member input. SMS on approve / reject / changes requested. Every admin action audit-logged

## Public (EN + /hi, designs `business-directory.html`, `business-detail.html`)
- [x] `/business-directory`: **approved only**, search (FULLTEXT + LIKE for short words), category + area filters (auto-submit), featured first, pagination, empty state text from CLAUDE.md
- [x] `/business-directory/{slug}`: photo, category, area, address, hours, owner name, Call / WhatsApp / Website **only if allowed**, description, Google Maps embed + Get Directions, related businesses, `LocalBusiness` JSON-LD. Pending / rejected / hidden / deleted → "Business not found" (404)
- [x] Homepage "Featured Businesses & Professionals" (4 approved, featured first) + "Approved Businesses" statistic

## Member + admin
- [x] `/member/business` (design `member/business.html`): status pill, admin note, form with privacy toggles, logo, re-approval notice
- [x] Admin → Business Directory (design `admin/businesses.html`): tabs Pending / Changes Requested / Approved / Rejected / Hidden / Featured with counts, search, review page (approve, request changes, reject, feature, hide, edit, delete), note dialog
- [x] Admin → Business Categories: add EN/HI, inline rename + order, activate/deactivate, delete only when unused

## Fixed during testing / responsive check
- Member's listing was read through a cached relation and could show a stale status/note. Now always queried fresh
- On phones the detail photo (aspect-ratio + min-height) forced the page to 473px wide. Fixed, and all public pages re-checked at exactly 390px

## Tests: 173 passing
Hidden until approved, verified-member requirement, status/featured tampering ignored, approve + SMS + audit,
contact privacy, reject / request changes / resubmit, re-approval setting on/off, feature / hide / delete,
search + category + area filters, validation (incl. javascript: URL), categories CRUD, permission 403.
