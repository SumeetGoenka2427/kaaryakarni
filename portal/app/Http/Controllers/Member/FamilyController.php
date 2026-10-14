<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Http\Requests\FamilyMemberRequest;
use App\Models\FamilyMember;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Member → My Family. Every lookup is scoped to the logged-in member, so a
 * member can never read or change another member's family (404 otherwise).
 */
class FamilyController extends Controller
{
    use HandlesImageUpload;

    public function index(Request $request): View
    {
        return view('member.family.index', ['family' => $request->user('member')->family()->get()]);
    }

    public function create(Request $request): View
    {
        $next = (int) $request->user('member')->family()->max('sort_order') + 1;

        return view('member.family.form', ['person' => new FamilyMember(['lives_with_member' => true, 'sort_order' => $next])]);
    }

    public function store(FamilyMemberRequest $request): RedirectResponse
    {
        $member = $request->user('member');
        $request->checkAgainst($member->id);

        $person = new FamilyMember($request->familyData());
        $person->member_id = $member->id;
        $this->syncImage($request, $person, 'photo', 'family', [160, 400], 1, 'photo', 'private');
        $person->save();

        return redirect(localized_route('member.family.index'))->with('success', __('web.family.saved'));
    }

    public function edit(Request $request, string $family): View
    {
        return view('member.family.form', ['person' => $this->own($request, $family)]);
    }

    public function update(FamilyMemberRequest $request, string $family): RedirectResponse
    {
        $person = $this->own($request, $family);
        $request->checkAgainst($person->member_id, $person);

        $person->fill($request->familyData());
        $this->syncImage($request, $person, 'photo', 'family', [160, 400], 1, 'photo', 'private');
        $person->save();

        return redirect(localized_route('member.family.index'))->with('success', __('web.family.saved'));
    }

    public function destroy(Request $request, string $family, ImageService $images): RedirectResponse
    {
        $person = $this->own($request, $family);
        $images->delete($person->photo);
        $person->photo = null;
        $person->save();
        $person->delete();

        return redirect(localized_route('member.family.index'))->with('success', __('web.family.deleted'));
    }

    /** The logged-in member's own family row by ULID, or 404. */
    private function own(Request $request, string $ulid): FamilyMember
    {
        return $request->user('member')->family()->where('ulid', $ulid)->firstOrFail();
    }
}
