<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\Membership\MembershipService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, MembershipService $memberships): View
    {
        $member = $request->user('member')->load('area');
        $familyCount = $member->family()->count();
        $membership = $memberships->forMember($member);

        return view('member.dashboard', compact('member', 'familyCount', 'membership'));
    }
}
