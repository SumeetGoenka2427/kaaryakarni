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

    // `can` = permission required to see the item (enforced again on the route).
    'admin' => [
        'Overview' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
        ],
        'Members' => [
            ['route' => 'admin.members.index', 'label' => 'Members', 'can' => 'members.view'],
            ['route' => 'admin.areas.index', 'label' => 'Jaipur Areas', 'can' => 'areas.manage'],
            ['route' => 'admin.memberships.index', 'label' => 'Memberships', 'can' => 'memberships.view'],
        ],
        'Community Content' => [
            ['route' => 'admin.businesses.index', 'label' => 'Business Directory', 'can' => 'businesses.view'],
            ['route' => 'admin.categories.index', 'label' => 'Business Categories', 'can' => 'businesses.manage'],
            ['route' => 'admin.matrimonial.index', 'label' => 'Matrimonial', 'can' => 'matrimonial.view'],
            ['route' => 'admin.campaigns.index', 'label' => 'Donation Campaigns', 'can' => 'donations.view'],
            ['route' => 'admin.donations.index', 'label' => 'Donation Records', 'can' => 'donations.view'],
            ['route' => 'admin.banners.index', 'label' => 'Homepage Banners', 'can' => 'content.manage'],
            ['route' => 'admin.gallery.index', 'label' => 'Gallery', 'can' => 'content.manage'],
            ['route' => 'admin.committee.index', 'label' => 'Committee', 'can' => 'content.manage'],
            ['route' => 'admin.announcements.index', 'label' => 'Announcements', 'can' => 'content.manage'],
            ['route' => 'admin.content.index', 'label' => 'Page Content', 'can' => 'content.manage'],
            ['route' => 'admin.contact-messages.index', 'label' => 'Contact Messages', 'can' => 'content.manage'],
        ],
        'Finance' => [
            ['route' => 'admin.payments.index', 'label' => 'Payments', 'can' => 'payments.view'],
            ['route' => 'admin.receipts.index', 'label' => 'Receipts', 'can' => 'payments.view'],
        ],
        'System' => [
            ['route' => 'admin.reports.index', 'label' => 'Reports', 'can' => 'reports.view'],
            ['route' => 'admin.notifications.index', 'label' => 'Notifications', 'can' => 'notifications.send'],
            ['route' => 'admin.branding.edit', 'label' => 'Branding & Logo', 'can' => 'settings.manage'],
            ['route' => 'admin.site-texts.index', 'label' => 'Site Texts', 'can' => 'content.manage'],
            ['route' => 'admin.settings.edit', 'label' => 'Settings', 'can' => 'settings.manage'],
            ['route' => 'admin.admin-users.index', 'label' => 'Admin Users', 'can' => 'admins.manage'],
            ['route' => 'admin.audit-logs.index', 'label' => 'Audit Logs', 'can' => 'audit.view'],
        ],
    ],
];
