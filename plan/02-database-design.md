# 02 — Database Design

Conventions:
- `utf8mb4` / `utf8mb4_unicode_ci` everywhere (required for Hindi).
- `BIGINT UNSIGNED` auto-increment PKs internally. **Public URLs never expose sequential IDs**:
  businesses and campaigns use `slug`, and member-facing records use a `ulid` column
  (this blocks ID guessing).
- Money is `DECIMAL(12,2)`. Never float.
- Status columns are `VARCHAR(30)` backed by PHP enums, indexed.
- `created_at` / `updated_at` on all tables. Soft deletes only where admin "delete" must be
  recoverable (members, businesses, matrimonial, campaigns, albums).
- FKs with explicit `ON DELETE` (restrict for financial data, cascade for child media).
- `*_en` / `*_hi` = translatable admin content (see 03).

## Tables

### Settings, branding & content
| Table | Key columns | Notes |
|-------|-------------|-------|
| `settings` | `key` (unique), `value` (json), `group` | Org name EN/HI, contact info, fee, donor-name visibility, business re-approval toggle, social links. Cached as one array. |
| `branding_assets` | `type` (unique: `logo_header`, `logo_footer`, `logo_admin`, `logo_receipt`, `favicon`, `og_image`), `path`, `width`, `height`, `version` | Logo manager. `version` busts cache. |
| `site_texts` | `group`, `key`, `value_en`, `value_hi` — unique(`group`,`key`) | UI label overrides |
| `pages` | `slug` unique, `title_en/hi`, `body_en/hi` (longtext, purified), `meta_title_en/hi`, `meta_description_en/hi`, `is_active` | About, Contact intro, privacy, terms |
| `page_sections` | `page_id`, `key`, `title_en/hi`, `body_en/hi`, `image_path`, `sort_order`, `is_active` | Homepage "About" block, mission/vision/values, important links |
| `announcements` | `title_en/hi`, `body_en/hi`, `type` (event/notice), `event_date`, `starts_at`, `ends_at`, `is_active`, `sort_order` | idx(`is_active`,`starts_at`) |
| `committee_members` | `name_en/hi`, `designation_en/hi`, `tier` (leadership/general), `photo_path`, `sort_order`, `is_active` | |
| `contact_messages` | `name`, `mobile`, `email`, `subject`, `message`, `status`, `ip` | Contact form inbox |

### Users & auth
| Table | Key columns | Notes |
|-------|-------------|-------|
| `admin_users` | `name`, `username` unique, `email` unique, `password` (bcrypt), `is_active`, `last_login_at`, `two_factor_secret` (nullable) | + spatie `roles`, `permissions`, pivot tables |
| `members` | `ulid` unique, `membership_no` unique nullable (`KSJ-2026-0001`), `name`, `mobile` unique, `email`, `dob`, `gender`, `address`, `area_id` FK, `photo_path`, `father_or_husband_name`, `status` (enum), `locale`, `verified_at`, `approved_at`, `suspended_at`, `last_login_at`, soft deletes | idx(`status`), idx(`area_id`), idx(`created_at`). **No city/state, no expiry columns.** |
| `family_members` | `ulid`, `member_id` FK cascade, `relation` (spouse/son/daughter/father/mother/brother/sister/daughter_in_law/son_in_law/grandson/granddaughter/other), `name`, `gender`, `dob`, `marital_status`, `education`, `occupation`, `mobile` nullable, `email` nullable, `blood_group`, `photo_path` nullable, `is_deceased`, `lives_with_member` (bool), `notes`, `sort_order`, soft deletes | idx(`member_id`). Owned and edited by the member. Admin can view and edit. **Private**: never shown publicly. Optional link `matrimonial_profiles.family_member_id` so a son's or daughter's matrimonial profile can be created from this record. |
| `member_verifications` | `member_id`, `action` (submitted/verified/rejected/suspended/reinstated), `reason`, `admin_user_id`, `created_at` | Full verification history |
| `otp_codes` | `mobile`, `code_hash`, `purpose` (login/register), `attempts`, `expires_at`, `consumed_at`, `ip` | idx(`mobile`,`purpose`). Code stored **hashed**. Pruned daily. |
| `sessions` | Laravel default | |

### Areas & categories
| Table | Key columns |
|-------|-------------|
| `areas` | `name_en`, `name_hi`, `is_active`, `sort_order`, `is_sample` (bool, marks demo areas) |
| `business_categories` | `name_en`, `name_hi`, `slug`, `is_active`, `sort_order` |

### Membership & payments
| Table | Key columns | Notes |
|-------|-------------|-------|
| `memberships` | `member_id` **unique** FK, `fee_amount`, `status` (pending/payment_pending/payment_submitted/under_verification/active/rejected/suspended), `payment_id` FK nullable, `approved_at`, `approved_by` | One row per member and lifetime. **No renewal or expiry columns exist at all.** |
| `payments` | `ulid`, `member_id` nullable FK, `purpose` (membership/donation/ticket), `payable_type`+`payable_id` (morph → membership/donation), `amount`, `currency`, `mode` (online/offline), `provider` (razorpay/offline), `provider_order_id` unique nullable, `provider_payment_id` unique nullable, `offline_method` (cash/cheque/upi/neft), `offline_reference`, `proof_path` (private disk), `status` (created/pending/submitted/successful/verified/failed/refunded/cancelled), `verified_by`, `verified_at`, `paid_at`, `raw_response` (json) | idx(`status`,`mode`), idx(`purpose`), idx(`paid_at`). The only source of truth for money. |
| `payment_events` | `payment_id`, `event`, `payload` json, `signature_valid`, `created_at` | Webhook log, idempotency |
| `receipts` | `payment_id` unique FK, `receipt_no` unique (`KSJ/25-26/000123`, per financial year), `issued_at`, `pdf_path` nullable | Generated once the payment is successful or verified |

