# CLAUDE.md --- Khandelwal Samaj Membership Portal

## 1. Project Objective

Build a complete, production-ready, responsive **Khandelwal Samaj
Membership Portal** for a Jaipur-based Samaj organization.

The existing screenshot/reference UI is only a visual starting point. Do
not copy its wording or limitations blindly. Improve the design into a
polished **Samaj/community website** with a trustworthy Indian
community-organization feel, strong accessibility, responsive behavior,
and a complete member/admin workflow.

The project must be implemented as a **working website**, not a static
mockup.

The final system must support:

-   Public Samaj website pages
-   Member registration/login using mobile OTP
-   Member profile management and verification
-   Lifetime membership management
-   Business/professional directory with admin approval
-   Private matrimonial module with admin approval
-   Online and offline payments
-   Donation campaigns
-   Live donation information on the homepage
-   Gallery management
-   Admin-controlled homepage banners/carousels
-   Admin dashboard and management screens
-   Reports, Excel exports and PDF receipts
-   Notifications
-   Protected member/private data
-   Responsive desktop/tablet/mobile UI
-   Proper routing/navigation
-   Database-backed data

------------------------------------------------------------------------

# 2. Important Corrections From the Existing Design

The current screenshot contains copy and concepts that must be changed.

## Remove

Do NOT use:

-   "Join thousands of members across India."
-   "Browse and connect with Kaaryakarni members across India."
-   India-wide member discovery language
-   City field
-   State field
-   City/state filters
-   Membership renewal workflows
-   Any implication that membership expires
-   Any implication that membership requires recurring renewal

## Replace With

The organization is focused on **Jaipur**.

The client will provide approximately **20 Jaipur area/locality names**.
These must be configurable and used during user/member creation.

Do not hard-code city/state fields into the member form.

Use a configurable field such as:

-   Area / Locality

The admin must be able to manage the available Jaipur areas from the
admin panel.

Example areas can be seeded for development, but clearly mark them as
sample data until the client provides the final list.

## Membership Rule

Membership is a **one-time lifetime membership**.

There is:

-   No membership renewal
-   No recurring membership fee
-   No expiry date
-   No renewal reminder
-   No renewal button

After successful membership approval/payment, the member status should
remain **Lifetime Member** unless an admin explicitly changes/suspends
the account.

------------------------------------------------------------------------

# 3. Recommended Product/Brand Direction

The website should feel like a genuine **Khandelwal Samaj community
portal**, not a generic SaaS dashboard.

Design direction:

-   Premium but warm
-   Traditional Indian/community identity combined with modern UX
-   Trustworthy
-   Family/community oriented
-   Clean typography
-   Strong navy/royal blue foundation
-   Gold/saffron accents
-   White and light neutral backgrounds
-   Subtle Indian cultural motifs where appropriate
-   Avoid excessive gradients, glassmorphism and startup-style visuals
-   Avoid making every section look like a software dashboard

Use visual hierarchy suitable for a Samaj organization.

The public website and admin panel should have **different visual
treatments**:

### Public website

Community-focused, welcoming, polished, content-rich.

### Admin panel

Functional, information-dense, professional, easy to manage.

------------------------------------------------------------------------

# 4. Homepage Requirements

Create a complete homepage with sections such as:

1.  Header/navigation
2.  Top promotional/banner area
3.  About Samaj
4.  Key statistics
5.  Membership call-to-action
6.  Donation section
7.  Donation campaigns
8.  Community/business highlights
9.  Upcoming events/announcements if enabled
10. Gallery preview
11. Important links
12. Footer

Sections may be adjusted based on UX, but the homepage must not feel
empty.

------------------------------------------------------------------------

# 5. Homepage Top Banner --- Critical Requirement

The top banner must be **fully managed from Admin**.

It must be divided into **3 sections/columns**:

``` text
┌────────────────────┬──────────────────────────────────────────┬────────────────────┐
│                    │                                          │                    │
│  LEFT              │              CENTER                      │       RIGHT        │
│  Small             │              Larger                      │       Small        │
│                    │                                          │                    │
│  Live Donation     │       Admin-controlled Carousel          │   Live Donation    │
│  / Donation Info   │       / Promotional Slides               │   / Donation Info  │
│                    │                                          │                    │
└────────────────────┴──────────────────────────────────────────┴────────────────────┘
```

