# W01 — Website Pages Pass ✅ Done (requested before P05)

**Goal:** the public website pages from the final HTML, fully working, with every
piece of content admin-editable in English and Hindi, and responsive on phone, tablet and laptop.

## Public pages (EN + `/hi`)
| HTML | Route | Content source |
|------|-------|----------------|
| `index.html` | `/` | 3-column top banner (slides + left/right panels), About block, live stats, membership CTA, events, important links |
| `about.html` | `/about` | History text + image, Mission/Vision/Values cards, leadership committee |
| `committee-members.html` | `/committee` | Leadership cards (photo or initials) + general committee chips with "Load 20 More" (works without JS) |
| `membership.html` | `/membership` | Highlight cards, fee (from Settings), "What's Included", steps, FAQ accordion |
| `contact.html` | `/contact` | Working form → admin inbox (validated, throttled, honeypot). Office details from Settings |

Campaign, featured-business and gallery sections of the homepage appear automatically once
P07/P09/P11 add their data. They are not shown empty or with fake content.

## Admin screens (all EN/HI, audit-logged, permission `content.manage`)
- **Page Content**: blocks per page/section defined in `config/content.php` (add / edit / hide / order / delete; required blocks can't be deleted)
- **Homepage Banners**: carousel slides (image, title, subtitle, button text + link, order, active, start/end schedule, EN + HI **preview**), activate/deactivate, delete. Left/right panel mode: membership CTA or advertisement (image, link, caption). Donation-campaign modes arrive with P07
- **Committee**: leadership/general, photo, order, visibility
- **Announcements**: event/notice, event date, display window, order
- **Contact Messages**: inbox, search/filter, view, resolve, delete

## Engineering
- `ImageService`: re-encodes uploads (strips EXIF/payloads) to WebP at several widths. `<x-img>` renders `srcset`/`sizes`, width/height (no layout shift), lazy loading, and preloads the first carousel image
- `PublicContent`: all public content cached under one version key, bumped automatically on any save/delete, so there are no content queries per page view and changes appear instantly
- `safe_url()`: admin links only allow site paths or https (no `javascript:`)
- Homepage stats are live counts and hidden while zero (no fake numbers)
- Storage URLs are site-relative (`/storage/...`), so images work on any domain
- Initial page text from the HTML (EN + HI) via `WebsiteContentSeeder` (fills empty sections only). Sample slides, committee and events via `SampleWebsiteSeeder` (dev only)

## Responsive verification
Device emulation (puppeteer + Chrome) at 360/390/768/1024/1280 px for home, about, committee,
membership, contact (EN + HI) and admin banners/content: **0 px horizontal overflow** everywhere.
Fixed during the check: admin phone tables cut off (desktop min-width) and banner panels on tablet now sit side by side.

## Tests
116 passing: every page EN + HI, admin edit → instant public update, Hindi fallback, hidden/
scheduled slides & announcements, unsafe link blocking, contact validation + honeypot, slide image
lifecycle (create → resize → replace → delete files), panel ad, committee CRUD, permissions, XSS escaping.
