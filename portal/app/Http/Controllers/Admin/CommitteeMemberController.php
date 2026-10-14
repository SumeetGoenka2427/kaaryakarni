<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Committee (About page leadership + Committee Members page). */
class CommitteeMemberController extends Controller
{
    use HandlesImageUpload;

    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $tier = $request->query('tier');
        $search = trim((string) $request->query('q', ''));

        $members = CommitteeMember::query()
            ->when(in_array($tier, array_keys(CommitteeMember::TIERS), true), fn ($q) => $q->where('tier', $tier))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name_en', 'like', "%{$search}%")
                ->orWhere('name_hi', 'like', "%{$search}%")->orWhere('designation_en', 'like', "%{$search}%")))
            ->orderByRaw("tier = 'leadership' desc")->orderBy('sort_order')->orderBy('name_en')
            ->paginate(25)->withQueryString();

        return view('admin.committee.index', [
            'members' => $members, 'tier' => $tier, 'search' => $search,
            'counts' => CommitteeMember::selectRaw('tier, count(*) c')->groupBy('tier')->pluck('c', 'tier'),
        ]);
    }

    public function create(): View
    {
        return view('admin.committee.form', ['member' => new CommitteeMember([
            'tier' => 'general', 'is_active' => true, 'sort_order' => (int) CommitteeMember::max('sort_order') + 1,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = $this->save($request, new CommitteeMember);
        $this->audit->record('committee.created', $member, "Added committee member {$member->name_en}");

        return redirect()->route('admin.committee.index')->with('success', "{$member->name_en} added to the committee.");
    }

    public function edit(CommitteeMember $committee): View
    {
        return view('admin.committee.form', ['member' => $committee]);
    }

    public function update(Request $request, CommitteeMember $committee): RedirectResponse
    {
        $old = $committee->only(['name_en', 'designation_en', 'tier', 'is_active']);
        $this->save($request, $committee);
        $this->audit->record('committee.updated', $committee, "Updated committee member {$committee->name_en}", $old, $committee->only(array_keys($old)));

        return redirect()->route('admin.committee.index')->with('success', "{$committee->name_en} updated.");
    }

    public function destroy(CommitteeMember $committee, ImageService $images): RedirectResponse
    {
        $images->delete($committee->photo);
        $committee->delete();
        $this->audit->record('committee.deleted', null, "Removed committee member {$committee->name_en}");

        return redirect()->route('admin.committee.index')->with('success', "{$committee->name_en} removed.");
    }

    private function save(Request $request, CommitteeMember $member): CommitteeMember
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:120'],
            'name_hi' => ['nullable', 'string', 'max:120'],
            'designation_en' => ['required', 'string', 'max:120'],
            'designation_hi' => ['nullable', 'string', 'max:120'],
            'tier' => ['required', Rule::in(array_keys(CommitteeMember::TIERS))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'photo' => ImageService::rules(),
        ]);

        $member->fill(collect($data)->except('photo')->all());
        $member->sort_order = (int) ($data['sort_order'] ?? 0);
        $member->is_active = $request->boolean('is_active');
        $this->syncImage($request, $member, 'photo', 'committee', [192], 1, 'photo');
        $member->save();

        return $member;
    }
}
