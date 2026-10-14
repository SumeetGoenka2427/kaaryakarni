<?php

namespace App\Http\Requests\Web;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'father_or_husband_name' => ['nullable', 'string', 'max:120', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'dob' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            // Only active, admin-managed Jaipur areas.
            'area_id' => ['required', 'integer', Rule::exists('areas', 'id')->where('is_active', true)],
            'address' => ['nullable', 'string', 'max:500'],
            'consent' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('web.register.full_name'),
            'father_or_husband_name' => __('web.register.father_name'),
            'dob' => __('web.register.dob'),
            'gender' => __('web.register.gender'),
            'email' => __('web.register.email'),
            'area_id' => __('web.register.area'),
            'address' => __('web.register.address'),
            'consent' => __('web.register.consent_short'),
        ];
    }
}
