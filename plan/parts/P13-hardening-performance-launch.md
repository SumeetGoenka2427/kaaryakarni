# P13 — Security Pass, Performance Pass, Testing & Launch

## Security (check against [05](../05-security.md))
- [ ] Route audit: every admin route has a permission and every member route has a policy
- [ ] ID manipulation tests on all member-owned resources (profile, family, business, matrimonial, payments, receipts)
- [ ] Upload tests: PHP file disguised as JPG, oversized file, SVG with script
- [ ] Webhook forgery + replay tests
- [ ] Security headers/CSP verified, `APP_DEBUG=false`, `.env` not web-accessible, storage not executable
- [ ] `composer audit`, `npm audit`

## Performance (check against [06](../06-performance.md))
- [ ] Lighthouse ≥ 90 on home, donations, directory, gallery (mobile + desktop)
- [ ] Debugbar: no N+1, < 15 queries per uncached public page
- [ ] `EXPLAIN` on directory/admin list queries
- [ ] Production cache commands, OPcache, gzip/brotli, immutable asset caching

## Content & UI acceptance
- [ ] Walk through the CLAUDE.md §37 checklist on rendered pages
- [ ] Visual match with the final HTML at 360/390/430/768/1024/1280/1440 px, EN and HI
- [ ] Search the rendered output for: "across India", "City", "State", "renew", "expiry", lorem → none found
- [ ] Every screen has loading, empty, error, 403 and 404 states
- [ ] Accessibility: keyboard-only run, focus visible, labels, alt text, contrast, reduced motion

## Launch
- [ ] Production `.env`, Razorpay live keys + webhook URL, MSG91 DLT templates, SSL
- [ ] Cron `schedule:run` + queue worker (Supervisor or cron fallback)
- [ ] Run `ProductionSeeder` only (no demo data). Admin replaces sample areas with the client's list
- [ ] Backups configured. README with deploy and rollback steps
- [ ] Sitemap.xml (public pages, EN + HI), robots.txt (block `/member`, `/admin`)
