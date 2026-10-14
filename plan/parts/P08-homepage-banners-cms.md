# P08 — Homepage, 3-Column Banner & Page Content

**Goal:** a fully data-driven homepage, identical to `web/index.html`, with every text
editable in EN/HI.

## Tasks
### Top banner (3 columns: small left / large center / small right)
- [ ] `banner_slides`, `banner_panels` migrations + sample slides
- [ ] Admin → Banners (existing design):
  - Slides: create/edit/delete/deactivate, image upload (desktop + optional mobile), title/subtitle/CTA label EN/HI, CTA URL, order (drag), active, start/end schedule, **live preview**
  - Left & right panels: mode (campaign progress / compact campaign / recent donors / advertisement), campaign select, toggles for target, %, donor count, recent donors; ad image/URL/label EN/HI
- [ ] Left and right panels use different compact designs (as in the final HTML) and real data from P07
- [ ] Carousel: autoplay, pause on hover/focus, prev/next, dots, keyboard, swipe, `prefers-reduced-motion`. First slide image is preloaded
- [ ] Responsive: center first on mobile, then panels side by side or stacked. No horizontal overflow at 360 px

### Rest of homepage (each section admin-editable)
- [ ] About block (`page_sections`: title/body EN/HI + image)
- [ ] Stats from DB: lifetime members, active areas, approved businesses, total donations (each toggleable). **No hard-coded numbers**
- [ ] Membership CTA (text EN/HI from site texts)
- [ ] Active campaigns (P07), featured businesses (P09), announcements (P11), gallery preview (P11), important links (`page_sections`, EN/HI)
- [ ] Section show/hide + order toggles in Admin → Settings → Homepage

### Static pages
- [ ] Admin → Pages: About (history, mission, vision, values EN/HI), Contact intro, Privacy, Terms. Rich text purified. SEO title/description EN/HI per page
- [ ] `/about`, `/contact` rendered from DB (existing designs)

### Caching
- [ ] Per-locale fragment cache for each homepage section. Observers clear it on save

## Tests
Admin creates, orders, deactivates and schedules slides, and the homepage reflects it.
Panels show DB totals. Hindi homepage shows Hindi content with English fallback.

## Acceptance
Homepage at 360/390/430/768/1024/1280/1440 px matches the final HTML. Nothing on it is hard-coded.
