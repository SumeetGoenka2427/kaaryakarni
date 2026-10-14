# P06 — Payments, Lifetime Membership & PDF Receipts ✅ Done

## Payment engine (shared with donations in P07)
- [x] Tables: `payments` (single source of truth, ULIDs), `payment_events` (callback/webhook log, unique event id), `memberships` (one per member, **no expiry/renewal columns**), `receipts`, `sequences` (gap-free counters)
- [x] `PaymentGateway` interface + `RazorpayGateway` (orders, checkout HMAC check, webhook HMAC check). Swapping the provider = new class + binding
- [x] `PaymentService` is the only place payment status changes:
  - Online: created → pending → **successful only after server-side signature verification** (checkout callback) or a **signed webhook**. Idempotent, row-locked, amount re-checked (a mismatch marks the payment failed)
  - Offline: submitted (method, reference, date, optional private proof image/PDF) → admin **verify** (receipt issued) or **reject** (reason, member can pay again)
  - Payable hooks (`Membership`, later `Donation`) run in the same transaction
- [x] Without Razorpay keys, online payment shows "not available" (never faked). Offline works fully
- [x] Checkout page loads Razorpay's script only there (CSP-allowed) and posts the result to our server for verification. Closing the widget marks the attempt failed. The webhook still confirms late payments

## Lifetime membership
- [x] Becomes **Lifetime Member automatically** when the fee is confirmed **and** the profile is verified (whichever is last), with a membership ID `KSJ-YYYY-NNNN` and an SMS
- [x] Member pages (designs `member/membership.html`, `pay-membership.html`, `payments.html`): status card, pay CTA, online/offline toggle, rejection reason, receipt download, payment history (own payments only)
- [x] Dashboard membership status card + "Pay Now"
- [x] Fee changes apply only to members who have not paid. Lifetime members are never charged again

## Receipts
- [x] Numbered per Indian financial year: `KSJ/26-27/000001` (gap-free, locked)
- [x] English A5 PDF (dompdf): logo (Branding receipt logo or default mark), org address/phone/email, receipt no, date, payer + membership ID, purpose, amount + **amount in words (Indian system)**, mode/method, transaction reference, authorised signatory. ~30 KB (font subsetting)
- [x] Stored on the private disk. Downloads are owner-checked (member) or permission-checked (admin). Admin can regenerate after logo/address changes

## Admin (designs `memberships.html`, `payments.html`, `receipts.html`)
- [x] Memberships: stat cards, fee setting, status filter, offline membership payments awaiting review
- [x] Payments: filters (mode/status/purpose/date/search), detail with proof viewer + gateway event log, **Verify / Reject (reason)** for `payments.verify` only, CSV export
- [x] Receipts: tabs by purpose, search, PDF download, regenerate

## SMS (EN/HI templates)
`payment_submitted`, `payment_received` (with receipt no), `payment_rejected`, `membership_active`

## Fixed during testing
- Amount-mismatch failure was rolled back by the exception in the same transaction, so the payment stayed "pending". Now committed as "failed" before the error is raised
- Member payment table on phones hid the Download button behind a sideways scroll. Card-style mobile tables now apply site-wide

## Tests: 150 passing
Valid / forged checkout signature, signed / forged / replayed webhook, amount mismatch, callback + webhook
idempotency (one receipt), activation waits for profile verification, offline submit → verify → receipt →
lifetime, reject → pay again, validation, no double payment, receipt owner-only + sequential numbers,
gateway-not-configured, permission 403 for verification, admin screens + CSV + fee change.
