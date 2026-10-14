# P07 — Donation Campaigns & Donations ✅ Done

## Campaigns
- [x] `donation_campaigns` (EN/HI title + description, image, target, live `raised_amount` / `donor_count`, permanent flag, active, online/offline switches, optional fixed-price **tickets** with EN/HI label, start/end dates, order, soft deletes)
- [x] Permanent **Samaj Donation** seeded on every install (`DonationCampaignSeeder`, in `ProductionSeeder`). It can't be deleted or deactivated: enforced in the model, the controller and the admin UI
- [x] Admin → **Donation Campaigns** (`admin/donations.html`): list with target / raised / % / donors / tickets / dates / status, create & edit (EN/HI, image, tickets, payment options, dates), activate/deactivate, delete only if no donations

## Donating (EN + /hi, members and guests)
- [x] `/donations` (design `donations.html`): permanent campaign card, other campaigns, donate form. `/donations/{campaign}`: description, live progress, recent contributions, form
- [x] Form: campaign, donation or ticket (quantity × fixed price, a submitted amount is ignored), amount presets (₹501–₹11,000) + custom, name, mobile, email, PAN (optional), message, **"do not show my name publicly"**, online or offline (method, reference, date, proof)
- [x] Uses the P06 payment engine: online confirmed only by verified signature or signed webhook, offline verified by Accounts admin, receipt for every confirmed donation, SMS
- [x] Guests can open only checkouts started in their own session. Members are prefilled and linked

## Live totals & privacy
- [x] Totals change only inside the payment transaction (confirm) or a refund. Homepage reads one row per campaign (cached, flushed on change)
- [x] `php artisan donations:recalculate` rebuilds totals from confirmed donations
- [x] Donor names are public only if Settings allows **and** the donor did not choose anonymity. Otherwise "A Samaj member"

## Homepage (CLAUDE.md §5 / §13)
- [x] Banner panel modes: **Live campaign — full** (design left panel: label, title, description, amount, progress, donors, optional recent donations, Donate) and **Live campaign — compact** (collected, target, progress bar, % and donor tiles, gold Donate), plus membership CTA / advertisement. Admin picks the campaign and toggles target / % / donors / recent. A closed campaign falls back to Samaj Donation
- [x] "Active Donation Campaigns" section (3 cards) and a "Donations Collected" statistic (₹ L / Cr format)
- [x] Fresh installs: left = live campaign (full), right = advertisement (as in the final design). Admin can switch the right panel to the compact live campaign

## Admin records
- [x] **Donation Records**: search, campaign / mode / status / date filters, totals, link to the payment for offline verification, **Refund** (reason, reduces totals, payment marked refunded), CSV export (incl. PAN, receipt no)

## Fixed during the responsive check
- Missing `share_progress` text key
- Radio/checkbox inputs inside `.field` were stretched to full width by the design CSS (site-wide fix)

## Tests: 162 passing
Permanent campaign protection (model + admin), guest online donation with signature + live totals, guest
checkout isolation, offline donation → verify → totals + receipt, ticket pricing (amount tampering ignored),
closed campaign / disabled mode / no tickets, validation, donor privacy (+ setting off), refund + recalculate,
homepage live panels/sections, admin campaign CRUD + EN/HI, permission 403.
