<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out a member whose account was suspended or removed by admin while
 * they were logged in.
 */
class EnsureMemberIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = Auth::guard('member')->user();

        if ($member && ($member->isSuspended() || $member->trashed())) {
            Auth::guard('member')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect(localized_route('login'))->withErrors(['mobile' => __('web.auth.account_suspended')]);
        }

        return $next($request);
    }
}
