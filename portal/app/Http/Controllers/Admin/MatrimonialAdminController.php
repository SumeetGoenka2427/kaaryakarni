<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MatrimonialStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MatrimonialProfileRequest;
use App\Models\Area;
use App\Models\MatrimonialProfile;
use App\Models\MatrimonialReport;
use App\Services\Matrimonial\MatrimonialService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin → Matrimonial (admin/matrimonial.html): review, approve, reject, hide, suspend, edit, delete + member reports. */
class MatrimonialAdminController extends Controller
{
    public function __construct(private readonly MatrimonialService $service) {}

    public function index(Request $request): View
    {
        $tab = (string) $request->query('status', 'pending');
        $q = trim((string) $request->query('q', ''));
        $openReports = MatrimonialReport::where('status', 'open')->count();

        if ($tab === 'reports') {
            $reports = MatrimonialReport::with(['profile:id,ulid,name,status,deleted_at', 'reporter:id,ulid,name,mobile'])
                ->orderByRaw("status = 'open' desc")->latest('id')->paginate(20)->withQueryString();
        } else {
            $status = MatrimonialStatus::tryFrom($tab) ?? MatrimonialStatus::Pending;
            $tab = $status->value;
            $profiles = MatrimonialProfile::with(['area:id,name_en', 'member:id,ulid,name,mobile'])
                ->withCount(['reports as open_reports_count' => fn ($r) => $r->where('status', 'open')])
                ->where('status', $status)
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', '%'.addcslashes($q, '%_\\').'%')
                    ->orWhereHas('area', fn ($a) => $a->where('name_en', 'like', '%'.addcslashes($q, '%_\\').'%'))))
                ->orderByDesc('submitted_at')->paginate(20)->withQueryString();
        }

        return view('admin.matrimonial.index', [
            'profiles' => $profiles ?? null,
            'reports' => $reports ?? null,
            'current' => $tab,
            'counts' => MatrimonialProfile::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'openReports' => $openReports,
            'q' => $q,
        ]);
    }

    public function show(MatrimonialProfile $profile): View
    {
        return view('admin.matrimonial.show', [
            'profile' => $profile->load(['area', 'member', 'photos', 'reports.reporter']),
        ]);
    }

    public function edit(MatrimonialProfile $profile): View
    {
        return view('admin.matrimonial.edit', [
            'profile' => $profile,
            'areas' => Area::orderBy('sort_order')->pluck('name_en', 'id'),
        ]);
    }

    public function update(MatrimonialProfileRequest $request, MatrimonialProfile $profile): RedirectResponse
    {
        $this->service->adminUpdate($profile, $request->profileData());

        return redirect()->route('admin.matrimonial.show', $profile)->with('success', 'Profile saved.');
    }

    public function approve(MatrimonialProfile $profile): RedirectResponse
    {
        return $this->act(fn () => $this->service->approve($profile), "“{$profile->name}” approved. Eligible members can now see it.");
    }

    public function reject(Request $request, MatrimonialProfile $profile): RedirectResponse
    {
        $note = $request->validate(['note' => ['required', 'string', 'min:5', 'max:500']])['note'];

        return $this->act(fn () => $this->service->reject($profile, $note), "“{$profile->name}” rejected.");
    }

    public function hide(MatrimonialProfile $profile): RedirectResponse
    {
        return $this->act(fn () => $this->service->hide($profile), "“{$profile->name}” hidden from members.");
    }

    public function suspend(Request $request, MatrimonialProfile $profile): RedirectResponse
    {
        $note = $request->validate(['note' => ['required', 'string', 'min:5', 'max:500']])['note'];

        return $this->act(fn () => $this->service->suspend($profile, $note), "“{$profile->name}” suspended.");
    }

    public function destroy(MatrimonialProfile $profile): RedirectResponse
    {
        $this->service->adminDelete($profile);

        return redirect()->route('admin.matrimonial.index')->with('success', "“{$profile->name}” deleted.");
    }

    public function resolveReport(MatrimonialReport $report): RedirectResponse
    {
        $this->service->resolveReport($report);

        return back()->with('success', 'Report marked as resolved.');
    }

    private function act(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}
