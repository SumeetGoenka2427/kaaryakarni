# P12 — Admin Dashboard, Reports, Notifications, Audit Logs ✅ Done

## Dashboard (design `admin/index.html`)
- [x] 8 live cards: total members, lifetime members, pending member verification, approved businesses, pending business approvals, pending matrimonial approvals, total donations collected, offline donations to verify. Each card links to its filtered list; pending numbers are highlighted
- [x] Recent members, recent donations, pending approvals (with "All clear"), recent admin actions (logins left out), current campaign collections with progress, online payment status for the last 30 days
- [x] Figures aggregated in `DashboardStats` (cached 60 s). Each panel only shows when the admin has the matching permission

## Reports (design `admin/reports.html`)
- [x] `ReportService`: Member, Membership (count by status + fees), Business, Matrimonial approval (no contact details), Donation, Payment, Area-wise members
- [x] Date range filter, on-screen preview (first 25 rows + total), **Excel / CSV / PDF** downloads. CSV/Excel are streamed row by row; CSV has a BOM so Hindi shows in Excel. PDF is A4 landscape, up to 2,000 rows
- [x] `reports.view` to preview, `reports.export` to download; every export is audit-logged

## Notifications
- [x] `Notifier` service: every system message (registration, verification, membership, payments, business, matrimonial) goes to the member's in-app inbox in EN + HI, and by SMS through `SmsService`. OTP SMS do not pass through it
- [x] `/member/notifications` (design `web/member/notifications.html`): list, "New" badge, open (marks read and goes to the related page), mark all as read. Bell with an unread count in the member header, plus a badge in the "More" menu
- [x] Admin → Notifications (design `admin/notifications.html`): new announcement (EN/HI title + message, type) to all members / lifetime members / one area, optional SMS (`announcement` template); announcement history with recipient count; delete removes it from inboxes; list of member-specific notifications with search
- [x] Suspended and rejected accounts never receive announcements

## Audit logs (design `admin/audit-logs.html`)
- [x] Admin → Audit Logs: search, filter by admin, area of the portal, date range; old → new values for each change; read-only
- [x] Approvals, verifications, exports, announcements, content changes and logins are recorded by the module services (`AuditLogger`)

## Tests (`tests/Feature/Admin/DashboardReportsNotificationsTest.php`, 10 tests)
Dashboard counts and links; permission-based panels; report preview + CSV/Excel/PDF with date range; preview-only vs export permission;
matrimonial report has no contact data; member inbox in Hindi + mark read; a member cannot open someone else's notification;
announcement audiences + SMS + delete; announcement permission; audit log filters and permission.
