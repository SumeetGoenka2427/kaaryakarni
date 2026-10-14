# P05 — Jaipur Areas, Members, Profile & Family Details ✅ Done

## Member area (EN + /hi)
- [x] `/member/profile` (design `member/profile.html`): name, father/husband, DOB, gender, email, **Area / Locality**, address, private photo. Mobile and status are read-only (mobile changes go through the Samaj office / admin). No city/state
- [x] `/member/family`: add / edit / remove family members (spouse, son, daughter, father, mother, brother, sister, in-laws, grandchildren, other) with gender, DOB (age shown), marital status, education, occupation, mobile, email, blood group, photo, late flag, lives-with-member, notes. Only one father and one mother, maximum 30 entries
- [x] Family rows are always looked up inside the logged-in member's own family (ULID). Another member's row returns 404. Family is never shown on public pages
- [x] Member and family photos are stored on the **private disk** and streamed by `PrivateImageController` only to the owner or an admin with `members.view`
- [x] Dashboard quick-action cards (design `member/dashboard.html`) appear per module as routes go live. Family count shown

## Admin
- [x] **Jaipur Areas** (`admin/areas.html`): add EN/HI, edit, move up/down, activate/deactivate (confirm), delete only if unused, sample-data banner (editing an area marks it real). Area dropdown cache refreshes automatically
- [x] **Members** (`admin/members.html`): status tabs with counts, search (name/mobile/ID), area + date filters, pagination, **CSV export** (streamed, UTF-8 BOM for Hindi)
- [x] Member detail: Profile / Family / Verification History tabs. Verify, Reject (reason), Suspend (reason), Reinstate, all with confirm dialogs. Edit details including mobile. Manual registration for offline forms. Admin family add/edit/remove
- [x] `MemberService` is the only place status changes happen: allowed transitions only, history row, audit log, bilingual SMS (`member_verified/rejected/suspended/reinstated`)

## Notes
- Member **verification** (profile checked) is separate from **lifetime membership approval** (fee paid), which comes in P06
- The "create matrimonial profile from a son/daughter" shortcut is added in P10
- Sample members (9876543210 / …11 / …12) with a family for demos: `SampleMembersSeeder` (dev only)

## Tests: 134 passing
Profile edit (mobile/status cannot be changed), private photo access rules, family CRUD, one father/mother,
validation, cross-member 404s, no family on public pages, admin verify/reject/suspend/reinstate + history/
audit/SMS, invalid transitions, manual registration + unique mobile, admin family, CSV export, areas
(unique, reorder, deactivate, no delete when used), permission 403s.
