<?php

it('serves the English homepage with English locale', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="en">', false);
});

it('serves the Hindi homepage under the /hi prefix', function () {
    $this->get('/hi')
        ->assertOk()
        ->assertSee('<html lang="hi">', false)
        ->assertCookie('site_locale', 'hi', false);
});

it('names Hindi routes with the hi. prefix', function () {
    expect(route('home', absolute: false))->toBe('/')
        ->and(route('hi.home', absolute: false))->toBe('/hi');
});

it('sends security headers on web responses', function () {
    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('uses a nonce-based content security policy', function () {
    $csp = $this->get('/')->headers->get('Content-Security-Policy');

    expect($csp)->toContain("script-src 'self' 'nonce-")
        ->and($csp)->toContain("object-src 'none'")
        ->and($csp)->toContain("frame-ancestors 'none'");
});
