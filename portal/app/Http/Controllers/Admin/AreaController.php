<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Jaipur Areas. These populate the Area / Locality dropdown in
 * registration and profiles. Areas in use are deactivated, never deleted.
 */
class AreaController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        return view('admin.areas.index', [
            'areas' => Area::withCount('members')->orderBy('sort_order')->orderBy('name_en')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $area = Area::create($data + ['is_active' => true, 'sort_order' => (int) Area::max('sort_order') + 1]);
        $this->audit->record('area.created', $area, "Added area {$area->name_en}");

        return back()->with('success', "Area “{$area->name_en}” added.");
    }

    public function edit(Area $area): View
    {
        return view('admin.areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area): RedirectResponse
    {
        $old = $area->only(['name_en', 'name_hi']);
        $area->fill($this->validated($request, $area));
        $area->is_sample = false; // edited by admin → real data now
        $area->save();
        $this->audit->record('area.updated', $area, "Updated area {$area->name_en}", $old, $area->only(['name_en', 'name_hi']));

        return redirect()->route('admin.areas.index')->with('success', "Area “{$area->name_en}” updated.");
    }

    public function toggle(Area $area): RedirectResponse
    {
        $area->is_active = ! $area->is_active;
        $area->save();
        $this->audit->record($area->is_active ? 'area.activated' : 'area.deactivated', $area, $area->name_en);

        return back()->with('success', "“{$area->name_en}” ".($area->is_active ? 'activated' : 'deactivated — it is no longer offered in forms').'.');
    }

    /** Move an area one place up or down in the dropdown order. */
    public function move(Request $request, Area $area): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        DB::transaction(function () use ($area, $direction) {
            $ordered = Area::orderBy('sort_order')->orderBy('name_en')->lockForUpdate()->get()->values();
            $index = $ordered->search(fn ($a) => $a->id === $area->id);
            $swap = $direction === 'up' ? $index - 1 : $index + 1;
            if ($swap < 0 || $swap >= $ordered->count()) {
                return;
            }
            [$ordered[$index], $ordered[$swap]] = [$ordered[$swap], $ordered[$index]];
            foreach ($ordered as $position => $item) {
                if ($item->sort_order !== $position + 1) {
                    $item->sort_order = $position + 1;
                    $item->save();
                }
            }
        });

        return back();
    }

    public function destroy(Area $area): RedirectResponse
    {
        if ($area->members()->withTrashed()->exists()) {
            return back()->with('error', "“{$area->name_en}” is used by members and cannot be deleted. Deactivate it instead.");
        }

        $area->delete();
        $this->audit->record('area.deleted', null, "Deleted area {$area->name_en}");

        return back()->with('success', "Area “{$area->name_en}” deleted.");
    }

    private function validated(Request $request, ?Area $area = null): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:100', Rule::unique('areas', 'name_en')->ignore($area?->id)],
            'name_hi' => ['nullable', 'string', 'max:100'],
        ], [], ['name_en' => 'area name (English)', 'name_hi' => 'area name (Hindi)']);
    }
}
