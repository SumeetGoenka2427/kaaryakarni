<?php

use App\Enums\MembershipStatus;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

beforeEach(fn () => view()->share('errors', new ViewErrorBag));

it('renders the admin layout with its sidebar and title', function () {
    $html = Blade::render('<x-layouts.admin title="Jaipur Areas"><p>Body</p></x-layouts.admin>');

    expect($html)->toContain('class="admin-sidebar"')
        ->and($html)->toContain('<h1>Jaipur Areas</h1>')
        ->and($html)->toContain('noindex, nofollow')
        ->and($html)->toContain('<p>Body</p>');
});

it('renders bilingual admin fields as English and Hindi tabs', function () {
    $html = Blade::render('<x-admin.translatable-field name="title" label="Title" required />');

    expect($html)->toContain('name="title_en"')
        ->and($html)->toContain('name="title_hi"')
        ->and($html)->toContain('हिन्दी')
        ->and($html)->toContain('required');
});

it('renders a status pill from an enum with its colour and translated label', function () {
    app()->setLocale('hi');
    $html = Blade::render('<x-pill :status="$s" />', ['s' => MembershipStatus::Active]);

    expect($html)->toContain('pill-success')->and($html)->toContain('आजीवन सदस्य');
});

it('renders a form field with a label tied to its input', function () {
    $html = Blade::render('<x-field name="mobile" label="Mobile Number" type="tel" required hint="10 digits" />');

    expect($html)->toContain('<label for="f-mobile">')
        ->and($html)->toContain('id="f-mobile"')
        ->and($html)->toContain('aria-describedby="f-mobile-hint"');
});
