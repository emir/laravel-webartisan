<?php

namespace Emir\Webartisan\Tests\Feature;

use Emir\Webartisan\Tests\TestCase;

class WebartisanRoutesTest extends TestCase
{
    // =========================================================================
    // Index (GET /)
    // =========================================================================

    public function test_index_page_loads_successfully(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('webartisan::index');
    }

    public function test_index_page_contains_webartisan_title(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('Webartisan');
    }

    public function test_index_page_contains_csrf_meta_tag(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('csrf-token', false);
    }

    public function test_index_page_contains_noindex_robots_tag(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('noindex, nofollow', false);
    }

    public function test_index_page_passes_theme_to_view(): void
    {
        config()->set('webartisan.theme', 'dracula');

        $response = $this->get(route('webartisan.index'));

        $response->assertSee('data-theme="dracula"', false);
    }

    public function test_index_page_passes_version_to_view(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('2.0.0', false);
    }

    public function test_index_page_includes_jquery_cdn(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('jquery/3.7.1/jquery.min.js', false);
    }

    public function test_index_page_includes_jquery_terminal_cdn(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('jquery.terminal/2.45.2', false);
    }

    public function test_index_page_includes_app_js_asset(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('vendor/webartisan/app.js', false);
    }

    public function test_index_page_includes_css_asset(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('vendor/webartisan/webartisan.css', false);
    }

    public function test_index_page_shows_environment(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('testing', false);
    }

    public function test_index_page_has_webartisan_config_js_object(): void
    {
        $response = $this->get(route('webartisan.index'));

        $response->assertSee('WebartisanConfig', false);
    }

    // =========================================================================
    // Commands (GET /commands)
    // =========================================================================

    public function test_commands_endpoint_returns_json(): void
    {
        $response = $this->getJson(route('webartisan.commands'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'commands',
        ]);
    }

    public function test_commands_returns_array_of_command_objects(): void
    {
        $response = $this->getJson(route('webartisan.commands'));

        $response->assertStatus(200);

        $commands = $response->json('commands');
        $this->assertIsArray($commands);

        if (count($commands) > 0) {
            $this->assertArrayHasKey('name', $commands[0]);
            $this->assertArrayHasKey('description', $commands[0]);
        }
    }

    public function test_commands_are_sorted_by_name(): void
    {
        $response = $this->getJson(route('webartisan.commands'));

        $commands = $response->json('commands');
        $names = array_column($commands, 'name');

        $sorted = $names;
        sort($sorted);

        $this->assertSame($sorted, $names);
    }

    public function test_commands_respects_blocked_list(): void
    {
        config()->set('webartisan.blocked_commands', ['tinker']);

        $response = $this->getJson(route('webartisan.commands'));

        $commands = $response->json('commands');
        $names = array_column($commands, 'name');

        $this->assertNotContains('tinker', $names);
    }

    public function test_commands_respects_allowed_list(): void
    {
        config()->set('webartisan.allowed_commands', ['help']);
        config()->set('webartisan.blocked_commands', []);

        $response = $this->getJson(route('webartisan.commands'));

        $commands = $response->json('commands');
        $names = array_column($commands, 'name');

        $this->assertContains('help', $names);
    }

    public function test_commands_endpoint_does_not_accept_post(): void
    {
        $response = $this->postJson(route('webartisan.commands'));

        $response->assertStatus(405);
    }

    // =========================================================================
    // Run (POST /run)
    // =========================================================================

    public function test_run_requires_command_field(): void
    {
        $response = $this->postJson(route('webartisan.run'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('command');
    }

    public function test_run_requires_command_to_be_string(): void
    {
        $response = $this->postJson(route('webartisan.run'), [
            'command' => 12345,
        ]);

        $response->assertStatus(422);
    }

    public function test_run_rejects_command_over_1000_chars(): void
    {
        $response = $this->postJson(route('webartisan.run'), [
            'command' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422);
    }

    public function test_run_accepts_command_at_max_length(): void
    {
        // A 1000-char string passes max:1000 validation
        // It will fail at Artisan level (command not found), returning 422 with output
        $response = $this->postJson(route('webartisan.run'), [
            'command' => str_repeat('a', 1000),
        ]);

        // Should pass validation (no 'command' validation error) but fail at execution
        $response->assertJsonMissingValidationErrors('command');
    }

    public function test_run_executes_valid_command(): void
    {
        $response = $this->postJson(route('webartisan.run'), [
            'command' => 'help',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'output',
            'exit_code',
        ]);
        $response->assertJson(['success' => true]);
        $response->assertJson(['exit_code' => 0]);
    }

    public function test_run_returns_command_output(): void
    {
        $response = $this->postJson(route('webartisan.run'), [
            'command' => 'help',
        ]);

        $output = $response->json('output');
        $this->assertNotEmpty($output);
    }

    public function test_run_blocks_forbidden_command(): void
    {
        config()->set('webartisan.blocked_commands', ['migrate:fresh']);

        $response = $this->postJson(route('webartisan.run'), [
            'command' => 'migrate:fresh',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $response->assertJsonFragment(['exit_code' => 1]);
    }

    public function test_run_blocks_forbidden_command_with_arguments(): void
    {
        config()->set('webartisan.blocked_commands', ['migrate:fresh']);

        $response = $this->postJson(route('webartisan.run'), [
            'command' => 'migrate:fresh --seed',
        ]);

        $response->assertStatus(403);
    }

    public function test_run_handles_nonexistent_command(): void
    {
        $response = $this->postJson(route('webartisan.run'), [
            'command' => 'nonexistent:command',
        ]);

        // Should return 422 as it catches the Throwable
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_run_strips_whitespace_from_command(): void
    {
        $response = $this->postJson(route('webartisan.run'), [
            'command' => '  help  ',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_run_command_with_arguments(): void
    {
        config()->set('webartisan.blocked_commands', []);

        $response = $this->postJson(route('webartisan.run'), [
            'command' => 'help help',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_run_returns_403_message_with_command_name(): void
    {
        config()->set('webartisan.blocked_commands', ['tinker']);

        $response = $this->postJson(route('webartisan.run'), [
            'command' => 'tinker',
        ]);

        $response->assertStatus(403);
        $this->assertStringContainsString('tinker', $response->json('output'));
    }

    public function test_run_endpoint_does_not_accept_get(): void
    {
        $response = $this->getJson('/webartisan/run');

        $response->assertStatus(405);
    }

    // =========================================================================
    // Route names
    // =========================================================================

    public function test_index_route_is_named(): void
    {
        $this->assertTrue(app('router')->has('webartisan.index'));
    }

    public function test_run_route_is_named(): void
    {
        $this->assertTrue(app('router')->has('webartisan.run'));
    }

    public function test_commands_route_is_named(): void
    {
        $this->assertTrue(app('router')->has('webartisan.commands'));
    }

    // =========================================================================
    // HTTP methods
    // =========================================================================

    public function test_index_only_accepts_get(): void
    {
        $this->postJson('/webartisan')->assertStatus(405);
        $this->putJson('/webartisan')->assertStatus(405);
        $this->deleteJson('/webartisan')->assertStatus(405);
    }

    public function test_run_only_accepts_post(): void
    {
        $this->getJson('/webartisan/run')->assertStatus(405);
        $this->putJson('/webartisan/run')->assertStatus(405);
        $this->deleteJson('/webartisan/run')->assertStatus(405);
    }
}
