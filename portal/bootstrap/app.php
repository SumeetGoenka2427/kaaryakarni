<?php

use App\Http\Middleware\AdminIdleTimeout;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Member area (/member and /hi/member) — guarded in P04.
            Route::middleware('web')->group(base_path('routes/member.php'));

            // Admin panel (/admin) — English UI, separate admin guard.
            Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));

            // Payment gateway webhooks: no session/CSRF, signature-verified instead.
            Route::prefix('webhooks')->name('webhooks.')->middleware('throttle:60,1')->group(base_path('routes/webhooks.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SecurityHeaders::class]);

        $middleware->alias([
            'setlocale' => SetLocale::class,
            'admin.idle' => AdminIdleTimeout::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);

        // Guests hitting a protected page go to the matching login screen.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : (Route::has('login') ? route('login') : route('home')));
        $middleware->redirectUsersTo(fn ($request) => $request->is('admin', 'admin/*')
            ? route('admin.dashboard')
            : route('home'));

        // The language cookie only holds "en"/"hi"; no need to encrypt it.
        $middleware->encryptCookies(except: [SetLocale::COOKIE]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
