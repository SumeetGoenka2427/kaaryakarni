<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Http\Requests\FamilyMemberRequest;
use App\Models\FamilyMember;
use App\Models\Member;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Admin edits a member's family (Members → member → Family tab). */
class MemberFamilyController extends Controller
{
    use HandlesImageUpload;

    public function __construct(private readonly AuditLogger $audit) {}

    public function create(Member $member): View
    {
        return view('admin.members.family-form', ['member' => $member, 'person' => new FamilyMember(['lives_with_member' => true])]);
    }

    public function store(FamilyMemberRequest $request, Member $member): RedirectResponse
    {
        $request->checkAgainst($member->id);
        $person = new FamilyMember($request->familyData());
        $person->member_id = $member->id;
        $this->syncImage($request, $person, 'photo', 'family', [160, 400], 1, 'photo', 'private');
        $person->save();
        $this->audit->record('member.family_added', $member, "Added {$person->relation->label()} {$person->name} to {$member->name}");

        return redirect()->route('admin.members.show', [$member, 'tab' => 'family'])->with('success', 'Family member added.');
    }

    public function edit(Member $member, string $family): View
    {
        return view('admin.members.family-form', ['member' => $member, 'person' => $this->find($member, $family)]);
    }

    public function update(FamilyMemberRequest $request, Member $member, string $family): RedirectResponse
    {
        $person = $this->find($member, $family);
        $request->checkAgainst($member->id, $person);
        $person->fill($request->familyData());
        $this->syncImage($request, $person, 'photo', 'family', [160, 400], 1, 'photo', 'private');
        $person->save();
        $this->audit->record('member.family_updated', $member, "Updated {$person->name} in {$member->name}'s family");

        return redirect()->route('admin.members.show', [$member, 'tab' => 'family'])->with('success', 'Family member saved.');
    }

    public function destroy(Member $member, string $family, ImageService $images): RedirectResponse
    {
        $person = $this->find($member, $family);
        $images->delete($person->photo);
        $person->photo = null;
        $person->save();
        $person->delete();
        $this->audit->record('member.family_removed', $member, "Removed {$person->name} from {$member->name}'s family");

        return redirect()->route('admin.members.show', [$member, 'tab' => 'family'])->with('success', 'Family member removed.');
    }

    private function find(Member $member, string $ulid): FamilyMember
    {
        return $member->family()->where('ulid', $ulid)->firstOrFail();
    }
}
