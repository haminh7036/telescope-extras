<?php

namespace Haminh7036\TelescopeExtras;

use Haminh7036\TelescopeExtras\Http\Middleware\InjectAssets;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TelescopeExtrasServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     *
     * Telescope (re)defines its "telescope" middleware group while booting, so
     * the push waits until every provider has booted.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->app->make(Router::class)->pushMiddlewareToGroup('telescope', InjectAssets::class);
        });
    }
}
