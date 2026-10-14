<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs an admin out after ADMIN_IDLE_TIMEOUT minutes without activity, and
 * blocks an admin account that was deactivated while still signed in.
 */
class AdminIdleTimeout
{
    private const SESSION_KEY = 'admin_last_activity';

    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            $last = (int) $request->session()->get(self::SESSION_KEY, time());
            $expired = time() - $last > config('portal.admin.idle_timeout') * 60;

            if ($expired || ! $admin->is_active) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')->with('warning', $expired
                    ? 'You were logged out after a period of inactivity. Please log in again.'
                    : 'Your admin account is inactive.');
            }

            $request->session()->put(self::SESSION_KEY, time());
        }

        return $next($request);
    }
}
