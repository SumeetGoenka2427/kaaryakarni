<?php

namespace App\Http\Controllers\Member;

use App\Enums\FamilyRelation;
use App\Enums\MaritalStatus;
use App\Enums\MatrimonialStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MatrimonialProfileRequest;
use App\Models\Area;
use App\Models\MatrimonialPhoto;
use App\Models\MatrimonialProfile;
use App\Models\MatrimonialReport;
use App\Models\Member;
use App\Services\Matrimonial\MatrimonialService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Member → Matrimonial (web/member/matrimonial.html + matrimonial-browse.html).
 * Private: Lifetime Members only; other members see only approved profiles.
 */
class MatrimonialController extends Controller
{
    public function __construct(private readonly MatrimonialService $service) {}

    /** My profiles (left) + add / edit form (right). */
    public function index(Request $request): View
    {
        $member = $request->user('member');
        $profiles = $member->matrimonialProfiles()->with('photos', 'area')->get();
        $eligible = $this->service->isEligible($member);

        $editing = null;
        $prefill = [];
        if ($eligible && $request->filled('profile')) {
            $editing = $profiles->firstWhere('ulid', $request->string('profile')->toString());
            abort_unless($editing, 404);
        } elseif ($eligible && $request->boolean('new') && $profiles->count() < MatrimonialProfile::MAX_PER_MEMBER) {
            $prefill = $this->prefillFromFamily($member, $request->string('family')->toString());
        }

        return view('member.matrimonial.index', [
            'member' => $member,
            'profiles' => $profiles,
            'eligible' => $eligible,
            'editing' => $editing,
            'showForm' => $editing || ($eligible && $request->boolean('new') && $profiles->count() < MatrimonialProfile::MAX_PER_MEMBER),
            'prefill' => $prefill,
            'areas' => $this->areas($editing?->area_id ?? $member->area_id),
        ]);
    }

    public function store(MatrimonialProfileRequest $request): RedirectResponse
    {
        $member = $this->eligibleMember($request);

        return $this->persist($request, $member, null);
    }

    public function update(MatrimonialProfileRequest $request, MatrimonialProfile $profile): RedirectResponse
    {
        $member = $this->eligibleMember($request);
        $this->ownOnly($profile, $member);

        return $this->persist($request, $member, $profile);
    }

    public function destroy(Request $request, MatrimonialProfile $profile): RedirectResponse
    {
        $this->ownOnly($profile, $request->user('member'));
        $this->service->deleteByOwner($profile);

        return redirect(localized_route('member.matrimonial.index'))->with('success', __('web.matrimonial.deleted'));
    }

    /** Approved profiles of other members, with filters. */
    public function browse(Request $request): View
    {
        $member = $this->eligibleMember($request);
        $filters = $request->validate([
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'min_age' => ['nullable', 'integer', 'between:18,80'],
            'max_age' => ['nullable', 'integer', 'between:18,80'],
            'area' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $profiles = MatrimonialProfile::approved()
            ->where('member_id', '!=', $member->id)
            ->when($filters['gender'] ?? null, fn ($q, $g) => $q->where('gender', $g))
            // Age N means born between (today - N - 1 years, today - N years].
            ->when($filters['min_age'] ?? null, fn ($q, $a) => $q->where('dob', '<=', now()->subYears($a)->toDateString()))
            ->when($filters['max_age'] ?? null, fn ($q, $a) => $q->where('dob', '>', now()->subYears($a + 1)->toDateString()))
            ->when($filters['area'] ?? null, fn ($q, $a) => $q->where('area_id', $a))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('education', 'like', $like)->orWhere('occupation', 'like', $like)
                    ->orWhere('native_place', 'like', $like)->orWhere('gotra', 'like', $like));
            })
            ->with(['area', 'photos'])
            ->latest('approved_at')
            ->paginate(12)
            ->withQueryString();

