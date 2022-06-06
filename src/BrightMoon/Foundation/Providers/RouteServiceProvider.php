<?php

namespace BrightMoon\Foundation\Providers;

use BrightMoon\Http\Request;
use BrightMoon\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Khởi chạy nhúng các route đã đăng ký.
     *
     * @return mixed
     */
    public function boot()
    {
        $this->loadRoutes();
    }

    /**
     * Load các file route.
     *
     * @return void
     */
    public function loadRoutes()
    {
        $routesRegistered = Route::getListRouteRegisted();

        foreach ($routesRegistered as $route) {
            if (!in_array($route, ['.', '..'])) {
                require $route;
            }
        }

        Route::run($this->app->make(Request::class));
    }
}
