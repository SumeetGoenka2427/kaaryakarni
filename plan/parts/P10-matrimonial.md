# P10 — Matrimonial (Private)

## Tasks
- [ ] `matrimonial_profiles`, `matrimonial_photos`, `matrimonial_reports` migrations
- [ ] Access: logged-in **active lifetime member** only (middleware). Everything `noindex`, not in sitemap or public nav
- [ ] `/member/matrimonial` (existing design): create/edit profile for self or a family member (prefill from `family_members`), photos (max 5, private disk), bio-data PDF (private), privacy toggles (show contact, show photos). Submit → `pending`
- [ ] `/member/matrimonial/browse` (existing design): approved profiles only, filters (gender, age range, area, education, occupation), pagination, empty state "No matching approved profiles found."
- [ ] Profile detail: contact shown only if `show_contact`. Photos via authorized streaming route
- [ ] Report/flag profile → admin queue
- [ ] Privacy messaging banner on all matrimonial pages (EN/HI)
- [ ] Admin → Matrimonial (existing design): Pending / Approved / Rejected / Hidden / Suspended tabs, review, approve, reject (reason), hide, edit, suspend, delete, reports queue
- [ ] Editing an approved profile's key fields → re-review

## Tests
Guest → redirect to login. Non-lifetime member → 403 with explanation. Direct photo URL without
login → 403. Pending profiles are not visible to others. Filters work.
