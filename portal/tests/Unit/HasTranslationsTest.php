<?php

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

uses(TestCase::class);

function bilingual(array $attributes): Model
{
    $model = new class extends Model
    {
        use HasTranslations;

        protected $guarded = [];

        protected array $translatable = ['title'];
    };

    return $model->forceFill($attributes);
}

it('returns Hindi text on Hindi pages', function () {
    app()->setLocale('hi');
    expect(bilingual(['title_en' => 'Samaj Donation', 'title_hi' => 'समाज दान'])->t('title'))->toBe('समाज दान');
});

it('falls back to English when Hindi is empty', function () {
    app()->setLocale('hi');
    expect(bilingual(['title_en' => 'Samaj Donation', 'title_hi' => ''])->t('title'))->toBe('Samaj Donation')
        ->and(bilingual(['title_en' => 'Samaj Donation', 'title_hi' => null])->t('title'))->toBe('Samaj Donation');
});

it('flags rows with English but no Hindi', function () {
    expect(bilingual(['title_en' => 'Samaj Donation', 'title_hi' => null])->isMissingTranslation())->toBeTrue()
        ->and(bilingual(['title_en' => 'Samaj Donation', 'title_hi' => 'समाज दान'])->isMissingTranslation())->toBeFalse();
});
