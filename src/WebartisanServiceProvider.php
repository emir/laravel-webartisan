<?php

namespace Emir\Webartisan;

use Illuminate\Support\ServiceProvider;

class WebartisanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../assets' => public_path('emir/webartisan'),
        ], 'public');

        $this->loadViewsFrom(__DIR__.'/views', 'webartisan');

        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../routes.php';
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
