<?php

use Tests\TestCase;

uses(TestCase::class);

it('formats rupees in the Indian numbering system', function () {
    expect(inr(482600))->toBe('₹4,82,600')
        ->and(inr(1250.5))->toBe('₹1,250.5')
        ->and(inr(750000, false))->toBe('7,50,000');
});

it('builds localized route URLs', function () {
    app()->setLocale('hi');
    expect(localized_route('home', absolute: false))->toBe('/hi');

    app()->setLocale('en');
    expect(localized_route('home', absolute: false))->toBe('/');
});

it('strips the language prefix from route names', function () {
    expect(base_route_name('hi.about'))->toBe('about')
        ->and(base_route_name('about'))->toBe('about')
        ->and(base_route_name('history.index'))->toBe('history.index');
});
