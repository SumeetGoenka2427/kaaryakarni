<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Donation;
use App\Models\Member;
use App\Services\Reports\DashboardStats;
use Illuminate\View\View;

/** Admin → Dashboard (admin/index.html): live snapshot of members, approvals and donations. */
class DashboardController extends Controller
{
    public function __invoke(DashboardStats $stats): View
    {
        $admin = auth('admin')->user();

        return view('admin.dashboard', [
            'counts' => $stats->counts(),
            'online' => $admin->can('payments.view') ? $stats->onlinePayments() : [],
            'campaigns' => $admin->can('donations.view') ? $stats->campaigns() : collect(),
            'recentMembers' => $admin->can('members.view')
                ? Member::with('area:id,name_en')->latest('id')->limit(6)->get(['id', 'ulid', 'name', 'membership_no', 'area_id', 'status'])
                : collect(),
            'recentDonations' => $admin->can('donations.view')
                ? Donation::with('campaign:id,title_en')->latest('id')->limit(6)->get(['id', 'campaign_id', 'donor_name', 'amount', 'status'])
                : collect(),
            'recentActions' => $admin->can('audit.view')
                ? AuditLog::with('admin:id,name')->where('action', '!=', 'admin.login')->latest('id')->limit(6)->get()
                : collect(),
        ]);
    }
}
