<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Load platform core routes
            Route::middleware('web')
                ->group(base_path('platform/core/acl/routes/web.php'));
            Route::middleware('web')
                ->group(base_path('platform/core/base/routes/web.php'));
            Route::middleware('web')
                ->group(base_path('platform/core/setting/routes/web.php'));
            Route::middleware('web')
                ->group(base_path('platform/core/media/routes/web.php'));
            Route::middleware('web')
                ->group(base_path('platform/core/dashboard/routes/web.php'));
            Route::middleware('web')
                ->group(base_path('platform/core/table/routes/web.php'));

            // Load platform package routes
            if (is_dir(base_path('platform/packages'))) {
                foreach (glob(base_path('platform/packages/*/routes/web.php')) as $routeFile) {
                    Route::middleware('web')->group($routeFile);
                }
            }
        });
    }
}
