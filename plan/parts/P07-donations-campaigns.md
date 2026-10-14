# P07 — Donation Campaigns & Donations

## Tasks
### Campaigns
- [ ] `donation_campaigns`, `donations` migrations
- [ ] Seed permanent **Samaj Donation** (`is_permanent`). Policy + model guard block delete/deactivate. The admin UI hides those buttons
- [ ] Admin → Donations (existing design): campaign list, create/edit (title/description EN/HI,
      image, target, dates, order, online/offline toggles, tickets option with label EN/HI + price), activate/deactivate with confirm
- [ ] Campaign detail in admin: donations list, totals, online/offline split

### Donating
- [ ] `/donations` + `/donations/{slug}` (existing design): progress, donors count, donate form
- [ ] Donor can be a guest or a logged-in member (prefilled). Fields: name, mobile, email, PAN (optional), amount presets + custom, anonymous toggle, "show my name publicly"
- [ ] Ticket purchase (e.g. Diwali Utsav entry pass): quantity × price
- [ ] Online → P06 payment layer. Offline → submitted → Accounts admin verifies → receipt + SMS
- [ ] Statuses: pending, successful, failed, submitted, verified, refunded, cancelled
- [ ] Counters (`raised_amount`, `donor_count`) updated in the payment transaction. Refund reverses them. `donations:recalculate` command
- [ ] Admin → Donation records: filters online/offline, status, campaign, date. Verify/reject offline. Refund/cancel (online refund via gateway)

### Live data helpers (used by P08 homepage)
- [ ] `CampaignStats` query object: collected, target, %, donor count, recent donations (names only if setting + donor consent, else "A Samaj member")
- [ ] Cache invalidation on payment verify

## Tests
Samaj Donation can't be deleted or deactivated (UI + direct request). Admin-created campaign
shows publicly when active and in date range. Online and offline donations. Totals update
only after success/verify. Refund lowers the totals. Receipt is generated.

## Acceptance
Totals shown on the donations page match the DB after mixed online/offline/refund scenarios.
