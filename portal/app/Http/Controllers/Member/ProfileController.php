<?php

namespace App\Http\Controllers\Member;

use App\Enums\Gender;
use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Member edits their own profile (web/member/profile.html). Status/mobile are not editable here. */
class ProfileController extends Controller
{
    use HandlesImageUpload;

    public function edit(Request $request): View
    {
        $member = $request->user('member');

        return view('member.profile', [
            'member' => $member,
            'areas' => $this->areaOptions($member->area_id),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $member = $request->user('member');

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'father_or_husband_name' => ['nullable', 'string', 'max:120', 'regex:/^[\pL\pM\s.\'-]+$/u'],
            'dob' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            // Current area stays valid even if admin has since deactivated it.
            'area_id' => ['required', 'integer', Rule::exists('areas', 'id')->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $member->area_id))],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ImageService::rules(),
        ], [], [
            'name' => __('web.register.full_name'), 'area_id' => __('web.register.area'), 'photo' => __('web.profile.photo'),
        ]);

        $member->fill(collect($data)->except('photo')->all());
        $this->syncImage($request, $member, 'photo', 'members', [160, 400], 1, 'photo', 'private');
        $member->save();

        return redirect(localized_route('member.profile'))->with('success', __('web.profile.saved'));
    }

    private function areaOptions(?int $currentId)
    {
        return Area::query()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $currentId))
            ->orderBy('sort_order')->orderBy('name_en')->get()
            ->mapWithKeys(fn ($a) => [$a->id => $a->t('name')]);
    }
}
