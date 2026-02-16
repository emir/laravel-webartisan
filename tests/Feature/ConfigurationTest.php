<?php

namespace Emir\Webartisan\Tests\Feature;

use Emir\Webartisan\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    // =========================================================================
    // Config merging
    // =========================================================================

    public function test_config_is_loaded(): void
    {
        $this->assertNotNull(config('webartisan'));
    }

    public function test_default_enabled_is_true(): void
    {
        // We set it to true in TestCase, but verify the config file defaults
        $this->assertTrue(config('webartisan.enabled'));
    }

    public function test_default_environments(): void
    {
        // We override in tests, but check the structure exists
        $envs = config('webartisan.enabled_environments');
        $this->assertIsArray($envs);
    }

    public function test_default_route_prefix(): void
    {
        $this->assertSame('webartisan', config('webartisan.route_prefix'));
    }

    public function test_default_domain_is_null(): void
    {
        $this->assertNull(config('webartisan.domain'));
    }

    public function test_default_middleware_includes_web(): void
    {
        $middleware = config('webartisan.middleware');
        $this->assertIsArray($middleware);
        $this->assertContains('web', $middleware);
    }

    public function test_default_gate_is_null(): void
    {
        $this->assertNull(config('webartisan.gate'));
    }

    public function test_default_allowed_commands_is_empty(): void
    {
        $allowed = config('webartisan.allowed_commands');
        $this->assertIsArray($allowed);
        $this->assertEmpty($allowed);
    }

    public function test_default_blocked_commands_is_not_empty(): void
    {
        $blocked = config('webartisan.blocked_commands');
        $this->assertIsArray($blocked);
        $this->assertNotEmpty($blocked);
    }

    public function test_default_theme(): void
    {
        $this->assertSame('dark', config('webartisan.theme'));
    }

    // =========================================================================
    // Theme configuration affects view
    // =========================================================================

    public function test_dark_theme_renders_in_view(): void
    {
        config()->set('webartisan.theme', 'dark');

        $response = $this->get(route('webartisan.index'));
        $response->assertSee('data-theme="dark"', false);
    }

    public function test_light_theme_renders_in_view(): void
    {
        config()->set('webartisan.theme', 'light');

        $response = $this->get(route('webartisan.index'));
        $response->assertSee('data-theme="light"', false);
    }

    public function test_monokai_theme_renders_in_view(): void
    {
        config()->set('webartisan.theme', 'monokai');

        $response = $this->get(route('webartisan.index'));
        $response->assertSee('data-theme="monokai"', false);
    }

    public function test_dracula_theme_renders_in_view(): void
    {
        config()->set('webartisan.theme', 'dracula');

        $response = $this->get(route('webartisan.index'));
        $response->assertSee('data-theme="dracula"', false);
    }

    // =========================================================================
    // Route prefix configuration
    // =========================================================================

    public function test_default_prefix_registers_routes(): void
    {
        $this->get('/webartisan')->assertStatus(200);
    }

    public function test_routes_accessible_with_default_prefix(): void
    {
        $this->get('/webartisan')->assertStatus(200);
        $this->getJson('/webartisan/commands')->assertStatus(200);
    }

    // =========================================================================
    // Blocked commands in config
    // =========================================================================

    public function test_blocked_commands_config_contains_dangerous_defaults(): void
    {
        $blocked = config('webartisan.blocked_commands');

        $this->assertContains('down', $blocked);
        $this->assertContains('tinker', $blocked);
        $this->assertContains('db:wipe', $blocked);
        $this->assertContains('migrate:fresh', $blocked);
        $this->assertContains('key:generate', $blocked);
    }

    public function test_commands_endpoint_filters_blocked_by_default(): void
    {
        $response = $this->getJson(route('webartisan.commands'));

        $commands = $response->json('commands');
        $names = array_column($commands, 'name');

        foreach (config('webartisan.blocked_commands') as $blocked) {
            $this->assertNotContains($blocked, $names, "Blocked command '{$blocked}' should not appear in list");
        }
    }
}