### Relative sizing

On desktop:

-   Left: approximately 20--25%
-   Center: approximately 50--60%
-   Right: approximately 20--25%

The center area must visually dominate.

On tablet/mobile, stack/reflow these sections intelligently. Do not
allow horizontal overflow.

## Left section

Show live donation information.

Examples:

-   Donation campaign
-   Amount collected
-   Target amount
-   Recent donation activity
-   CTA such as "Donate Now"

This section should be data-driven.

## Center section

Admin controls the carousel.

Admin must be able to:

-   Create slide
-   Edit slide
-   Delete/deactivate slide
-   Upload image
-   Add title
-   Add subtitle/description
-   Add CTA label
-   Add CTA URL/action
-   Set display order
-   Enable/disable
-   Set start/end date if required
-   Preview slide

Support multiple slides.

The homepage should automatically rotate through active slides.

Provide:

-   Previous/next controls
-   Pagination indicators
-   Auto-play
-   Pause on hover/focus
-   Keyboard accessibility
-   Touch/swipe support on mobile

## Right section

Show live donation information, similar to the left but with a different
compact presentation so the two sides do not look like duplicated cards.

Possible content:

-   Current campaign
-   Live progress
-   Donor count
-   Amount collected
-   Quick Donate button

All donation values must come from real application data.

Do not use permanently hard-coded fake amounts in production UI.

------------------------------------------------------------------------

# 6. Public Navigation

Suggested navigation:

-   Home
-   About Samaj
-   Membership
-   Business Directory
-   Gallery
-   Donations
-   Contact
-   Login

Authenticated members may additionally see:

-   My Profile
-   My Membership
-   Business Profile
-   Matrimonial
-   My Payments/Receipts
-   Notifications
-   Logout

Do not show the Matrimonial module as a public searchable directory.

------------------------------------------------------------------------

# 7. Authentication

Implement secure member authentication using:

## Registration

Mobile number is the primary authentication identifier.

Flow:

1.  Enter mobile number
2.  Validate number
3.  Send OTP
4.  Verify OTP
5.  Create/continue member profile
6.  Select area/locality
7.  Complete required profile information
8.  Submit for verification if applicable

## Login

1.  Enter mobile number
2.  Request OTP
3.  Verify OTP
4.  Create authenticated session
5.  Redirect to member dashboard

Include:

-   OTP expiry
-   Resend cooldown
-   Rate limiting
-   Maximum verification attempts
-   Proper error states
-   Loading states
-   Success states

Never expose OTP in production UI/logs.

For local development, provide a clearly isolated development/mock OTP
mechanism only if the actual SMS provider is not configured.

------------------------------------------------------------------------

# 8. Member Profile

Member profile must support:

-   Full name
-   Mobile number
-   Email, if applicable
-   Date of birth, if required by the client
-   Gender, if required
-   Address information appropriate to the project
-   Area/locality
-   Profile photo
-   Family/basic community information if required
-   Membership details
-   Verification status

Do not include City or State fields.

Area/locality must be selected from the admin-managed Jaipur area list.

Member can:

-   View profile
-   Edit allowed fields
-   Upload/update profile image
-   View verification status
-   View membership status
-   View receipts/payments

Admin can:

-   View
-   Edit
-   Verify
-   Reject with reason
-   Suspend/deactivate where appropriate

------------------------------------------------------------------------

# 9. Membership Module

Membership is **lifetime / one-time**.

Statuses should include appropriate states such as:

-   Pending
-   Payment Pending
-   Payment Submitted
-   Under Verification
-   Active / Lifetime Member
-   Rejected
-   Suspended

A successful lifetime member record should show:

-   Membership ID
-   Member name
-   Joining/approval date
-   Membership status: Lifetime
-   Payment/receipt information

Do not implement:

-   Renewal dates
-   Expiration dates
-   Renewal subscriptions
-   Recurring membership billing

The membership page should clearly communicate:

> One-time membership with lifetime validity.

The actual membership fee should be configurable by admin.

------------------------------------------------------------------------

# 10. Business Directory

Members can create a business/professional profile.

Fields can include:

