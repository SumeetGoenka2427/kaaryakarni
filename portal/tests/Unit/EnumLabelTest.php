<?php

use App\Enums\MembershipStatus;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class);

it('has an English and Hindi label for every enum case', function () {
    foreach (File::files(app_path('Enums')) as $file) {
        $enum = 'App\\Enums\\'.$file->getFilenameWithoutExtension();

        foreach ($enum::cases() as $case) {
            foreach (['en', 'hi'] as $locale) {
                app()->setLocale($locale);
                $label = $case->label();
                expect($label)->not->toStartWith('enums.', "{$enum}::{$case->name} is missing a {$locale} label");
            }
        }
    }
});

it('labels an active membership as lifetime, with no expiry or renewal state', function () {
    app()->setLocale('en');

    expect(MembershipStatus::Active->label())->toBe('Lifetime Member')
        ->and(MembershipStatus::values())->not->toContain('expired')
        ->and(MembershipStatus::values())->not->toContain('renewal_due');
});
