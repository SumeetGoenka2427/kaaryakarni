<?php

it('renders the English homepage inside the site layout', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="en">', false)
        ->assertSee('Skip to main content')
        ->assertSee('Khandelwal Samaj')
        ->assertSee('Become a Lifetime Member')
        ->assertSee('no renewals, no recurring fees, no expiry');
});

it('renders the Hindi homepage with Hindi chrome and content', function () {
    $this->get('/hi')
        ->assertOk()
        ->assertSee('<html lang="hi">', false)
        ->assertSee('मुख्य सामग्री पर जाएं')
        ->assertSee('खंडेलवाल समाज')
        ->assertSee('आजीवन सदस्य बनें');
});

it('links the language toggle to the same page in the other language', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toMatch('#<a href="'.preg_quote(url('/hi'), '#').'"\s+hreflang="hi"#')
        ->and($html)->toContain('hreflang="hi"')
        ->and($html)->toContain('aria-current="true"');
});

it('publishes hreflang alternates for search engines', function () {
    $this->get('/hi')
        ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/').'">', false)
        ->assertSee('<link rel="alternate" hreflang="hi" href="'.url('/hi').'">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/').'">', false);
});

it('never renders a nav link to a route that does not exist yet', function () {
    $html = $this->get('/')->getContent();

    expect($html)->not->toContain('href="#"')
        ->and($html)->toContain(e(__('web.nav.home')));
});

it('shows a styled English 404 page', function () {
    $this->get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSee('Page Not Found')
        ->assertSee('Go to Homepage');
});

it('shows a Hindi 404 page under /hi', function () {
    $this->get('/hi/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSee('पृष्ठ नहीं मिला')
        ->assertSee('<html lang="hi">', false);
});

it('contains no India-wide, city/state or renewal wording on the homepage', function () {
    foreach (['/', '/hi'] as $url) {
        $text = strtolower(strip_tags($this->get($url)->getContent()));
        expect($text)->not->toContain('across india')
            ->and($text)->not->toContain('renew now')
            ->and($text)->not->toContain('expiry date');
    }
});
