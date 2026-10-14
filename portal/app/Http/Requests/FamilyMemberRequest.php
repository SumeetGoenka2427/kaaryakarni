<?php

namespace App\Http\Requests;

use App\Enums\FamilyRelation;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Models\FamilyMember;
use App\Services\Media\ImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Validation for a family member, shared by the member area and admin.
 * The owning member is resolved by the controller (never from input).
 */
class FamilyMemberRequest extends FormRequest
{
    public const MAX_PER_MEMBER = 30;

    /** Relations a member can have only one of. */
    public const SINGLE_RELATIONS = ['father', 'mother'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relation' => ['required', Rule::enum(FamilyRelation::class)],
            'name' => ['required', 'string', 'min:2', 'max:120', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'dob' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'marital_status' => ['nullable', Rule::enum(MaritalStatus::class)],
            'education' => ['nullable', 'string', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'mobile' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'blood_group' => ['nullable', Rule::in(FamilyMember::BLOOD_GROUPS)],
            'is_deceased' => ['boolean'],
            'lives_with_member' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'photo' => ImageService::rules(),
        ];
    }

    public function attributes(): array
    {
        return collect(['relation', 'name', 'gender', 'dob', 'marital_status', 'education', 'occupation', 'mobile', 'email', 'blood_group', 'notes', 'photo'])
            ->mapWithKeys(fn ($f) => [$f => __("web.family.{$f}")])->all();
    }

    public function messages(): array
    {
        return ['mobile.regex' => __('web.auth.mobile_invalid')];
    }

    /** Extra checks against the member's existing family (one father/mother, max size). */
    public function checkAgainst(int $memberId, ?FamilyMember $current = null): void
    {
        $relation = $this->input('relation');
        $others = FamilyMember::where('member_id', $memberId)->when($current, fn ($q) => $q->whereKeyNot($current->id));

        if (in_array($relation, self::SINGLE_RELATIONS, true) && (clone $others)->where('relation', $relation)->exists()) {
            throw ValidationException::withMessages([
                'relation' => __('web.family.only_one', ['relation' => FamilyRelation::from($relation)->label()]),
            ]);
        }

        if (! $current && $others->count() >= self::MAX_PER_MEMBER) {
            throw ValidationException::withMessages(['relation' => __('web.family.limit', ['max' => self::MAX_PER_MEMBER])]);
        }
    }

    public function familyData(): array
    {
        $data = collect($this->validated())->except('photo')->all();
        $data['is_deceased'] = $this->boolean('is_deceased');
        $data['lives_with_member'] = $this->boolean('lives_with_member');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return array_map(fn ($v) => $v === '' ? null : $v, $data);
    }
}
