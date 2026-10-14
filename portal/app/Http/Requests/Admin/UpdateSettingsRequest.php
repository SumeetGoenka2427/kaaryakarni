<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:255'];
        $hex = ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'];

        return [
            'org_name_en' => ['required', 'string', 'max:150'],
            'org_name_hi' => ['nullable', 'string', 'max:150'],
            'org_short_name_en' => ['required', 'string', 'max:80'],
            'org_short_name_hi' => ['nullable', 'string', 'max:80'],
            'tagline_en' => ['nullable', 'string', 'max:500'],
            'tagline_hi' => ['nullable', 'string', 'max:500'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.now()->year],

            'office_name_en' => $text,
            'office_name_hi' => $text,
            'address_en' => ['nullable', 'string', 'max:500'],
            'address_hi' => ['nullable', 'string', 'max:500'],
            'phone_primary' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'phone_secondary' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'whatsapp' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'office_hours_en' => $text,
            'office_hours_hi' => $text,
            'map_url' => ['nullable', 'url:https', 'max:500'],

            'social_facebook' => ['nullable', 'url:https', 'max:255'],
            'social_instagram' => ['nullable', 'url:https', 'max:255'],
            'social_youtube' => ['nullable', 'url:https', 'max:255'],

            'membership_fee' => ['required', 'integer', 'min:0', 'max:1000000'],
            'donor_names_public' => ['boolean'],
            'business_reapproval_on_edit' => ['boolean'],
            'show_name_beside_logo' => ['boolean'],

            'color_primary' => $hex,
            'color_gold' => $hex,
            'color_accent' => $hex,
        ];
    }

    public function messages(): array
    {
        return ['regex' => 'The :attribute format is invalid.', 'whatsapp.regex' => 'Enter a 10-digit Indian mobile number.'];
    }

    /** Validated values with correct PHP types (unchecked boxes → false, numbers → int). */
    public function settings(): array
    {
        $data = $this->validated();

        foreach (['donor_names_public', 'business_reapproval_on_edit', 'show_name_beside_logo'] as $flag) {
            $data[$flag] = $this->boolean($flag);
        }
        $data['membership_fee'] = (int) $data['membership_fee'];
        $data['founded_year'] = filled($data['founded_year'] ?? null) ? (int) $data['founded_year'] : null;

        return array_map(fn ($v) => $v === '' ? null : $v, $data);
    }
}
