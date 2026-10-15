<?php

namespace App\Http\Controllers\Member;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessProfileRequest;
use App\Models\Area;
use App\Models\BusinessCategory;
use App\Services\Business\BusinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Member → My Business Profile (web/member/business.html). One listing per member. */
class BusinessController extends Controller
{
    public function edit(Request $request): View
    {
        $member = $request->user('member');
        $business = $member->business()->first();

        return view('member.business', [
            'member' => $member,
            'business' => $business,
            'canList' => $member->status === MemberStatus::Verified,
            'categories' => BusinessCategory::where('is_active', true)->orWhere('id', $business?->category_id)
                ->orderBy('sort_order')->orderBy('name_en')->get()->mapWithKeys(fn ($c) => [$c->id => $c->t('name')]),
            'areas' => Area::where('is_active', true)->orWhere('id', $business?->area_id ?? $member->area_id)
                ->orderBy('sort_order')->get()->mapWithKeys(fn ($a) => [$a->id => $a->t('name')]),
        ]);
    }

    public function update(BusinessProfileRequest $request, BusinessService $service): RedirectResponse
    {
        $member = $request->user('member');
        abort_unless($member->status === MemberStatus::Verified, 403, __('web.business.needs_verification'));

        [, $review] = $service->saveByMember($member, $request->listingData(), $request->file('logo'), $request->boolean('remove_logo'));

        return redirect(localized_route('member.business'))->with('success', __($review ? 'web.business.submitted' : 'web.business.saved'));
    }
}
