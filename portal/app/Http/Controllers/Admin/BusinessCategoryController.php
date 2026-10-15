<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Business Categories (EN/HI). Categories in use are deactivated, never deleted. */
class BusinessCategoryController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        return view('admin.businesses.categories', [
            'categories' => BusinessCategory::withCount('businesses')->orderBy('sort_order')->orderBy('name_en')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $category = new BusinessCategory($data);
        $category->slug = Str::slug($data['name_en']) ?: Str::lower(Str::random(6));
        $category->is_active = true;
        $category->sort_order = (int) BusinessCategory::max('sort_order') + 1;
        $category->save();
        $this->done('business_category.created', $category);

        return back()->with('success', "Category “{$category->name_en}” added.");
    }

    public function update(Request $request, BusinessCategory $category): RedirectResponse
    {
        $category->fill($this->validated($request, $category));
        $category->sort_order = $request->integer('sort_order', $category->sort_order);
        $category->save();
        $this->done('business_category.updated', $category);

        return back()->with('success', "Category “{$category->name_en}” saved.");
    }

    public function toggle(BusinessCategory $category): RedirectResponse
    {
        $category->is_active = ! $category->is_active;
        $category->save();
        $this->done($category->is_active ? 'business_category.activated' : 'business_category.deactivated', $category);

        return back()->with('success', "“{$category->name_en}” ".($category->is_active ? 'activated.' : 'deactivated.'));
    }

    public function destroy(BusinessCategory $category): RedirectResponse
    {
        if ($category->businesses()->withTrashed()->exists()) {
            return back()->with('error', "“{$category->name_en}” is used by listings. Deactivate it instead.");
        }
        $category->delete();
        $this->done('business_category.deleted', null, $category->name_en);

        return back()->with('success', 'Category deleted.');
    }

    private function validated(Request $request, ?BusinessCategory $category = null): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:100', Rule::unique('business_categories', 'name_en')->ignore($category?->id)],
            'name_hi' => ['nullable', 'string', 'max:100'],
        ]);
    }

    private function done(string $action, ?BusinessCategory $category, ?string $name = null): void
    {
        Cache::forget('business.categories.active');
        $this->audit->record($action, $category, $name ?? $category?->name_en);
    }
}
