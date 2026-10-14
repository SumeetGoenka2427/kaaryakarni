<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\Audit\AuditLogger;
use App\Services\Settings\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(SettingsService $settings): View
    {
        return view('admin.settings.edit', ['s' => $settings->all()]);
    }

    public function update(UpdateSettingsRequest $request, SettingsService $settings, AuditLogger $audit): RedirectResponse
    {
        [$old, $new] = $settings->update($request->settings());

        if ($new) {
            $audit->record('settings.updated', null, 'Updated: '.implode(', ', array_keys($new)), $old, $new);
        }

        return back()->with('success', $new ? 'Settings saved.' : 'No changes to save.');
    }
}
