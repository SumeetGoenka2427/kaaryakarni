<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

/**
 * Admin landing page. Module statistics (members, donations, approvals) are
 * added here as each module is built; the full dashboard is P12.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $recentActions = AuditLog::with('admin:id,name')->latest('created_at')->limit(8)->get();

        return view('admin.dashboard', compact('recentActions'));
    }
}
