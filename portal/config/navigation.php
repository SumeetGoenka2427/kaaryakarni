<?php

/*
|--------------------------------------------------------------------------
| Navigation menus
|--------------------------------------------------------------------------
| Items whose route does not exist yet are skipped automatically, so the menu
| never shows a dead link while modules are being built part by part.
| Public/member labels are translation keys; admin labels are plain English.
*/

return [

    'public' => [
        ['route' => 'home', 'label' => 'web.nav.home', 'active' => ['home']],
        ['route' => 'about', 'label' => 'web.nav.about', 'active' => ['about', 'committee']],
        ['route' => 'business.index', 'label' => 'web.nav.directory', 'active' => ['business.*']],
        ['route' => 'gallery.index', 'label' => 'web.nav.gallery', 'active' => ['gallery.*']],
        ['route' => 'donations.index', 'label' => 'web.nav.donations', 'active' => ['donations.*']],
        ['route' => 'contact', 'label' => 'web.nav.contact', 'active' => ['contact']],
    ],

    'member' => [
        'main' => [
            ['route' => 'member.dashboard', 'label' => 'web.member_nav.dashboard'],
            ['route' => 'member.profile', 'label' => 'web.member_nav.profile'],
            ['route' => 'member.family.index', 'label' => 'web.member_nav.family'],
            ['route' => 'member.membership', 'label' => 'web.member_nav.membership'],
        ],
        'more' => [
            ['route' => 'member.business', 'label' => 'web.member_nav.business'],
            ['route' => 'member.matrimonial.index', 'label' => 'web.member_nav.matrimonial'],
            ['route' => 'member.matrimonial.browse', 'label' => 'web.member_nav.matrimonial_browse'],
            ['route' => 'member.payments', 'label' => 'web.member_nav.payments'],
            ['route' => 'member.notifications', 'label' => 'web.member_nav.notifications'],
        ],
    ],

    // Admin sidebar, grouped by task.
    // can    = permission required to see the item (enforced again on the route)
    // icon   = <x-admin.nav-icon> name
    // active = route patterns that highlight the item
    // badge  = key from AdminNavBadges (count of things waiting for action)
    'admin' => [
        'Overview' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'active' => ['admin.dashboard']],
        ],
        'Members' => [
            ['route' => 'admin.members.index', 'label' => 'Members', 'can' => 'members.view', 'icon' => 'users', 'active' => ['admin.members.*'], 'badge' => 'members'],
            ['route' => 'admin.memberships.index', 'label' => 'Lifetime Memberships', 'can' => 'memberships.view', 'icon' => 'badge', 'active' => ['admin.memberships.*']],
            ['route' => 'admin.matrimonial.index', 'label' => 'Matrimonial', 'can' => 'matrimonial.view', 'icon' => 'heart', 'active' => ['admin.matrimonial.*'], 'badge' => 'matrimonial'],
        ],
        'Business Directory' => [
            ['route' => 'admin.businesses.index', 'label' => 'Business Listings', 'can' => 'businesses.view', 'icon' => 'briefcase', 'active' => ['admin.businesses.*'], 'badge' => 'businesses'],
            ['route' => 'admin.categories.index', 'label' => 'Categories', 'can' => 'businesses.manage', 'icon' => 'tag', 'active' => ['admin.categories.*']],
        ],
        'Donations & Payments' => [
            ['route' => 'admin.campaigns.index', 'label' => 'Donation Campaigns', 'can' => 'donations.view', 'icon' => 'gift', 'active' => ['admin.campaigns.*']],
            ['route' => 'admin.donations.index', 'label' => 'Donation Records', 'can' => 'donations.view', 'icon' => 'list', 'active' => ['admin.donations.*']],
            ['route' => 'admin.payments.index', 'label' => 'Payments', 'can' => 'payments.view', 'icon' => 'card', 'active' => ['admin.payments.*'], 'badge' => 'payments'],
            ['route' => 'admin.receipts.index', 'label' => 'Receipts', 'can' => 'payments.view', 'icon' => 'receipt', 'active' => ['admin.receipts.*']],
        ],
        'Website Content' => [
            ['route' => 'admin.banners.index', 'label' => 'Homepage Banners', 'can' => 'content.manage', 'icon' => 'layout', 'active' => ['admin.banners.*', 'admin.banner-panels.*']],
            ['route' => 'admin.content.index', 'label' => 'Page Content', 'can' => 'content.manage', 'icon' => 'file', 'active' => ['admin.content.*']],
            ['route' => 'admin.announcements.index', 'label' => 'Events & Announcements', 'can' => 'content.manage', 'icon' => 'calendar', 'active' => ['admin.announcements.*']],
            ['route' => 'admin.gallery.index', 'label' => 'Gallery', 'can' => 'content.manage', 'icon' => 'image', 'active' => ['admin.gallery.*']],
            ['route' => 'admin.committee.index', 'label' => 'Committee', 'can' => 'content.manage', 'icon' => 'group', 'active' => ['admin.committee.*']],
            ['route' => 'admin.site-texts.index', 'label' => 'Website Texts (EN / HI)', 'can' => 'content.manage', 'icon' => 'type', 'active' => ['admin.site-texts.*']],
        ],
        'Communication' => [
            ['route' => 'admin.notifications.index', 'label' => 'Notifications & SMS', 'can' => 'notifications.send', 'icon' => 'bell', 'active' => ['admin.notifications.*']],
            ['route' => 'admin.contact-messages.index', 'label' => 'Contact Messages', 'can' => 'content.manage', 'icon' => 'mail', 'active' => ['admin.contact-messages.*'], 'badge' => 'messages'],
        ],
        'Reports & Logs' => [
            ['route' => 'admin.reports.index', 'label' => 'Reports & Exports', 'can' => 'reports.view', 'icon' => 'chart', 'active' => ['admin.reports.*']],
            ['route' => 'admin.audit-logs.index', 'label' => 'Audit Log', 'can' => 'audit.view', 'icon' => 'shield', 'active' => ['admin.audit-logs.*']],
        ],
        'Settings' => [
            ['route' => 'admin.settings.edit', 'label' => 'Organisation Settings', 'can' => 'settings.manage', 'icon' => 'settings', 'active' => ['admin.settings.*']],
            ['route' => 'admin.branding.edit', 'label' => 'Branding & Logo', 'can' => 'settings.manage', 'icon' => 'palette', 'active' => ['admin.branding.*']],
            ['route' => 'admin.areas.index', 'label' => 'Jaipur Areas', 'can' => 'areas.manage', 'icon' => 'pin', 'active' => ['admin.areas.*']],
            ['route' => 'admin.admin-users.index', 'label' => 'Admin Users & Roles', 'can' => 'admins.manage', 'icon' => 'key', 'active' => ['admin.admin-users.*']],
        ],
    ],
];
