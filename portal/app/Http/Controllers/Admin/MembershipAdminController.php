<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipStatus;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use App\Services\Settings\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin → Memberships (admin/memberships.html): lifetime membership overview,
 * the one-time fee, member membership statuses and membership payments
 * awaiting verification. No renewal / expiry anywhere.
 */
class MembershipAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = MembershipStatus::tryFrom((string) $request->query('status'));

        $memberships = Membership::with(['member:id,ulid,name,mobile,membership_no,status', 'payment:id,ulid,mode,amount,paid_at'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('updated_at')->paginate(20)->withQueryString();

        return view('admin.memberships.index', [
            'memberships' => $memberships,
            'status' => $status,
            'counts' => Membership::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'pendingPayments' => Payment::where('purpose', PaymentPurpose::Membership)->where('status', PaymentStatus::Submitted)
                ->with('member:id,ulid,name,mobile')->oldest()->limit(50)->get(),
            'fee' => (int) setting('membership_fee'),
        ]);
    }

    public function updateFee(Request $request, SettingsService $settings, AuditLogger $audit): RedirectResponse
    {
        $fee = (int) $request->validate(['membership_fee' => ['required', 'integer', 'min:0', 'max:1000000']])['membership_fee'];

        [$old, $new] = $settings->update(['membership_fee' => $fee]);
        if ($new) {
            $audit->record('settings.updated', null, 'Lifetime membership fee changed', $old, $new);
        }

        return back()->with('success', 'Membership fee saved. It applies to members who have not paid yet; lifetime members are never charged again.');
    }
}
