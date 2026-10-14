# P09 — Business Directory

## Tasks
- [ ] `business_categories` (EN/HI) + `business_profiles` migrations. Admin → Categories screen
- [ ] `/member/business` (existing design): create/edit (name, category, description, area, address, phone/WhatsApp/email with show/hide toggles, website, socials, hours, logo, cover). Submit → `pending`
- [ ] Important edits (name, category, description, logo) → back to `pending` if the setting is on. Minor edits stay live
- [ ] Admin → Businesses (existing design): tabs Pending / Approved / Rejected / Hidden / Featured, search/filter, review, approve, reject (reason), request changes (note), edit, hide, delete (confirm), feature toggle. SMS + in-app notification
- [ ] `/business-directory` (existing design): **only approved**, search (FULLTEXT), category + area filters, pagination, empty state "No approved businesses found for the selected filters."
- [ ] `/business-directory/{slug}`: details, contact only if allowed, owner name only (no member mobile)
- [ ] Featured businesses on homepage (cached)
- [ ] SEO: title/description, `LocalBusiness` JSON-LD with Jaipur address

## Tests
Pending/rejected/hidden return 404 publicly. Member can't edit another member's business.
Approval publishes it. Re-approval toggle works.
