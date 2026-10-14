# P04 — Authentication (Admin + Member OTP) & SMS Layer

## Tasks
### Admin
- [ ] `admin_users` migration, `admin` guard, seeded super-admin from `.env`
- [ ] Roles/permissions (Super Admin, Membership Manager, Content Manager, Accounts, Matrimonial Moderator)
- [ ] `/admin/login` (existing design): throttled, lockout, session regenerate, optional TOTP 2FA
- [ ] Admin → Admin Users screen (create, role assign, deactivate, reset password)

### SMS abstraction
- [ ] `SmsProvider` interface, `Msg91Provider`, `LogProvider` (local only, blocked in production)
- [ ] `notification_templates` (EN/HI + DLT id), `sms_logs` (without OTP text)
- [ ] Queued SMS job with retry/backoff

### Member OTP
- [ ] `otp_codes` table + `OtpService` (hashed, 5-min expiry, 5 attempts, 60 s resend cooldown, per-mobile + per-IP limits)
- [ ] `/login` (existing design): mobile → OTP → verify → session → `/member/dashboard`
- [ ] `/register` (existing design): mobile → OTP → create member (status `pending`) → continue profile (P05)
- [ ] OTP input UX: 6 boxes, paste support, countdown, resend, loading/error/success states (EN/HI)
- [ ] Logout. `member` guard. Middleware `auth:member`, `member.active`
- [ ] Suspended members are blocked with a clear message

## Tests
Valid, invalid and expired OTP, resend cooldown, rate limit (429), max attempts, login/logout,
admin lockout, member cannot access `/admin`, guest is redirected from `/member/*`.

## Acceptance
Full login works locally with the OTP from `storage/logs/otp.log`. All tests above pass.