-   Business/professional name
-   Category
-   Description
-   Owner/member
-   Contact details according to privacy settings
-   Area/locality
-   Address
-   Website/social links if required
-   Logo/business image
-   Business hours if applicable

Workflow:

``` text
Member submits business
        ↓
Pending Admin Approval
        ↓
Admin reviews
   ↓          ↓
Approve     Reject
   ↓
Published in Business Directory
```

Only **approved** business profiles appear publicly.

Members must be able to edit their business profile.

After important edits, optionally return the profile to pending approval
based on admin configuration.

Admin can:

-   Approve
-   Reject
-   Request changes
-   Edit
-   Hide
-   Delete
-   Feature/highlight a business

Business directory should support:

-   Search
-   Category filter
-   Area filter
-   Pagination
-   Business detail page

Do not expose unnecessary private member information.

------------------------------------------------------------------------

# 11. Matrimonial Module

Matrimonial profiles are **private**.

Public visitors must not be able to browse matrimonial profiles.

Access requires:

-   Member login
-   Appropriate member permissions/status

Features:

-   Create matrimonial profile
-   Edit profile
-   Submit for admin approval
-   Admin approve/reject
-   Search
-   Filters
-   Profile details
-   Privacy controls
-   Report/flag profile if required

Admin can:

-   Review
-   Approve
-   Reject
-   Hide
-   Edit
-   Suspend
-   Delete

Only approved profiles should appear to eligible logged-in members.

Design this area with additional privacy messaging.

Never display sensitive personal data unnecessarily.

------------------------------------------------------------------------

# 12. Donations

Donation functionality must support **multiple campaigns**.

## Permanent campaign

There must always be a default/permanent:

> Samaj Donation

This campaign should always exist and should not be removable from the
admin UI.

## Additional campaigns

Admin can create campaigns such as:

-   Diwali Utsav Donation
-   Community Event Donation
-   Education Support
-   Medical Assistance
-   Any future Samaj campaign

Admin can:

-   Create campaign
-   Edit campaign
-   Activate/deactivate campaign
-   Set title
-   Description
-   Target amount
-   Campaign image
-   Start/end date
-   Display order
-   Payment options
-   View donations

## Donation payments

Support:

-   Online payment
-   Offline payment

Offline donation should have a proper workflow.

Example:

``` text
Member/User chooses Offline Donation
        ↓
Enter amount/details
        ↓
Submit offline donation request
        ↓
Admin verifies payment
        ↓
Donation marked verified/received
        ↓
Receipt generated
```

Admin must be able to distinguish:

-   Online
-   Offline

and statuses such as:

-   Pending
-   Successful
-   Failed
-   Submitted
-   Verified
-   Refunded/cancelled where applicable

------------------------------------------------------------------------

# 13. Live Donation Display

Homepage donation widgets must be data-driven.

Show information such as:

-   Campaign name
-   Amount collected
-   Target
-   Progress percentage
-   Number of donors if available
-   Recent donation information where privacy permits

Use real database values.

Avoid exposing full donor information without consent.

Admin should be able to configure whether donor names are publicly
displayed.

------------------------------------------------------------------------

# 14. Gallery Management

Add a complete Gallery module.

## Admin

Admin can:

-   Create gallery album
-   Add title
-   Add description
-   Upload multiple images
-   Reorder images
-   Edit title
-   Edit description
-   Activate/deactivate
-   Delete album
-   Delete individual images
-   Set cover image

Each gallery item/album should have a title.

## Website

Show gallery on:

-   Homepage preview
-   Dedicated Gallery page

Gallery should support:

-   Responsive grid
-   Image preview/lightbox
-   Album title
-   Optional description
-   Mobile-friendly layout

Use lazy loading and optimized images.

------------------------------------------------------------------------

# 15. Admin Panel

Build a real admin panel, not placeholder cards.

Admin navigation should include:

### Dashboard

Show:

-   Total members
-   Pending member approvals
-   Lifetime members
-   Pending businesses
-   Approved businesses
-   Pending matrimonial profiles
-   Total donations
-   Current campaign collections
-   Pending offline donations
-   Online payment status
-   Recent registrations
-   Recent donations
-   Recent approvals

### Member Management

-   List members
-   Search
-   Filter
-   View details
-   Edit
-   Verify
-   Reject
-   Suspend
-   Export
-   Membership information

