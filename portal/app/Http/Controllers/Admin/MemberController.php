<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Member;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use App\Services\Members\MemberService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Admin → Members (admin/members.html): search, view, edit, verify, reject, suspend, export. */
class MemberController extends Controller
{
    use HandlesImageUpload;

    public function __construct(
        private readonly MemberService $members,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $members = $this->filtered($request)->with('area:id,name_en')->latest('id')->paginate(25)->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'areas' => Area::orderBy('sort_order')->pluck('name_en', 'id'),
            'statusCounts' => Member::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'filters' => $request->only(['q', 'area', 'status', 'from', 'to']),
        ]);
    }

    /** Streams a CSV of the current filter (low memory, any size). */
    public function export(Request $request): StreamedResponse
    {
        $this->audit->record('members.exported', null, 'Exported members CSV', [], $request->only(['q', 'area', 'status', 'from', 'to']));

        return response()->streamDownload(function () use ($request) {
            // CSV with BOM so Excel shows Hindi names correctly.
            $writer = SimpleExcelWriter::create('php://output', 'csv');
            $this->filtered($request)->with('area:id,name_en')->withCount('family')->orderBy('id')->lazy(500)
                ->each(fn (Member $m) => $writer->addRow([
                    'Membership ID' => $m->membership_no,
                    'Name' => $m->name,
                    'Father / Husband' => $m->father_or_husband_name,
                    'Mobile' => $m->mobile,
                    'Email' => $m->email,
                    'Gender' => $m->gender?->label(),
                    'Date of Birth' => $m->dob?->format('d-m-Y'),
                    'Area' => $m->area?->name_en,
                    'Address' => $m->address,
                    'Status' => $m->status->label(),
                    'Family Members' => $m->family_count,
                    'Registered On' => $m->created_at->format('d-m-Y'),
                    'Verified On' => $m->verified_at?->format('d-m-Y'),
                ]));
            $writer->close();
        }, 'members-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(Member $member): View
    {
        $member->load(['area', 'family', 'verifications.admin:id,name']);

        return view('admin.members.show', compact('member'));
    }

    public function create(): View
    {
        return view('admin.members.form', ['member' => new Member, 'areas' => $this->areaOptions(null)]);
    }

    /** Manual registration by admin (e.g. offline paper form). Starts Under Verification. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(null));

        $member = new Member(collect($data)->except('photo')->all());
        $member->mobile = $data['mobile'];
        $member->status = MemberStatus::UnderVerification;
        $member->profile_submitted_at = now();
        $this->syncImage($request, $member, 'photo', 'members', [160, 400], 1, 'photo', 'private');
        $member->save();

        $this->members->recordSubmitted($member);
        $this->audit->record('member.created', $member, "Admin registered member {$member->name} ({$member->mobile})");

        return redirect()->route('admin.members.show', $member)->with('success', 'Member registered and placed under verification.');
    }

    public function edit(Member $member): View
    {
        return view('admin.members.form', ['member' => $member, 'areas' => $this->areaOptions($member->area_id)]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate($this->rules($member));
        $tracked = ['name', 'mobile', 'email', 'area_id', 'address'];
        $old = $member->only($tracked);

        $member->fill(collect($data)->except(['photo', 'mobile'])->all());
        $member->mobile = $data['mobile'];
        $this->syncImage($request, $member, 'photo', 'members', [160, 400], 1, 'photo', 'private');
        $member->save();

        $this->audit->record('member.updated', $member, "Edited member {$member->name}", $old, $member->only($tracked));

        return redirect()->route('admin.members.show', $member)->with('success', 'Member details saved.');
    }

    public function verify(Member $member): RedirectResponse
    {
        return $this->act(fn () => $this->members->verify($member), "{$member->name} verified.");
    }

    public function reject(Request $request, Member $member): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];

        return $this->act(fn () => $this->members->reject($member, $reason), "{$member->name} rejected.");
    }

    public function suspend(Request $request, Member $member): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];

        return $this->act(fn () => $this->members->suspend($member, $reason), "{$member->name} suspended.");
    }

    public function reinstate(Member $member): RedirectResponse
    {
        return $this->act(fn () => $this->members->reinstate($member), "{$member->name} reinstated.");
    }

    private function act(callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $success);
    }

    private function filtered(Request $request): Builder
    {
        $q = trim((string) $request->query('q', ''));
        $status = MemberStatus::tryFrom((string) $request->query('status'));

        return Member::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                ->orWhere('mobile', 'like', "%{$q}%")->orWhere('membership_no', 'like', "%{$q}%")))
            ->when($request->integer('area'), fn ($query, $area) => $query->where('area_id', $area))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($request->date('from'), fn ($query, $from) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($query, $to) => $query->where('created_at', '<=', $to->endOfDay()));
    }

    private function rules(?Member $member): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'father_or_husband_name' => ['nullable', 'string', 'max:120'],
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/', Rule::unique('members', 'mobile')->ignore($member?->id)],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'area_id' => ['required', 'integer', Rule::exists('areas', 'id')],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ImageService::rules(),
        ];
    }

    private function areaOptions(?int $currentId)
    {
        return Area::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $currentId))
            ->orderBy('sort_order')->pluck('name_en', 'id');
    }
}
