# P11 — Gallery, Committee, Announcements, Contact ✅ Done

## Gallery
- [x] `gallery_albums` (slug, title / description EN/HI, event date, cover photo, photo count, active, order, sample flag) and `gallery_images` (responsive WebP 480/960/1600, caption EN/HI, order)
- [x] `GalleryService` keeps the cover photo and photo count in step with the photos. Every admin action is audit-logged
- [x] Admin → Gallery (design `admin/gallery.html`): album list with search, add / edit album, upload up to 20 photos at a time, captions EN/HI, move photos up / down, set cover, delete photo, activate / deactivate, delete album (removes the files). Each step asks for confirmation
- [x] `/gallery` + `/hi/gallery` (design `web/gallery.html`): every active album with photos shows its title, date, description and up to 8 photos; "View all N photos" links to `/gallery/{slug}` (paginated). Empty state: "No gallery albums are currently available"
- [x] Lightbox: browses within one album, responsive image, counter, keyboard (← → Esc), touch swipe, focus trap / return. Tiles are links to the full photo, so they still work without JavaScript
- [x] Homepage "Moments From the Samaj" preview: cover photos of the latest 4 albums (cached, refreshed on any gallery change)
- [x] Inactive or empty albums are not shown publicly (404 on direct link)
- [x] `SampleGallerySeeder` (development only, `is_sample`): 4 albums from the bundled sample photos
- [ ] Drag-and-drop reordering and upload progress bar were simplified to ↑ / ↓ buttons and a normal upload. They can be added later if the office needs them

## Committee, announcements, contact
- [x] Done earlier in [W01](W01-website-pages.md): Admin → Committee, Admin → Announcements (homepage events section), `/contact` form → Admin → Contact Messages

## Tests (`tests/Feature/GalleryTest.php`, 7 tests)
Admin creates an album and uploads photos, which then appear on `/gallery`, `/hi/gallery`, the album page and the homepage.
Inactive and empty albums are hidden. Reorder, cover, caption (EN/HI) and delete keep the counts right.
Deleting an album removes its files. Upload validation (file type, 20-photo limit). Unique slugs.
Guests and admins without content permission are blocked.

## Acceptance ✅
Admin uploads an album, and its title and photos appear on `/gallery`, `/hi/gallery` and the homepage. Checked at 390, 768 and 1280 px with no horizontal overflow; the lightbox opens and swipes on a phone.
