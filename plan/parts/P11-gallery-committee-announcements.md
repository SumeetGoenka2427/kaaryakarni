# P11 — Gallery, Committee, Announcements, Contact

## Tasks
### Gallery
- [ ] `gallery_albums`, `gallery_images` migrations
- [ ] Admin → Gallery (existing design): album create/edit (title/description EN/HI, event date), multi-image upload with progress (queued resize → WebP 480/960/1600 + thumbs), drag reorder, captions EN/HI, set cover, delete image, activate/deactivate, delete album (confirm)
- [ ] `/gallery` + `/gallery/{slug}` (existing design): responsive grid, album titles, lightbox (keyboard, swipe, focus trap), lazy loading, empty state
- [ ] Homepage preview (latest 4 albums, cached)

### Committee members
- [ ] Admin → Committee: name/designation EN/HI, tier (leadership/general), photo, order, active
- [ ] `/committee` + about-page leadership block (existing designs)

### Announcements / events
- [ ] Admin → Announcements: title/body EN/HI, type (event/notice), event date, schedule, active, order
- [ ] Homepage "Community Events & Announcements" section uses this data

### Contact
- [ ] `/contact` form (existing design): validated, throttled, honeypot → `contact_messages`
- [ ] Admin → Contact Messages: list, read, mark resolved

## Acceptance
Admin uploads an album, and its title and images appear on `/gallery`, `/hi/gallery` and the homepage, working at 360 px.