### Jaipur Area Management

Admin can:

-   Add area
-   Edit area
-   Activate/deactivate area
-   Reorder areas

These areas populate the member creation form.

### Membership Management

-   Membership settings
-   Membership fee
-   Membership records
-   Payment status
-   Lifetime status
-   Receipts
-   Verification

### Business Management

-   Pending
-   Approved
-   Rejected
-   Hidden
-   Featured
-   Search/filter
-   Approve/reject/edit/delete

### Matrimonial Management

-   Pending
-   Approved
-   Rejected
-   Hidden
-   Search/filter
-   Review profile
-   Privacy controls

### Donation Management

-   Campaign list
-   Permanent Samaj Donation
-   Additional campaigns
-   Create campaign
-   Edit campaign
-   Donation records
-   Online/offline filters
-   Verify offline donations
-   Refund/cancel where supported
-   Export

### Homepage Banner Management

Admin must manage all 3 top banner columns.

For center carousel:

-   Slides
-   Images
-   Titles
-   Descriptions
-   CTA
-   Order
-   Active/inactive
-   Scheduling
-   Preview

For left/right live donation panels:

-   Configure display mode
-   Select campaign
-   Configure displayed metrics
-   Enable/disable supported elements

### Gallery Management

-   Albums
-   Titles
-   Images
-   Ordering
-   Visibility

### Reports

Support:

-   Member report
-   Membership report
-   Business report
-   Matrimonial approval report
-   Donation report
-   Payment report
-   Area-wise member report
-   Date-range filters

Exports:

-   Excel
-   CSV where useful
-   PDF

### Receipts/Invoicing

Generate downloadable PDF receipts for:

-   Membership payment
-   Donation payment
-   Offline donation after verification

Receipt should contain:

-   Samaj name/logo
-   Receipt number
-   Date
-   Member/donor name
-   Amount
-   Payment mode
-   Campaign/membership purpose
-   Transaction/reference number
-   Authorized organization information

------------------------------------------------------------------------

# 16. Notifications

Implement notification architecture for:

-   OTP
-   Registration confirmation
-   Membership payment confirmation
-   Membership approval/rejection
-   Business approval/rejection
-   Matrimonial approval/rejection
-   Donation payment confirmation
-   Offline donation verification
-   Important admin announcements

Support SMS integration through a provider abstraction.

Do not hard-code provider-specific code throughout the application.

Use a notification/service layer.

------------------------------------------------------------------------

# 17. Security

Security is mandatory.

Implement:

-   Secure authentication
-   Server-side authorization
-   Role-based access control
-   Admin-only routes
-   Member-only routes
-   Matrimonial access protection
-   Input validation
-   Output escaping
-   Secure file upload validation
-   File type restrictions
-   File size limits
-   Rate limiting for OTP
-   Session security
-   CSRF protection where applicable
-   Secure password handling if passwords are ever introduced
-   Payment webhook verification
-   No sensitive data in client-side source
-   No secret/API keys committed to repository
-   Environment variables for secrets
-   Audit logging for important admin actions

Never trust frontend-only approval/status values.

------------------------------------------------------------------------

# 18. Payment Architecture

Payment provider should be implemented behind a service abstraction.

Support:

``` text
Payment
├── Online
│   ├── Membership
│   └── Donation
└── Offline
    ├── Membership
    └── Donation
```

Payment records should include:

-   Internal payment ID
-   User/member ID
-   Purpose
-   Campaign ID if donation
-   Amount
-   Mode
-   Provider
-   Provider transaction/reference ID
-   Status
-   Created date
-   Updated date

Never mark an online payment successful based only on frontend
redirect/query parameters.

Verify payment using the payment provider's server-side
mechanism/webhook.

------------------------------------------------------------------------

# 19. Data Model

Create a clean relational data model or equivalent structured
persistence layer.

At minimum, model:

-   AdminUser
-   Member
-   MemberVerification
-   Area
-   Membership
-   BusinessProfile
-   BusinessCategory
-   MatrimonialProfile
-   DonationCampaign
-   Donation
-   Payment
-   GalleryAlbum
-   GalleryImage
-   HomepageBanner
-   Notification
-   Receipt
-   AuditLog

