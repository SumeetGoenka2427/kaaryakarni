<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 900;

    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $key = 'admin-login:'.Str::lower($credentials['username']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            throw ValidationException::withMessages([
                'username' => "Too many login attempts. Please try again in {$minutes} minute(s).",
            ]);
        }

        $ok = Auth::guard('admin')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'is_active' => true,
        ]);

        if (! $ok) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);
            // Same message for unknown user, wrong password or inactive account.
            throw ValidationException::withMessages(['username' => 'Invalid username or password.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        /** @var AdminUser $admin */
        $admin = Auth::guard('admin')->user();
        $admin->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        $audit->record('admin.login', $admin, "{$admin->username} logged in");

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request, AuditLogger $audit): RedirectResponse
    {
        if ($admin = Auth::guard('admin')->user()) {
            $audit->record('admin.logout', $admin, "{$admin->username} logged out");
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been logged out.');
    }
}
