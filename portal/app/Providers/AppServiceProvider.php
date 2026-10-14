<?php

namespace App\Providers;

use App\Models\AdminUser;
use App\Services\Audit\AuditLogger;
use App\Services\Branding\BrandingService;
use App\Services\Settings\SettingsService;
use App\Translation\DatabaseOverrideLoader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;
use ReflectionClass;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(BrandingService::class);
        $this->app->singleton(AuditLogger::class);

        // Admin-edited site texts override the lang/ files (Admin → Site Texts).
        $this->app->extend('translation.loader', function (FileLoader $loader, $app) {
            $frameworkLang = dirname((new ReflectionClass(FileLoader::class))->getFileName()).'/lang';

            return new DatabaseOverrideLoader($app['files'], [$frameworkLang, $app['path.lang']]);
        });
    }

    public function boot(): void
    {
        $this->guardProductionConfig();

        // Catch N+1 queries, silently discarded attributes and missing
        // attributes during development; never throw in production.
        Model::shouldBeStrict(! $this->app->isProduction());

        $this->registerLocalizedRouteMacro();

        // Super Admin passes every permission check; everyone else needs the permission.
        Gate::before(fn ($user) => $user instanceof AdminUser && $user->isSuperAdmin() ? true : null);
    }

    /**
     * Route::localized(fn () => ...) registers the given routes once per
     * website language: English without a prefix (route name "home") and
     * every other language under its prefix (route name "hi.home").
     */
    private function registerLocalizedRouteMacro(): void
    {
        Route::macro('localized', function (callable $routes) {
            $locales = array_keys(config('portal.locales'));
            $default = array_shift($locales);

            Route::middleware('setlocale')->group($routes);

            foreach ($locales as $locale) {
                Route::prefix($locale)->name("{$locale}.")->middleware('setlocale')->group($routes);
            }
        });
    }

    /**
     * Refuse to run in production with development-only settings.
     */
    private function guardProductionConfig(): void
    {
        if (! $this->app->isProduction() || $this->app->runningUnitTests()) {
            return;
        }

        if (config('portal.sms.driver') === 'log') {
            throw new RuntimeException('SMS_DRIVER=log is for local development only. Configure a real SMS provider.');
        }

        if (config('app.debug')) {
            throw new RuntimeException('APP_DEBUG must be false in production.');
        }
    }
}