Use foreign keys/relations where supported.

Do not duplicate critical data unnecessarily.

------------------------------------------------------------------------

# 20. Homepage Content Management

Where practical, admin should be able to manage homepage content without
editing source code.

Admin-controlled content should include:

-   Top banners
-   Donation campaigns
-   Gallery
-   Important announcements
-   Featured businesses if implemented
-   Homepage statistics where appropriate

Avoid creating a huge generic CMS unnecessarily. Build focused
management screens for actual project requirements.

------------------------------------------------------------------------

# 21. Responsive Design

The entire website must be responsive.

Target:

-   Desktop
-   Laptop
-   Tablet
-   Mobile

Mobile requirements:

-   Hamburger navigation
-   No horizontal scrolling
-   Touch-friendly controls
-   Responsive tables converted to cards where needed
-   Responsive forms
-   Responsive admin sidebar
-   Bottom actions where appropriate
-   Carousels support touch swipe
-   Images maintain correct aspect ratio

Test common widths including approximately:

-   360px
-   390px
-   430px
-   768px
-   1024px
-   1280px
-   1440px

------------------------------------------------------------------------

# 22. Accessibility

Implement:

-   Semantic HTML
-   Proper heading hierarchy
-   Labels for form fields
-   Keyboard navigation
-   Visible focus states
-   Accessible dialogs
-   Alt text
-   Sufficient contrast
-   Reduced-motion support where possible
-   Screen-reader-friendly buttons/status messages

Do not use emoji as the only visual meaning for important functionality.

------------------------------------------------------------------------

# 23. Visual Design Requirements

Use a consistent design system.

Suggested palette:

-   Primary: deep royal/navy blue
-   Secondary: gold
-   Accent: saffron/warm yellow
-   Background: white/off-white
-   Text: dark charcoal/navy

Do not overuse gold.

Cards should have:

-   Clear hierarchy
-   Moderate radius
-   Subtle shadow/border
-   Consistent spacing

Buttons should have:

-   Clear primary/secondary variants
-   Hover
-   Focus
-   Disabled
-   Loading states

Forms should have:

-   Labels
-   Helper text
-   Validation messages
-   Required indicators
-   Success/error feedback

------------------------------------------------------------------------

# 24. Public Pages

Create at minimum:

-   `/`
-   `/about`
-   `/membership`
-   `/login`
-   `/register`
-   `/business-directory`
-   `/business-directory/:id`
-   `/donations`
-   `/donations/:campaign`
-   `/gallery`
-   `/contact`

Authenticated:

-   `/member/dashboard`
-   `/member/profile`
-   `/member/membership`
-   `/member/business`
-   `/member/matrimonial`
-   `/member/payments`
-   `/member/notifications`

Admin:

-   `/admin/login`
-   `/admin`
-   `/admin/members`
-   `/admin/areas`
-   `/admin/memberships`
-   `/admin/businesses`
-   `/admin/matrimonial`
-   `/admin/donations`
-   `/admin/payments`
-   `/admin/banners`
-   `/admin/gallery`
-   `/admin/reports`
-   `/admin/receipts`
-   `/admin/notifications`
-   `/admin/settings`
-   `/admin/audit-logs`

Use the routing approach appropriate to the selected stack.

------------------------------------------------------------------------

# 25. Error, Empty and Loading States

Every data-driven screen must handle:

-   Loading
-   Empty
-   Error
-   Success
-   Permission denied
-   Not found

Examples:

Business directory with no results:

> No approved businesses found for the selected filters.

Matrimonial:

> No matching approved profiles found.

Gallery:

> No gallery albums are currently available.

Do not leave blank screens.

------------------------------------------------------------------------

# 26. SEO

Public pages should have:

-   Proper page titles
-   Meta descriptions
-   Open Graph metadata
-   Semantic headings
-   Clean URLs
-   Image alt text
-   Organization information
-   Local Jaipur relevance

Do not claim the organization serves all of India unless the client
explicitly asks for that.

------------------------------------------------------------------------

# 27. Content/Copy Direction

Use professional and community-oriented copy.

Prefer:

> Khandelwal Samaj Membership Portal

> Connect with your Samaj community in Jaipur.

> Become a Lifetime Member

> Support the Samaj

