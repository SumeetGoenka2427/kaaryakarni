# 03 — Bilingual Content (English / हिन्दी)

Scope: **website text only**: public pages, member area, emails/SMS to members.
PDF receipts, Excel exports and the admin panel chrome stay **English**.
Admin can edit content in both languages.

## Three kinds of text

| Kind | Example | Where stored | Who edits |
|------|---------|--------------|-----------|
| 1. UI labels | "Become a Member", nav items, form labels, validation messages, empty states | `lang/en/*.php`, `lang/hi/*.php` + DB override table `site_texts` | Developer seeds; **admin can override** from Admin → Site Texts |
| 2. Admin-managed content | banner titles, campaign names, About page, committee designations, gallery titles, announcements, areas, categories, SEO meta | Paired columns `*_en` + `*_hi` on each table | Admin, with EN / HI tabs in each form |
| 3. Member-entered data | member name, business description, matrimonial "about" | Single column, shown as entered | Member |

### Why paired columns (`title_en`, `title_hi`) and not JSON

- There are only 2 fixed languages, so there is no schema churn.
- They are indexable and searchable with normal `LIKE` / FULLTEXT; JSON search is weak on MariaDB.
- Queries stay simple and fast, and are clear in reports.

### `HasTranslations` trait

```php
// $campaign->t('title')  → title_hi if locale=hi and not empty, else title_en
public function t(string $field): ?string
```
- `_en` is **required** and `_hi` is optional. If Hindi is empty, the English text shows
  instead, so a page never shows blank text.
- The admin list shows a small **"HI missing"** badge on rows without Hindi, so gaps are visible.

## UI labels: `site_texts` override

- `lang/en|hi/web.php` hold defaults (all strings extracted from the HTML pages; `about.html`
  already has `data-i18n` keys we can reuse).
- Table `site_texts(group, key, value_en, value_hi)`. Admin → **Site Texts** lists every key,
  grouped by page (Header, Footer, Home, Membership, …), with search and inline EN/HI editing.
- A custom translation loader merges DB overrides over the file defaults. The result is
  cached forever and cleared when a site text is saved. There is no DB hit per request.

## Locale in URLs (SEO)

- English: `/membership`, `/donations/diwali-utsav`
- Hindi: `/hi/membership`, `/hi/donations/diwali-utsav`
- `SetLocale` middleware reads the prefix and sets `app()->setLocale()`.
- The EN | हिं toggle (existing `.lang-toggle` design) links to the same page in the other
  language. The choice is remembered in a cookie for the next visit to `/`.
- `<html lang="hi">`, `hreflang` alternate links, localized `<title>` / meta description / OG tags.
- Member area follows the same prefix. The member's preferred language is also stored
  on `members.locale` and used for SMS.

## Admin form UX

Every translatable field renders through one component:
```blade
<x-admin.translatable-field name="title" label="Title" :model="$banner" required />
```
This renders EN / हिन्दी tabs. The tabs share validation, and errors show on the correct tab.
Rich-text fields (About page) use the same pattern with a light editor, sanitized by Purifier.

## Fonts for Devanagari

The current `Georgia` / system stack has poor Hindi rendering. Self-host (woff2, subset):
- Headings: **Tiro Devanagari Hindi** or **Noto Serif Devanagari** (matches the serif heading style)
- Body: **Mukta** or **Noto Sans Devanagari**

They are added to the `:root` font tokens with `font-display: swap`, and only Latin +
Devanagari subsets are loaded (see 04 and 06).

## Notifications

SMS/notification templates have EN and HI versions (`notification_templates` table).
Indian DLT-registered SMS templates must match exactly, so the SMS text is fixed per
template ID and not free-edited (see 07-open-questions).
