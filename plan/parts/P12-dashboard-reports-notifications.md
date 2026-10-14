# P12 — Admin Dashboard, Reports, Notifications, Audit Logs

## Tasks
### Dashboard (existing `admin/index.html` design)
- [ ] Cards: total members, pending approvals, lifetime members, pending/approved businesses, pending matrimonial, total donations, per-campaign collections, pending offline donations, online payment status breakdown
- [ ] Recent registrations, donations, approvals. Every card links to its filtered list
- [ ] Queries aggregated in a single cached `DashboardStats` (60 s)

### Reports (existing `admin/reports.html` design)
- [ ] Member, membership, family (optional), business, matrimonial approval, donation, payment and area-wise member reports
- [ ] Date range + relevant filters. On-screen table + **Excel / CSV / PDF** export
- [ ] Streaming exports (simple-excel + `lazy()`). Large ones are queued and downloaded when ready
- [ ] Export permission check + audit log entry

### Notifications
- [ ] Laravel Notifications with `database` + custom `sms` channel (P04 provider)
- [ ] Events: registration, membership payment, membership approval/rejection, business approval/rejection, matrimonial approval/rejection, donation confirmation, offline donation verification, announcements
- [ ] Text EN/HI by member locale
- [ ] `/member/notifications` (existing design): list, mark read, unread badge in header
- [ ] Admin → Notifications (existing design): send announcement to all / lifetime members / an area, optional SMS, history

### Audit logs
- [ ] `Auditable` trait + observer on all admin-managed models. Logs approvals, verifications, exports and logins
- [ ] Admin → Audit Logs (existing design): filter by admin, action, model, date. Old/new value diff view