> Explore Approved Businesses

> Community Events & Gallery

Avoid SaaS/startup phrases such as:

> Unified platform

> Join thousands across India

> Manage your community connections

unless there is a genuine contextual reason.

The website should sound like an established Samaj organization.

------------------------------------------------------------------------

# 28. Homepage Statistics

Statistics must be database-driven when the backend is available.

Possible cards:

-   Total Lifetime Members
-   Areas Covered
-   Donations Collected
-   Years of Service

Use labels appropriate to actual available data.

Do not hard-code misleading numbers such as "2,200+" or "₹18L+" in
production.

Admin may configure/override display settings only where necessary.

------------------------------------------------------------------------

# 29. Admin UX

Admin screens must be practical.

Every management page should have:

-   Page title
-   Description
-   Primary action
-   Search
-   Filters where relevant
-   Table/list
-   Pagination
-   Row actions
-   Confirmation dialogs for destructive actions
-   Toast/success feedback
-   Empty states
-   Loading states

Use confirmation before:

-   Delete
-   Reject
-   Suspend
-   Deactivate
-   Permanently remove

------------------------------------------------------------------------

# 30. Seed/Demo Data

For development, create realistic demo data for:

-   Admin
-   Members
-   Jaipur areas
-   Businesses
-   Donation campaigns
-   Donations
-   Gallery albums
-   Homepage banners

Clearly mark demo/test data and make it easy to remove.

The permanent "Samaj Donation" campaign must exist in seed data.

Create several sample center banner slides so the carousel can be
demonstrated.

------------------------------------------------------------------------

# 31. Development Approach

Before changing the project:

1.  Inspect the existing repository.
2.  Identify the current framework/build setup.
3.  Preserve useful existing work.
4.  Do not replace the entire project unnecessarily.
5.  Identify missing functionality.
6.  Establish a clean component/design system.
7.  Implement public pages.
8.  Implement authentication.
9.  Implement member area.
10. Implement admin area.
11. Implement database models/migrations.
12. Implement payment abstractions.
13. Implement uploads/gallery/banner management.
14. Implement reports/receipts.
15. Add validation and security.
16. Test responsive layouts.
17. Fix all build/runtime errors.

If the existing project is only static HTML, upgrade it to an
appropriate full-stack architecture rather than pretending that static
JavaScript is a secure backend.

------------------------------------------------------------------------

# 32. Preferred Technical Quality

Use:

-   Reusable components
-   Reusable form controls
-   Reusable tables
-   Reusable modals/dialogs
-   Centralized API/service layer
-   Centralized validation
-   Centralized authentication/authorization
-   Environment configuration
-   Database migrations
-   Proper error handling

Avoid:

-   Giant single HTML files
-   Repeated inline styles
-   Repeated business logic
-   Hard-coded production data
-   Frontend-only authorization
-   Fake payment success
-   Fake admin functionality
-   Dead buttons
-   Placeholder pages presented as complete features

------------------------------------------------------------------------

# 33. If Using a Modern Web Stack

If the repository already has a framework, follow its conventions.

If no framework exists and a full-stack rebuild is required, choose a
maintainable modern stack appropriate for the deployment environment.

A reasonable implementation can use:

-   React/Next.js or another established frontend framework
-   TypeScript
-   A relational database
-   Server/API routes
-   ORM
-   Utility-first CSS or a well-structured CSS system

However, do not introduce unnecessary complexity if the existing project
already has a suitable architecture.

The final application must be runnable with clear commands.

------------------------------------------------------------------------

# 34. Environment Configuration

Create/document environment variables for:

-   Database
-   SMS provider
-   Payment gateway
-   Storage/upload provider if used
-   Application URL
-   Admin configuration
-   Email provider if used

Provide `.env.example`.

Never commit actual credentials.

------------------------------------------------------------------------

# 35. Testing Requirements

Test at minimum:

## Authentication

-   Valid OTP
-   Invalid OTP
-   Expired OTP
-   Resend OTP
-   Rate limit
-   Login/logout

## Member

-   Registration
-   Area selection
-   Profile edit
-   Verification

## Membership

-   One-time payment
-   Lifetime status
-   No renewal UI
-   Receipt generation

## Business

-   Member submission
-   Admin approval
-   Admin rejection
-   Public visibility only after approval

