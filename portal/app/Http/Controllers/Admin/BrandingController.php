<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditLogger;
use App\Services\Branding\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(BrandingService $branding): View
    {
        return view('admin.branding.edit', [
            'types' => BrandingService::TYPES,
            'assets' => $branding->all(),
        ]);
    }

    public function update(Request $request, string $type, BrandingService $branding, AuditLogger $audit): RedirectResponse
    {
        abort_unless(array_key_exists($type, BrandingService::TYPES), 404);

        $request->validate(['file' => BrandingService::rules()], [], ['file' => BrandingService::TYPES[$type]['label']]);

        $asset = $branding->store($type, $request->file('file'));
        $audit->record('branding.updated', $asset, BrandingService::TYPES[$type]['label'].' replaced (v'.$asset->version.')');

        return back()->with('success', BrandingService::TYPES[$type]['label'].' updated. It now appears across the whole site.');
    }

    public function destroy(Request $request, BrandingService $branding, AuditLogger $audit): RedirectResponse
    {
        $type = $request->validate(['type' => ['required', Rule::in(array_keys(BrandingService::TYPES))]])['type'];

        if ($asset = $branding->remove($type)) {
            $audit->record('branding.removed', null, BrandingService::TYPES[$type]['label'].' removed (default restored)', ['path' => $asset->path]);
        }

        return back()->with('success', BrandingService::TYPES[$type]['label'].' removed. The default is shown again.');
    }
}
