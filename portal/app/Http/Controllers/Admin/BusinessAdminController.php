<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessProfileRequest;
use App\Models\Area;
use App\Models\BusinessCategory;
use App\Models\BusinessProfile;
use App\Services\Business\BusinessService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin → Business Directory (admin/businesses.html): review, approve, reject, feature, hide, edit, delete. */
class BusinessAdminController extends Controller
{
    public function __construct(private readonly BusinessService $service) {}

    public function index(Request $request): View
    {
        $status = BusinessStatus::tryFrom((string) $request->query('status', 'pending'));
        $featured = $request->query('status') === 'featured';
        $q = trim((string) $request->query('q', ''));

        $businesses = BusinessProfile::with(['category:id,name_en', 'area:id,name_en', 'member:id,ulid,name,mobile'])
            ->when($featured, fn ($query) => $query->where('is_featured', true), fn ($query) => $query->when($status, fn ($s) => $s->where('status', $status)))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                ->orWhereHas('category', fn ($c) => $c->where('name_en', 'like', "%{$q}%"))
                ->orWhereHas('area', fn ($a) => $a->where('name_en', 'like', "%{$q}%"))))
            ->orderByDesc('submitted_at')->paginate(20)->withQueryString();

        return view('admin.businesses.index', [
            'businesses' => $businesses,
            'current' => $featured ? 'featured' : ($status?->value ?? 'pending'),
            'counts' => BusinessProfile::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'featuredCount' => BusinessProfile::where('is_featured', true)->count(),
            'q' => $q,
        ]);
    }

    public function show(BusinessProfile $business): View
    {
        return view('admin.businesses.show', ['business' => $business->load(['category', 'area', 'member'])]);
    }

    public function edit(BusinessProfile $business): View
    {
        return view('admin.businesses.edit', [
            'business' => $business,
            'categories' => BusinessCategory::orderBy('sort_order')->pluck('name_en', 'id'),
            'areas' => Area::orderBy('sort_order')->pluck('name_en', 'id'),
        ]);
    }

    public function update(BusinessProfileRequest $request, BusinessProfile $business): RedirectResponse
    {
        $this->service->saveByAdmin($business, $request->listingData(), $request->file('logo'), $request->boolean('remove_logo'));

        return redirect()->route('admin.businesses.show', $business)->with('success', 'Listing saved.');
    }

    public function approve(BusinessProfile $business): RedirectResponse
    {
        return $this->act(fn () => $this->service->approve($business), "“{$business->name}” approved and published.");
    }

    public function reject(Request $request, BusinessProfile $business): RedirectResponse
    {
        $note = $request->validate(['note' => ['required', 'string', 'min:5', 'max:500']])['note'];

        return $this->act(fn () => $this->service->reject($business, $note), "“{$business->name}” rejected.");
    }

    public function requestChanges(Request $request, BusinessProfile $business): RedirectResponse
    {
        $note = $request->validate(['note' => ['required', 'string', 'min:5', 'max:500']])['note'];

        return $this->act(fn () => $this->service->requestChanges($business, $note), "Changes requested from the owner of “{$business->name}”.");
    }

    public function hide(BusinessProfile $business): RedirectResponse
    {
        return $this->act(fn () => $this->service->hide($business), "“{$business->name}” hidden from the directory.");
    }

    public function feature(BusinessProfile $business): RedirectResponse
    {
        return $this->act(fn () => $this->service->toggleFeatured($business), 'Featured setting updated.');
    }

    public function destroy(BusinessProfile $business): RedirectResponse
    {
        $this->service->delete($business);

        return redirect()->route('admin.businesses.index')->with('success', "“{$business->name}” deleted.");
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
