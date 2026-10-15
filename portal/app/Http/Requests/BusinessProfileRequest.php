<?php

namespace App\Http\Requests;

use App\Services\Media\ImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Business listing fields (member and admin forms). Status is never accepted from input. */
class BusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $current = $this->route('business')?->area_id ?? $this->user('member')?->business?->area_id;

        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'category_id' => ['required', 'integer', Rule::exists('business_categories', 'id')->where('is_active', true)],
            'area_id' => ['required', 'integer', Rule::exists('areas', 'id')->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $current))],
            'description' => ['required', 'string', 'min:30', 'max:2000'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'whatsapp' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'website' => ['nullable', 'url:https,http', 'max:255'],
            'hours' => ['nullable', 'string', 'max:150'],
            'show_phone' => ['boolean'],
            'show_whatsapp' => ['boolean'],
            'show_email' => ['boolean'],
            'logo' => ImageService::rules(),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('web.business.name'), 'category_id' => __('web.business.category'), 'area_id' => __('web.business.area'),
            'description' => __('web.business.description'), 'address' => __('web.business.address'), 'phone' => __('web.business.phone'),
            'whatsapp' => __('web.business.whatsapp_number'), 'email' => __('web.business.email_label'),
            'website' => __('web.business.website_label'), 'hours' => __('web.business.hours_label'), 'logo' => __('web.business.logo'),
        ];
    }

    public function messages(): array
    {
        return ['phone.regex' => __('web.auth.mobile_invalid'), 'whatsapp.regex' => __('web.auth.mobile_invalid')];
    }

    public function listingData(): array
    {
        $data = collect($this->validated())->except('logo')->all();
        foreach (['show_phone', 'show_whatsapp', 'show_email'] as $flag) {
            $data[$flag] = $this->boolean($flag);
        }

        return array_map(fn ($v) => $v === '' ? null : $v, $data);
    }
}
