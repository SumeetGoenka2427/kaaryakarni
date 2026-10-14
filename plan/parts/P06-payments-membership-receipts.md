# P06 — Payments, Lifetime Membership & PDF Receipts

## Tasks
### Payment layer
- [ ] `payments`, `payment_events`, `receipts`, `memberships` migrations
- [ ] `PaymentGateway` interface (`createOrder`, `verifyCallback`, `verifyWebhook`, `refund`) + `RazorpayGateway`
- [ ] `PaymentService`: create order → checkout → **server-side signature verify** → mark successful inside a DB transaction → side effects (membership/donation status, counters, receipt, notification)
- [ ] `/webhooks/razorpay`: HMAC verify, idempotent on `provider_payment_id`, amount check, logged in `payment_events`
- [ ] Offline flow: member submits method (cash/cheque/UPI/NEFT) + reference + optional proof (private disk) → `submitted` → admin verifies or rejects
- [ ] Never trust the redirect query string. The status page polls the server for the final state

### Lifetime membership
- [ ] `/membership` public page (existing design): "One-time membership with lifetime validity", fee from settings
- [ ] `/member/membership` + `/member/membership/pay` (existing designs): online or offline pay
- [ ] Statuses: pending → payment_pending → payment_submitted → under_verification → **active (Lifetime Member)** / rejected / suspended
- [ ] Approval assigns `membership_no` (`KSJ-YYYY-NNNN`, gap-free via locked sequence) + approval date
- [ ] **No renewal, expiry, reminder or renewal button anywhere.** A test asserts this
- [ ] Admin → Memberships (existing design): records, filters, verify payment, approve/reject, receipt link

### Receipts (English only)
- [ ] `ReceiptService`: receipt no. per financial year (`KSJ/25-26/000123`), generated on success/verify
- [ ] Blade → dompdf template: receipt logo, org name/address, receipt no., date, name, amount (figures + words), mode, purpose/campaign, transaction/reference no., authorised signatory, 80G text if configured
- [ ] PDF is generated on demand and cached on the private disk. Download via policy-checked route
- [ ] `/member/payments` (existing design): list + receipt download
- [ ] Admin → Payments and Admin → Receipts (existing designs): search, filters (mode/status/purpose/date), view, download, regenerate

## Tests
Online success only after a valid signature. Forged webhook is rejected. Duplicate webhook is
idempotent. Offline submit → verify → receipt. Member can't download another member's receipt.
Lifetime status has no expiry. No renewal text in rendered pages.

## Acceptance
End to end: a member pays online (Razorpay test mode) or offline, admin approves, the member
sees "Lifetime Member" + membership no. + downloadable PDF receipt.
