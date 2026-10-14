# 07 — Open Questions for the Client

Until these are answered, the **assumption** in each row is what gets built. Every
assumption is configurable, so changing it later needs no code rewrite.

| # | Question | Working assumption |
|---|----------|--------------------|
| 1 | Final list of ~20 Jaipur areas (EN + HI names)? | 20 sample areas from the current design, flagged `is_sample` |
| 2 | Lifetime membership fee? | ₹5,000, editable in Admin → Settings |
| 3 | Payment gateway? | Razorpay (UPI/cards/netbanking) |
| 4 | SMS provider + DLT registration (sender ID, template IDs)? | MSG91. OTP + approval templates need DLT approval before go-live |
| 5 | Homepage right banner column: **Advertisement** (as in the final HTML) or **donation info** (as in CLAUDE.md)? | Admin chooses per panel: `advertisement` or a donation display mode. Default right = advertisement, matching the final design |
| 6 | Who provides Hindi translations of the content? | We seed Hindi for UI labels. Client reviews. Admin fills content Hindi |
| 7 | Hosting: shared hosting or VPS? | VPS (queue worker + optional Redis). Shared hosting fallback: `database` queue via cron `schedule:run` |
| 8 | Is 80G tax-exemption info needed on donation receipts (PAN, 80G reg. no.)? | Optional PAN field on donation. 80G text configurable in settings |
| 9 | Must members be lifetime members to create a business or matrimonial profile? | Business: any verified member. Matrimonial browse/create: active lifetime member |
| 10 | Logo files (SVG/PNG, light + dark versions)? | "KS" text mark until uploaded via Admin → Branding |
| 11 | Email needed for notifications too, or SMS + in-app only? | SMS + in-app. Email channel built but off |
| 12 | Domain name? | Set via `APP_URL` |
