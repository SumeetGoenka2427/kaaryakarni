<?php

namespace App\Http\Requests;

use App\Enums\MaritalStatus;
use App\Models\Area;
use App\Models\MatrimonialProfile;
use App\Services\Media\ImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for a matrimonial profile (member area + admin edit).
 * Status, owner and approval fields are never taken from input.
 */
class MatrimonialProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:150'];
        $person = ['nullable', 'string', 'max:120', 'regex:/^[\pL\pM\s.\'-]+$/u'];

        return [
            'relation' => ['required', Rule::in(MatrimonialProfile::RELATIONS)],
            'name' => ['required', 'string', 'min:2', 'max:120', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'dob' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString(), 'after:'.now()->subYears(80)->toDateString()],
            'height_cm' => ['nullable', 'integer', Rule::in(array_keys(MatrimonialProfile::heightOptions()))],
            'marital_status' => ['required', Rule::enum(MaritalStatus::class)->except([MaritalStatus::Married])],
            'father_name' => $person,
            'mother_name' => $person,
            'gotra' => ['nullable', 'string', 'max:80'],
            'native_place' => ['nullable', 'string', 'max:120'],
            'area_id' => ['nullable', 'integer', Rule::exists(Area::class, 'id')],
            'education' => $text,
            'occupation' => $text,
            'about' => ['nullable', 'string', 'max:1000'],
            'show_contact' => ['boolean'],
            'family' => ['nullable', 'string', 'size:26'],
            'photos' => ['nullable', 'array', 'max:'.MatrimonialProfile::MAX_PHOTOS],
            'photos.*' => ImageService::rules(true),
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer'],
            'bio_data' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:3072'],
            'remove_bio' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return collect([
            'relation' => 'relation', 'name' => 'name', 'gender' => 'gender', 'dob' => 'dob', 'height_cm' => 'height',
            'marital_status' => 'marital_status', 'father_name' => 'father', 'mother_name' => 'mother', 'gotra' => 'gotra',
            'native_place' => 'native_place', 'area_id' => 'area', 'education' => 'education', 'occupation' => 'occupation',
            'about' => 'about', 'photos' => 'photos', 'photos.*' => 'photos', 'bio_data' => 'bio_data',
        ])->map(fn ($k) => __("web.matrimonial.{$k}"))->all();
    }

    /** Fillable profile fields only. */
    public function profileData(): array
    {
        $data = $this->safe()->only([
            'relation', 'name', 'gender', 'dob', 'height_cm', 'marital_status', 'father_name', 'mother_name',
            'gotra', 'native_place', 'area_id', 'education', 'occupation', 'about',
        ]);
        $data['show_contact'] = $this->boolean('show_contact');

        return $data;
    }
}
