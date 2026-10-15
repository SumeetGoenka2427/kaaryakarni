# P10 — Matrimonial (Private) ✅ Done

## Data
- [x] `matrimonial_profiles`: ULID route key, owner member, optional link to a family member, relation, name, gender, **date of birth** (age is calculated, never stored), height (cm, dropdown 4'6"–6'6"), marital status, father / mother, gotra, native place, area, education, occupation, about, bio-data PDF (private disk), `show_contact`, status, admin note, submitted / approved dates, sample flag, soft deletes. Indexes on (status, gender, dob) and (status, area)
- [x] `matrimonial_photos` (1–4 per profile, re-encoded WebP 320/800, **private disk**)
- [x] `matrimonial_reports` (one per reporter per profile, open / resolved)

## Access rules (`MatrimonialService`, the only place status changes)
- [x] Create / browse / view: logged-in **verified member with an active Lifetime Membership**. Others see a locked explanation page (403 on direct URLs)
- [x] Up to **3 profiles per member** (self or family). Only **approved** profiles of *other* members are visible; unapproved ones return 404
- [x] Every owner create / edit → **Pending** (re-review). Status never taken from input
- [x] Photos + bio-data streamed by `/matrimonial-files/...` only to the owner, an admin with `matrimonial.view`, or an eligible member viewing an approved profile. Everyone else gets 404. `Cache-Control: private`, `X-Robots-Tag: noindex`
- [x] Contact number shown only when the owner ticks `show_contact`
- [x] All pages are in the member layout (`noindex`), not in the sitemap or public nav

## Member area (EN + /hi, designs `member/matrimonial.html`, `member/matrimonial-browse.html`)
- [x] My profiles list + add / edit form side by side (stacks on phones), delete with confirmation, rejection / suspension reason shown
- [x] **Family shortcut**: unmarried adult son / daughter / brother / sister / grandchild cards on My Family get "Create matrimonial profile", which prefills the form
- [x] Browse: gender, min / max age, area, keyword (education / occupation / native place / gotra), pagination, empty state "No matching approved profiles found"
- [x] Profile detail page: photos, details, about, bio-data PDF, contact (if allowed), **Report this profile** (reason + details)

## Admin (design `admin/matrimonial.html`)
- [x] Tabs Pending / Approved / Rejected / Hidden / Suspended + **Reports** queue, search by name / area, open-report badge
- [x] Review page: Approve, Reject (reason), Hide, Unhide, Suspend (reason), Edit, Delete (removes photos + PDF), resolve reports
- [x] Permissions: `matrimonial.view` / `matrimonial.manage` (role `matrimonial-moderator`). Audit log on every action (name + ULID only, no personal details)
- [x] SMS (EN/HI): `matrimonial_approved`, `matrimonial_rejected`

## Sample data
- [x] `SampleMatrimonialSeeder` (dev only, `is_sample`): 6 profiles with generated silhouette placeholder photos (never real people)

## Tests (`tests/Feature/MatrimonialTest.php`, 13 tests)
Guests redirected; non-lifetime member locked / 403; pending profiles invisible to others (browse, detail, photo, PDF);
1 photo required + 3-profile cap; under-18 and non-PDF rejected; filters incl. exact age; own profiles excluded;
contact privacy; no editing / deleting others' profiles (404); edit → re-review; family prefill;
admin approve / hide / reject / suspend / delete with SMS + audit; admins without permission blocked; report + resolve.
