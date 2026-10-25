<?php

namespace App\Services\Reports;

use App\Enums\BusinessStatus;
use App\Enums\MatrimonialStatus;
use App\Enums\MemberStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Models\BusinessProfile;
use App\Models\ContactMessage;
use App\Models\MatrimonialProfile;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

/** "Waiting for action" counts shown as badges in the admin sidebar (cached briefly). */
class AdminNavBadges
{
    public function all(): array
    {
        return Cache::remember('admin.nav.badges', 60, fn () => [
            'members' => Member::where('status', MemberStatus::UnderVerification)->count(),
            'matrimonial' => MatrimonialProfile::where('status', MatrimonialStatus::Pending)->count(),
            'businesses' => BusinessProfile::where('status', BusinessStatus::Pending)->count(),
            'payments' => Payment::where('mode', PaymentMode::Offline)->where('status', PaymentStatus::Submitted)->count(),
            'messages' => ContactMessage::where('status', 'new')->count(),
        ]);
    }
}