        return view('member.matrimonial.browse', [
            'profiles' => $profiles,
            'filters' => $filters,
            'areas' => $this->areas(null),
        ]);
    }

    public function show(Request $request, MatrimonialProfile $profile): View
    {
        $member = $request->user('member');
        $own = $profile->member_id === $member->id;
        if (! $own) {
            $this->eligibleMember($request);
        }
        // Unapproved profiles of other members do not exist as far as viewers are concerned.
        abort_unless($own || $profile->status === MatrimonialStatus::Approved, 404);

        $profile->load('photos', 'area', 'member');

        return view('member.matrimonial.show', [
            'profile' => $profile,
            'own' => $own,
            'reported' => ! $own && MatrimonialReport::where('profile_id', $profile->id)->where('reporter_id', $member->id)->exists(),
        ]);
    }

    public function report(Request $request, MatrimonialProfile $profile): RedirectResponse
    {
        $member = $this->eligibleMember($request);
        abort_unless($profile->status === MatrimonialStatus::Approved, 404);
        $data = $request->validate([
            'reason' => ['required', Rule::in(MatrimonialReport::REASONS)],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->service->report($profile, $member, $data['reason'], $data['details'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('web.matrimonial.reported'));
    }

    // ---- Private files ----

    public function photo(Request $request, MatrimonialPhoto $photo, ?int $width = null): Response
    {
        $profile = $photo->profile;
        abort_unless($profile && $this->service->canView($profile, $request->user('member'), $request->user('admin')), 404);

        $image = $photo->image;
        $variants = $image['variants'] ?? [];
        $path = $width && isset($variants[$width]) ? $variants[$width] : ($image['path'] ?? null);
        abort_unless($path && ($image['disk'] ?? null) === 'private' && Storage::disk('private')->exists($path), 404);

        return response(Storage::disk('private')->get($path), 200, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function bioData(Request $request, MatrimonialProfile $profile): Response
    {
        abort_unless($this->service->canView($profile, $request->user('member'), $request->user('admin')), 404);

        return $this->service->bioDataResponse($profile);
    }

    // ---- Helpers ----

    private function persist(MatrimonialProfileRequest $request, Member $member, ?MatrimonialProfile $profile): RedirectResponse
    {
        $data = $request->profileData();
        $familyId = null;
        if (! $profile && $request->filled('family')) {
            $familyId = $member->family()->where('ulid', $request->input('family'))->value('id');
        }

        try {
            $saved = $this->service->save(
                $member, $profile, $data,
                $request->file('photos', []),
                array_map('intval', $request->input('remove_photos', [])),
                $request->file('bio_data'),
                $request->boolean('remove_bio'),
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($familyId && ! $saved->family_member_id) {
            $saved->family_member_id = $familyId;
            $saved->save();
        }

        return redirect(localized_route('member.matrimonial.index'))->with('success', __('web.matrimonial.saved'));
    }

    private function eligibleMember(Request $request): Member
    {
        $member = $request->user('member');
        abort_unless($this->service->isEligible($member), 403, __('web.matrimonial.locked_text'));

        return $member;
    }

    private function ownOnly(MatrimonialProfile $profile, Member $member): void
    {
        // 404 rather than 403 so other members' profile IDs cannot be probed.
        abort_unless($profile->member_id === $member->id, 404);
    }

    private function prefillFromFamily(Member $member, string $ulid): array
    {
        if ($ulid === '') {
            return [];
        }
        $person = $member->family()->where('ulid', $ulid)->first();
        if (! $person) {
            return [];
        }
        $relation = match ($person->relation) {
            FamilyRelation::Son, FamilyRelation::Grandson => 'son',
            FamilyRelation::Daughter, FamilyRelation::Granddaughter => 'daughter',
            FamilyRelation::Brother => 'brother',
            FamilyRelation::Sister => 'sister',
            default => 'other',
        };

        return [
            'family' => $person->ulid,
            'relation' => $relation,
            'name' => $person->name,
            'gender' => in_array($person->gender?->value, ['male', 'female'], true) ? $person->gender->value : null,
            'dob' => $person->dob?->toDateString(),
            'marital_status' => $person->marital_status && $person->marital_status !== MaritalStatus::Married ? $person->marital_status->value : null,
            'education' => $person->education,
            'occupation' => $person->occupation,
            'father_name' => in_array($relation, ['son', 'daughter'], true) ? $member->name : null,
        ];
    }

    private function areas(?int $include)
    {
        return Area::where('is_active', true)->when($include, fn ($q) => $q->orWhere('id', $include))
            ->orderBy('sort_order')->get()->mapWithKeys(fn ($a) => [$a->id => $a->t('name')]);
    }
}
