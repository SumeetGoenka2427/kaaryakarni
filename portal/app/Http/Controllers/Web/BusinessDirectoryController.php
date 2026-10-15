<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\BusinessCategory;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Public Business Directory (web/business-directory.html, business-detail.html). Approved listings only. */
class BusinessDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $category = $this->categories()->firstWhere('slug', $request->query('category'));
        $areaId = $request->integer('area') ?: null;

        $businesses = BusinessProfile::approved()
            ->with(['category:id,name_en,name_hi', 'area:id,name_en,name_hi'])
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->when($q !== '', function ($query) use ($q) {
                // FULLTEXT for real words, LIKE for short terms (MySQL ignores words under 3-4 chars).
                mb_strlen($q) >= 4
                    ? $query->where(fn ($w) => $w->whereFullText(['name', 'description'], $q)->orWhere('name', 'like', "%{$q}%"))
                    : $query->where('name', 'like', "%{$q}%");
            })
            ->orderByDesc('is_featured')->orderBy('name')
            ->paginate(12)->withQueryString();

        return view('web.business.index', [
            'businesses' => $businesses,
            'categories' => $this->categories(),
            'areas' => Area::activeList(),
            'filters' => ['q' => $q, 'category' => $category?->slug, 'area' => $areaId],
        ]);
    }

    public function show(BusinessProfile $business): View|Response
    {
        if (! $business->isPublic()) {
            return response()->view('web.business.not-found', [], 404);
        }

        $business->load(['category', 'area', 'member:id,name']);

        $related = BusinessProfile::approved()->whereKeyNot($business->id)
            ->where(fn ($q) => $q->where('category_id', $business->category_id)->orWhere('area_id', $business->area_id))
            ->with(['category:id,name_en,name_hi', 'area:id,name_en,name_hi'])
            ->orderByDesc('is_featured')->inRandomOrder()->limit(3)->get();

        return view('web.business.show', compact('business', 'related'));
    }

    private function categories()
    {
        return Cache::remember('business.categories.active', 3600, fn () => BusinessCategory::where('is_active', true)
            ->orderBy('sort_order')->orderBy('name_en')->get(['id', 'slug', 'name_en', 'name_hi']));
    }
}
