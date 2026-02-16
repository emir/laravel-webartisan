<?php

namespace Emir\Webartisan\Tests\Feature;

use Emir\Webartisan\Tests\TestCase;
use Emir\Webartisan\Webartisan;
use Emir\Webartisan\WebartisanServiceProvider;
use Illuminate\Support\ServiceProvider;

class ServiceProviderTest extends TestCase
{
    // =========================================================================
    // Provider registration
    // =========================================================================

    public function test_service_provider_is_registered(): void
    {
        $this->assertArrayHasKey(
            WebartisanServiceProvider::class,
            $this->app->getLoadedProviders()
        );
    }

    // =========================================================================
    // Config
    // =========================================================================

    public function test_config_is_merged(): void
    {
        $config = config('webartisan');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('enabled_environments', $config);
        $this->assertArrayHasKey('route_prefix', $config);
        $this->assertArrayHasKey('middleware', $config);
        $this->assertArrayHasKey('gate', $config);
        $this->assertArrayHasKey('allowed_commands', $config);
        $this->assertArrayHasKey('blocked_commands', $config);
        $this->assertArrayHasKey('theme', $config);
        $this->assertArrayHasKey('domain', $config);
    }

    // =========================================================================
    // Views
    // =========================================================================

    public function test_views_are_registered(): void
    {
        $this->assertTrue(
            view()->exists('webartisan::index')
        );
    }

    public function test_view_namespace_is_webartisan(): void
    {
        $finder = view()->getFinder();
        $hints = $finder->getHints();

        $this->assertArrayHasKey('webartisan', $hints);
    }

    // =========================================================================
    // Routes
    // =========================================================================

    public function test_routes_are_registered_when_enabled(): void
    {
        $this->assertTrue(Webartisan::hasRoutes());
    }

    public function test_index_route_exists(): void
    {
        $router = $this->app['router'];
        $this->assertTrue($router->has('webartisan.index'));
    }

    public function test_run_route_exists(): void
    {
        $router = $this->app['router'];
        $this->assertTrue($router->has('webartisan.run'));
    }

    public function test_commands_route_exists(): void
    {
        $router = $this->app['router'];
        $this->assertTrue($router->has('webartisan.commands'));
    }

    public function test_routes_use_correct_prefix(): void
    {
        $indexUrl = route('webartisan.index');
        $this->assertStringContainsString('/webartisan', $indexUrl);
    }

    public function test_routes_have_correct_methods(): void
    {
        $routes = $this->app['router']->getRoutes();

        $indexRoute = $routes->getByName('webartisan.index');
        $this->assertContains('GET', $indexRoute->methods());

        $runRoute = $routes->getByName('webartisan.run');
        $this->assertContains('POST', $runRoute->methods());

        $commandsRoute = $routes->getByName('webartisan.commands');
        $this->assertContains('GET', $commandsRoute->methods());
    }

    // =========================================================================
    // Artisan commands
    // =========================================================================

    public function test_install_command_is_registered(): void
    {
        $commands = \Illuminate\Support\Facades\Artisan::all();

        $this->assertArrayHasKey('webartisan:install', $commands);
    }

    // =========================================================================
    // Publishable resources
    // =========================================================================

    public function test_config_is_publishable(): void
    {
        $publishes = ServiceProvider::pathsToPublish(
            WebartisanServiceProvider::class,
            'webartisan-config'
        );

        $this->assertNotEmpty($publishes);
    }

    public function test_assets_are_publishable(): void
    {
        $publishes = ServiceProvider::pathsToPublish(
            WebartisanServiceProvider::class,
            'webartisan-assets'
        );

        $this->assertNotEmpty($publishes);
    }

    public function test_views_are_publishable(): void
    {
        $publishes = ServiceProvider::pathsToPublish(
            WebartisanServiceProvider::class,
            'webartisan-views'
        );

        $this->assertNotEmpty($publishes);
    }
}
