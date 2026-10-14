# P04 — Authentication (Admin + Member OTP) & SMS Layer ✅ Done

## Tasks
### Admin
- [x] Guard, roles, login, lockout, idle timeout (done early in P03)
- [x] Admin → **Admin Users**: list/search, create (role + strong password), edit, reset password, activate/deactivate. Safeguards: no self-demotion/deactivation, at least one active Super Admin, usernames immutable. Audit-logged (passwords never logged)
- [ ] Optional TOTP 2FA → deferred to **P13** (hardening) unless the client wants it sooner

### SMS abstraction
- [x] `SmsProvider` interface + `Msg91SmsProvider` (DLT flow API) + `LogSmsProvider` (local only, writes `storage/logs/otp.log`, blocked in production)
- [x] `notification_templates` (EN/HI + DLT template id), seeded: `otp`, `registration_received`
- [x] `sms_logs` delivery records **without message text**
- [x] `SmsService::sendNow()` for OTPs (never queued, so codes never land in the jobs table) and `queue()` + `SendSms` job (3 tries, backoff) for everything else

### Member OTP
- [x] `areas` + `members` tables created here (registration needs the area list). Area admin screen is P05
- [x] `OtpService`: `random_int` codes, **hashed** storage, 5-min expiry, single use, 5 attempts, 60 s resend cooldown, 5/hour per mobile, 20/hour per IP, new code invalidates the old one, daily prune (scheduler)
- [x] One flow for `/login` and `/register` (+ `/hi/...`): mobile → OTP → existing member logged in, or new number → registration details. **Never reveals** whether a number is registered
- [x] Registration details (from `register.html` step 3): name, father/husband name, DOB, gender, email, **Area / Locality (active admin areas only)**, address, consent. **No city/state.** The mobile number comes only from the verified session, never from the form. Status → Under Verification, SMS queued
- [x] OTP UI: 6 boxes, paste a full code, `autocomplete="one-time-code"`, resend countdown, change number, EN/HI messages for every error
- [x] `member` guard (separate from admin), `member.active` signs out suspended/removed members, logout
- [x] Member dashboard (from `member/dashboard.html`): verification status, area, mobile, status notices. Module cards are added as modules go live
- [x] Shared pagination template in the site style (EN/HI)
- [x] 20 sample Jaipur areas (EN + HI, flagged `is_sample`) in `SampleAreasSeeder`

## Tests (80 passing in total)
Valid / invalid / expired / single-use OTP, max attempts, resend cooldown + old code invalidated,
per-mobile rate limit, SMS gateway failure, no enumeration, full registration with area,
mobile taken from session only (form `mobile`/`status` ignored), inactive area rejected,
suspended member blocked (at login and mid-session), guest redirects (EN/HI), member ≠ admin
session, logout, admin users CRUD + safeguards + permission 403.

## Note
The new login/register pages use the full site header/footer. The original `login.html` used a
slimmer frame (no top bar, short footer). This is easy to switch if preferred.