### Donations
| Table | Key columns | Notes |
|-------|-------------|-------|
| `donation_campaigns` | `slug` unique, `title_en/hi`, `description_en/hi`, `image_path`, `target_amount` nullable, `raised_amount` (cached), `donor_count` (cached), `is_permanent`, `is_active`, `allow_online`, `allow_offline`, `has_tickets`, `ticket_label_en/hi`, `ticket_price`, `starts_at`, `ends_at`, `sort_order`, soft deletes | `is_permanent=1` row ("Samaj Donation") is blocked from delete/deactivate by Policy **and** a model `deleting` guard |
| `donations` | `ulid`, `campaign_id` FK restrict, `member_id` nullable, `donor_name`, `donor_mobile`, `donor_email`, `donor_pan` nullable, `type` (donation/ticket), `quantity`, `amount`, `is_anonymous`, `show_name_publicly`, `payment_id` FK, `status`, `message` | idx(`campaign_id`,`status`), idx(`created_at`) |

`raised_amount` and `donor_count` are **denormalized counters**. They are updated in the
same DB transaction that marks a payment successful/verified (or refunded). This keeps
homepage live widgets to a single-row read. `php artisan donations:recalculate` rebuilds
them from `donations` to check they are correct.

### Homepage banner
| Table | Key columns |
|-------|-------------|
| `banner_slides` | `title_en/hi`, `subtitle_en/hi`, `cta_label_en/hi`, `cta_url`, `image_path` (desktop), `image_mobile_path`, `sort_order`, `is_active`, `starts_at`, `ends_at` — idx(`is_active`,`sort_order`) |
| `banner_panels` | `position` unique (left/right), `mode` (campaign_progress / campaign_compact / recent_donors / advertisement), `campaign_id` nullable, `show_target`, `show_donor_count`, `show_recent_donors`, `show_percentage`, `ad_image_path`, `ad_url`, `ad_label_en/hi`, `is_active` |

### Business directory
| Table | Key columns |
|-------|-------------|
| `business_profiles` | `slug` unique, `member_id` FK, `category_id` FK, `area_id` FK, `name`, `description`, `address`, `phone`, `whatsapp`, `email`, `website`, `social` json, `hours` json, `logo_path`, `cover_path`, `show_phone`, `show_email`, `status` (draft/pending/approved/rejected/changes_requested/hidden), `is_featured`, `rejection_reason`, `approved_at`, `approved_by`, soft deletes — idx(`status`,`category_id`,`area_id`), FULLTEXT(`name`,`description`) |

### Matrimonial
| Table | Key columns |
|-------|-------------|
| `matrimonial_profiles` | `ulid`, `member_id` FK, `family_member_id` nullable FK (set null), `relation` (self/son/daughter/brother/sister/other), `name`, `gender`, `dob`, `height_cm`, `father_name`, `mother_name`, `native_place`, `area_id`, `gotra`, `education`, `occupation`, `annual_income_range`, `manglik`, `about`, `bio_data_path` (private), `show_contact`, `show_photos`, `status` (draft/pending/approved/rejected/hidden/suspended), `rejection_reason`, `approved_at`, `approved_by`, soft deletes — idx(`status`,`gender`,`dob`) |
| `matrimonial_photos` | `profile_id` FK cascade, `path` (**private disk**), `sort_order` |
| `matrimonial_reports` | `profile_id`, `reported_by_member_id`, `reason`, `status`, `resolved_by` |

### Gallery
| Table | Key columns |
|-------|-------------|
| `gallery_albums` | `slug`, `title_en/hi`, `description_en/hi`, `cover_image_id` nullable, `event_date`, `is_active`, `sort_order`, soft deletes |
| `gallery_images` | `album_id` FK cascade, `path`, `thumb_path`, `width`, `height`, `caption_en/hi`, `sort_order` |

### Notifications & audit
| Table | Key columns |
|-------|-------------|
| `notifications` | Laravel default (`notifiable` morph, `data` json, `read_at`) — member in-app inbox |
| `admin_announcements` | `title_en/hi`, `message_en/hi`, `audience` (all/lifetime/area), `area_id`, `send_sms`, `sent_at`, `sent_by` |
| `notification_templates` | `key` unique, `sms_en`, `sms_hi`, `dlt_template_id`, `is_active` |
| `sms_logs` | `mobile`, `template_key`, `provider`, `status`, `provider_message_id`, `error` — **never stores OTP text** |
| `audit_logs` | `admin_user_id`, `action`, `auditable_type`+`auditable_id`, `old_values` json, `new_values` json, `ip`, `user_agent`, `created_at` — idx(`auditable_type`,`auditable_id`), idx(`created_at`) |
| `jobs`, `failed_jobs`, `cache`, `cache_locks` | Laravel defaults |

## Relationship overview

```
areas ─┬─< members ─┬─< family_members
       │            ├─1 memberships ──> payments ──1 receipts
       │            ├─< business_profiles >── business_categories
       │            ├─< matrimonial_profiles ─< matrimonial_photos
       │            └─< donations >── donation_campaigns
       └─< business_profiles            donations ──> payments
gallery_albums ─< gallery_images
banner_panels ──> donation_campaigns
```

## Seeders

- `ProductionSeeder`: super-admin (from `.env`), roles/permissions, **Samaj Donation**
  permanent campaign, default settings, default site texts, notification templates, default
  banner panels. It is safe to run on production and is idempotent.
- `DemoSeeder`: 20 sample Jaipur areas (`is_sample=1`), members, businesses, matrimonial,
  campaigns, donations, gallery, slides, committee. These mirror the current `store.js` data.
  Every demo row is flagged, and `php artisan demo:purge` removes it all.
