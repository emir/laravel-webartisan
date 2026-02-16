<?php

namespace Emir\Webartisan;

use Emir\Webartisan\Console\InstallCommand;
use Illuminate\Support\ServiceProvider;

class WebartisanServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webartisan.php', 'webartisan');
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerViews();
        $this->registerRoutes();
        $this->registerCommands();
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/webartisan.php' => config_path('webartisan.php'),
        ], 'webartisan-config');

        $this->publishes([
            __DIR__.'/../assets' => public_path('vendor/webartisan'),
        ], 'webartisan-assets');

        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/webartisan'),
        ], 'webartisan-views');
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/views', 'webartisan');
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        if (! Webartisan::isEnabled()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Webartisan::routesRegistered();
    }

    /**
     * Register the package artisan commands.
     */
    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }
}
