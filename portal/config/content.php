<?php

/*
|--------------------------------------------------------------------------
| Admin → Page Content: which editable blocks each public page has
|--------------------------------------------------------------------------
| fields: label | title | body | image | link_url   (all text fields are EN + HI)
| single: true  → exactly one block (edit only), false → a list admin can add to
| Page headings/short labels are in Admin → Site Texts; office contact and
| the membership fee are in Admin → Settings.
*/

return [
    'home' => [
        'label' => 'Homepage',
        'sections' => [
            'about' => [
                'label' => 'About block (image + text)', 'single' => true,
                'fields' => ['title', 'body', 'image', 'label', 'link_url'],
                'help' => 'Label = button text, Link = button link (e.g. /about).',
            ],
            'links' => [
                'label' => 'Important Links cards', 'single' => false,
                'fields' => ['title', 'body', 'link_url'],
                'help' => 'Short card with a title, one line of text and a link.',
            ],
        ],
    ],
    'about' => [
        'label' => 'About Samaj',
        'sections' => [
            'history' => [
                'label' => 'Our History (text + image)', 'single' => true,
                'fields' => ['title', 'body', 'image'],
                'help' => 'Leave an empty line between paragraphs.',
            ],
            'values' => [
                'label' => 'Mission / Vision / Values cards', 'single' => false,
                'fields' => ['title', 'body'],
            ],
        ],
    ],
];
