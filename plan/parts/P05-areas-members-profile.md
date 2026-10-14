# P05 — Jaipur Areas, Members, Profile & Family Details

## Tasks
### Areas (Admin → Areas, existing design)
- [ ] `areas` migration (name EN/HI, active, order, is_sample) + 20 sample areas
- [ ] Add/edit (EN/HI), activate/deactivate with confirm, drag/arrow reorder, cached list
- [ ] Deactivated areas stay on existing members but can't be picked for new ones

### Member registration & profile
- [ ] Registration step 2 (existing `register.html`): name, father/husband name, gender, DOB,
      email (optional), address, **Area / Locality** (from admin list), photo. **No city/state fields**
- [ ] Submit for verification → status `under_verification` (or `payment_pending` if fee is unpaid, see P06)
- [ ] `/member/profile` (existing design): view/edit allowed fields. Mobile change requires OTP on the new number
- [ ] Photo upload: validate, re-encode to WebP, 2 sizes
- [ ] `/member/dashboard` (existing design): status cards, quick links, notifications preview

### Family details (new)
Members can add, edit and remove their full family details.
- [ ] `family_members` migration (see 02): relation (spouse/wife/husband, son, daughter, father,
      mother, brother, sister, daughter-in-law, son-in-law, grandchild, other), name, gender, DOB,
      marital status, education, occupation, mobile, email, blood group, photo, deceased flag,
      lives-with-member, notes
- [ ] `/member/family`: list as cards (in the member area design), add/edit form in a modal or
      page, delete with confirm, reorder
- [ ] Validation rules (e.g. only one father and one mother, spouse limit configurable, DOB not in future)
- [ ] Policy: a member only sees and edits their own family rows (ULID in URLs)
- [ ] Shown on the member dashboard as a "Family" summary card
- [ ] "Create matrimonial profile" shortcut on an unmarried son/daughter row (pre-fills P10 form)
- [ ] Admin → Member detail → **Family tab** (view/edit). Included in member export (optional columns)
- [ ] Family data is **never public**

### Admin → Members (existing design)
- [ ] List with search (name/mobile/membership no.), filters (status, area, date), pagination, mobile cards
- [ ] Detail view: profile, family, membership, payments, businesses, verification history
- [ ] Verify / Reject (reason required) / Suspend / Reinstate with confirm. Each writes `member_verifications` + audit + SMS
- [ ] Admin create member manually (offline registrations) and edit
- [ ] Export (Excel/CSV) is left for P12

## Tests
Area selection uses only active areas. No city/state in the form. Profile edit can't change
status. Member A can't read or edit member B's family row by ULID (403/404). Admin verify/reject flow.

## Acceptance
A new member registers, picks an area, adds wife/son/father details, edits them, and an admin
verifies the member. Everything is visible in both languages.