## Matrimonial

-   Unauthorized public access blocked
-   Logged-in access
-   Approval workflow
-   Search/filter

## Donations

-   Permanent Samaj Donation
-   Admin-created campaign
-   Online donation
-   Offline donation
-   Offline verification
-   Live totals
-   Receipt

## Banner

-   Admin creates slide
-   Edit/delete/deactivate
-   Ordering
-   Carousel
-   Mobile behavior
-   Left/right live donation panels

## Gallery

-   Admin upload
-   Title
-   Album
-   Public display
-   Mobile layout

## Security

-   Unauthorized admin access
-   Unauthorized member access
-   ID manipulation
-   File upload validation
-   Payment webhook verification

------------------------------------------------------------------------

# 36. Definition of Done

Do not consider the task complete merely because pages render.

The project is complete only when:

-   Public website works
-   Website is responsive
-   Member OTP flow works or is cleanly abstracted for provider
    configuration
-   Member profiles work
-   Jaipur area management works
-   Lifetime membership works
-   No renewal functionality exists
-   Business approval workflow works
-   Matrimonial module is private and approval-controlled
-   Online/offline donations work
-   Permanent Samaj Donation exists
-   Admin can create additional campaigns
-   Live donation data appears on homepage
-   Top banner is admin-managed
-   Three-column banner layout works
-   Center carousel is admin-controlled
-   Left and right donation panels work
-   Gallery is manageable by admin
-   Gallery titles appear publicly
-   Admin dashboard works
-   Reports/export functionality works
-   PDF receipts work
-   Notifications architecture exists
-   Protected routes work
-   Error/loading/empty states exist
-   No India-wide wording remains
-   No City/State member fields remain
-   No membership renewal functionality remains
-   No major console/build/runtime errors remain
-   Mobile layout has been tested
-   README/setup instructions are accurate

------------------------------------------------------------------------

# 37. Final UI Acceptance Checklist

Before finishing, inspect the actual rendered pages and verify:

-   [ ] Header is responsive
-   [ ] Logo/branding looks like a Samaj organization
-   [ ] Navigation is clear
-   [ ] Homepage hero/banner is visually strong
-   [ ] Three-part banner has small-left / large-center / small-right
    layout
-   [ ] Each banner area behaves correctly on mobile
-   [ ] Center carousel is controlled by admin data
-   [ ] Left donation display uses live donation data
-   [ ] Right donation display uses live donation data
-   [ ] Donation progress is visually understandable
-   [ ] No "across India" wording remains
-   [ ] No City/State fields remain in member creation
-   [ ] Jaipur Area/Locality is available
-   [ ] Lifetime membership is clearly communicated
-   [ ] No renewal language/UI exists
-   [ ] Business directory only shows approved profiles
-   [ ] Matrimonial is not publicly browseable
-   [ ] Admin can approve/reject profiles
-   [ ] Admin can create donation campaigns
-   [ ] Samaj Donation cannot accidentally be removed
-   [ ] Admin can manage gallery titles/images
-   [ ] Gallery is visible on the public website
-   [ ] Reports and receipts are accessible
-   [ ] Forms have proper validation
-   [ ] Tables work on mobile
-   [ ] No horizontal overflow
-   [ ] No dead buttons
-   [ ] No placeholder lorem ipsum
-   [ ] No fake production statistics
-   [ ] No exposed secrets
-   [ ] No unauthorized private data exposure

------------------------------------------------------------------------

# 38. Important Instruction to Claude

Treat this file as the **product requirements and implementation
guardrail**.

When making implementation decisions, prioritize:

1.  Security
2.  Correct business rules
3.  Data integrity
4.  Admin manageability
5.  Responsive usability
6.  Accessibility
7.  Visual quality
8.  Maintainability

If an existing feature conflicts with this document, this document's
corrected requirements take precedence unless the client explicitly
provides a newer requirement.

Do not ask for confirmation for every small implementation decision.
Make sensible, production-quality decisions and continue.

When a requirement is ambiguous, choose the simplest implementation that
preserves the stated business rule and document the assumption in the
code/README.

Do not mark functionality as complete if it is only a visual
placeholder.

Build the actual working Khandelwal Samaj website and admin system.
