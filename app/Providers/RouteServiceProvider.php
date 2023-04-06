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
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
     protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->map();

    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

    public function map()
    {
        // ... older functions
        $this->mapApiV1UserRoutes();
        $this->mapApiV2UserRoutes();
        $this->mapApiV1RestaurantRoutes();
        $this->mapApiV2RestaurantRoutes();
        $this->mapWebRoutes();
    }

    // And new function

    protected function mapWebRoutes()
    {
        Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
    }
    protected function mapApiV1UserRoutes()
    {
        Route::prefix('api/v1')
            // ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/user/api_v1.php'));

    }
    protected function mapApiV1RestaurantRoutes(){
        Route::prefix('api/restaurant/v1')
        // ->middleware('api')
        ->namespace($this->namespace)
        ->group(base_path('routes/restaurant/api_v1.php'));

    }

   protected function mapApiV2UserRoutes()
    {
        Route::prefix('api/v2')
            // ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/user/api_v2.php'));

    }
    protected function mapApiV2RestaurantRoutes(){
        Route::prefix('api/restaurant/v2')
        // ->middleware('api')
        ->namespace($this->namespace)
        ->group(base_path('routes/restaurant/api_v2.php'));

    }
}
