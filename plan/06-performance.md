# 06 — Performance

Targets on a mid-range mobile over 4G: **LCP < 2.0 s, CLS < 0.05, TTFB < 200 ms (cached)**,
Lighthouse Performance ≥ 90, total JS on public pages < 30 KB gzipped.

## Server
- Production: `config:cache`, `route:cache`, `view:cache`, `event:cache`, `composer install
  --no-dev --optimize-autoloader --classmap-authoritative`, OPcache on (`validate_timestamps=0`).
- **Settings, site texts, branding, active areas, categories** are cached forever as single
  arrays and cleared by model observers on save. There are zero DB queries for these per request.
- **Homepage fragments** (slides, panels, campaigns, featured businesses, gallery preview,
  stats) are cached per locale with `Cache::remember` and cleared by observers. Live donation
  panels use a short 60 s TTL and are also cleared immediately when a payment is verified.
- Donation totals come from the denormalized `raised_amount` / `donor_count` columns (no SUM per view).
- Eager loading everywhere. `Model::preventLazyLoading()` in non-production catches N+1 queries.
- Pagination on all lists (`simplePaginate` for public lists, which avoids COUNT queries).
- Indexes as listed in 02. Check every list query with `EXPLAIN` in P13.
- Slow work goes to the **queue**: SMS, notifications, image variants, receipt PDF, large
  exports (emailed or downloadable when ready). Supervisor/cron `queue:work` in production.
- Exports stream with `spatie/simple-excel` + `lazy()` cursors, so memory stays flat.

## Front end
- Vite builds minified, hashed CSS/JS. Static assets get `Cache-Control: public, max-age=31536000, immutable`.
- One CSS file per area (web/admin), around 25 KB gzipped. No CSS framework.
- JS is `type="module"` and deferred. Page-specific entries keep the homepage light.
- **Images**: every upload produces WebP variants (e.g. 480/960/1600 w) + JPEG fallback, served
  via `<x-img>` with `srcset`/`sizes`, explicit `width`/`height` (no layout shift), `loading="lazy"`
  except the first carousel slide (`fetchpriority="high"`, preloaded).
- Fonts: self-hosted woff2, Latin + Devanagari subsets, `font-display: swap`, one preload.
- Gzip/Brotli on the web server. HTTP/2.
- No third-party scripts except Razorpay checkout, loaded only on payment pages.

## Database
- InnoDB, `utf8mb4`. Composite indexes match the filters (status + category + area).
- FULLTEXT index on business name/description for directory search.
- Scheduled pruning: expired OTPs, old sessions, `sms_logs` > 180 days.

## Checks (in P13)
Lighthouse at 360/1280 px for home, donations, directory, and gallery. Laravel Debugbar
query count < 15 per public page (uncached).
