<?php

namespace Emir\Webartisan\Tests\Unit;

use Emir\Webartisan\Tests\TestCase;
use Emir\Webartisan\Webartisan;
use PHPUnit\Framework\Attributes\DataProvider;

class CommandFilteringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Start with clean slate
        config()->set('webartisan.allowed_commands', []);
        config()->set('webartisan.blocked_commands', []);
    }

    // =========================================================================
    // Default behavior (no allow/block lists)
    // =========================================================================

    public function test_all_commands_allowed_by_default(): void
    {
        $this->assertTrue(Webartisan::isCommandAllowed('route:list'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:status'));
        $this->assertTrue(Webartisan::isCommandAllowed('make:model'));
        $this->assertTrue(Webartisan::isCommandAllowed('help'));
    }

    public function test_empty_command_name_is_allowed_by_default(): void
    {
        $this->assertTrue(Webartisan::isCommandAllowed(''));
    }

    // =========================================================================
    // Blocked commands (exact match)
    // =========================================================================

    public function test_exact_blocked_command_is_denied(): void
    {
        config()->set('webartisan.blocked_commands', ['tinker']);

        $this->assertFalse(Webartisan::isCommandAllowed('tinker'));
    }

    public function test_non_blocked_command_is_allowed(): void
    {
        config()->set('webartisan.blocked_commands', ['tinker']);

        $this->assertTrue(Webartisan::isCommandAllowed('route:list'));
    }

    public function test_multiple_blocked_commands(): void
    {
        config()->set('webartisan.blocked_commands', ['tinker', 'down', 'env']);

        $this->assertFalse(Webartisan::isCommandAllowed('tinker'));
        $this->assertFalse(Webartisan::isCommandAllowed('down'));
        $this->assertFalse(Webartisan::isCommandAllowed('env'));
        $this->assertTrue(Webartisan::isCommandAllowed('route:list'));
    }

    // =========================================================================
    // Allowed commands (exact match, whitelist mode)
    // =========================================================================

    public function test_allowed_whitelist_permits_listed_commands(): void
    {
        config()->set('webartisan.allowed_commands', ['route:list', 'migrate:status']);

        $this->assertTrue(Webartisan::isCommandAllowed('route:list'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:status'));
    }

    public function test_allowed_whitelist_denies_unlisted_commands(): void
    {
        config()->set('webartisan.allowed_commands', ['route:list']);

        $this->assertFalse(Webartisan::isCommandAllowed('make:model'));
        $this->assertFalse(Webartisan::isCommandAllowed('tinker'));
        $this->assertFalse(Webartisan::isCommandAllowed('migrate:status'));
    }

    // =========================================================================
    // Wildcard patterns in blocked commands
    // =========================================================================

    public function test_wildcard_blocks_all_matching_commands(): void
    {
        config()->set('webartisan.blocked_commands', ['db:*']);

        $this->assertFalse(Webartisan::isCommandAllowed('db:wipe'));
        $this->assertFalse(Webartisan::isCommandAllowed('db:seed'));
        $this->assertFalse(Webartisan::isCommandAllowed('db:show'));
        $this->assertTrue(Webartisan::isCommandAllowed('route:list'));
    }

    public function test_wildcard_blocks_prefix_group(): void
    {
        config()->set('webartisan.blocked_commands', ['migrate:*']);

        $this->assertFalse(Webartisan::isCommandAllowed('migrate:fresh'));
        $this->assertFalse(Webartisan::isCommandAllowed('migrate:reset'));
        $this->assertFalse(Webartisan::isCommandAllowed('migrate:rollback'));
        $this->assertFalse(Webartisan::isCommandAllowed('migrate:status'));
        $this->assertTrue(Webartisan::isCommandAllowed('make:migration'));
    }

    public function test_wildcard_at_beginning(): void
    {
        config()->set('webartisan.blocked_commands', ['*:fresh']);

        $this->assertFalse(Webartisan::isCommandAllowed('migrate:fresh'));
        $this->assertFalse(Webartisan::isCommandAllowed('db:fresh'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:status'));
    }

    public function test_double_wildcard_blocks_everything(): void
    {
        config()->set('webartisan.blocked_commands', ['*']);

        $this->assertFalse(Webartisan::isCommandAllowed('route:list'));
        $this->assertFalse(Webartisan::isCommandAllowed('tinker'));
        $this->assertFalse(Webartisan::isCommandAllowed(''));
    }

    // =========================================================================
    // Wildcard patterns in allowed commands
    // =========================================================================

    public function test_wildcard_allows_matching_group(): void
    {
        config()->set('webartisan.allowed_commands', ['migrate:*']);

        $this->assertTrue(Webartisan::isCommandAllowed('migrate:status'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:rollback'));
        $this->assertFalse(Webartisan::isCommandAllowed('route:list'));
    }

    public function test_multiple_wildcard_allowed_patterns(): void
    {
        config()->set('webartisan.allowed_commands', ['route:*', 'config:*', 'help']);

        $this->assertTrue(Webartisan::isCommandAllowed('route:list'));
        $this->assertTrue(Webartisan::isCommandAllowed('route:clear'));
        $this->assertTrue(Webartisan::isCommandAllowed('config:show'));
        $this->assertTrue(Webartisan::isCommandAllowed('config:cache'));
        $this->assertTrue(Webartisan::isCommandAllowed('help'));
        $this->assertFalse(Webartisan::isCommandAllowed('make:model'));
    }

    // =========================================================================
    // Question mark wildcard (single character)
    // =========================================================================

    public function test_question_mark_matches_single_character(): void
    {
        config()->set('webartisan.blocked_commands', ['db:???e']);

        $this->assertFalse(Webartisan::isCommandAllowed('db:wipe'));
        $this->assertTrue(Webartisan::isCommandAllowed('db:seed'));
        $this->assertTrue(Webartisan::isCommandAllowed('db:show'));
    }

    // =========================================================================
    // Blocked takes precedence over allowed
    // =========================================================================

    public function test_blocked_overrides_allowed_exact(): void
    {
        config()->set('webartisan.allowed_commands', ['migrate:fresh', 'migrate:status']);
        config()->set('webartisan.blocked_commands', ['migrate:fresh']);

        $this->assertFalse(Webartisan::isCommandAllowed('migrate:fresh'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:status'));
    }

    public function test_blocked_wildcard_overrides_allowed_wildcard(): void
    {
        config()->set('webartisan.allowed_commands', ['migrate:*']);
        config()->set('webartisan.blocked_commands', ['migrate:fresh', 'migrate:reset']);

        $this->assertTrue(Webartisan::isCommandAllowed('migrate:status'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:rollback'));
        $this->assertFalse(Webartisan::isCommandAllowed('migrate:fresh'));
        $this->assertFalse(Webartisan::isCommandAllowed('migrate:reset'));
    }

    public function test_blocked_wildcard_overrides_allowed_exact(): void
    {
        config()->set('webartisan.allowed_commands', ['db:seed', 'db:wipe']);
        config()->set('webartisan.blocked_commands', ['db:*']);

        $this->assertFalse(Webartisan::isCommandAllowed('db:seed'));
        $this->assertFalse(Webartisan::isCommandAllowed('db:wipe'));
    }

    // =========================================================================
    // Case insensitivity
    // =========================================================================

    public function test_pattern_matching_is_case_insensitive(): void
    {
        config()->set('webartisan.blocked_commands', ['Tinker']);

        $this->assertFalse(Webartisan::isCommandAllowed('tinker'));
        $this->assertFalse(Webartisan::isCommandAllowed('TINKER'));
        $this->assertFalse(Webartisan::isCommandAllowed('Tinker'));
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function test_command_with_colon_in_name(): void
    {
        config()->set('webartisan.blocked_commands', ['cache:clear']);

        $this->assertFalse(Webartisan::isCommandAllowed('cache:clear'));
        $this->assertTrue(Webartisan::isCommandAllowed('cache:forget'));
    }

    public function test_command_without_namespace(): void
    {
        config()->set('webartisan.blocked_commands', ['tinker', 'down', 'up']);

        $this->assertFalse(Webartisan::isCommandAllowed('tinker'));
        $this->assertFalse(Webartisan::isCommandAllowed('down'));
        $this->assertFalse(Webartisan::isCommandAllowed('up'));
        $this->assertTrue(Webartisan::isCommandAllowed('help'));
    }

    public function test_empty_blocked_list_blocks_nothing(): void
    {
        config()->set('webartisan.blocked_commands', []);

        $this->assertTrue(Webartisan::isCommandAllowed('tinker'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:fresh'));
    }

    public function test_empty_allowed_list_allows_everything_except_blocked(): void
    {
        config()->set('webartisan.allowed_commands', []);
        config()->set('webartisan.blocked_commands', ['tinker']);

        $this->assertFalse(Webartisan::isCommandAllowed('tinker'));
        $this->assertTrue(Webartisan::isCommandAllowed('route:list'));
        $this->assertTrue(Webartisan::isCommandAllowed('migrate:fresh'));
    }

    // =========================================================================
    // Default config blocked commands
    // =========================================================================

    #[DataProvider('defaultBlockedCommandsProvider')]
    public function test_default_config_blocks_dangerous_commands(string $command): void
    {
        // Restore the actual default blocked commands from config file
        config()->set('webartisan.blocked_commands', [
            'down', 'up', 'env', 'serve', 'tinker', 'key:generate',
            'package:discover', 'migrate:fresh', 'migrate:reset',
            'db:wipe', 'db:seed', 'storage:link', 'vendor:publish',
        ]);

        $this->assertFalse(Webartisan::isCommandAllowed($command));
    }

    public static function defaultBlockedCommandsProvider(): array
    {
        return [
            'down' => ['down'],
            'up' => ['up'],
            'env' => ['env'],
            'serve' => ['serve'],
            'tinker' => ['tinker'],
            'key:generate' => ['key:generate'],
            'package:discover' => ['package:discover'],
            'migrate:fresh' => ['migrate:fresh'],
            'migrate:reset' => ['migrate:reset'],
            'db:wipe' => ['db:wipe'],
            'db:seed' => ['db:seed'],
            'storage:link' => ['storage:link'],
            'vendor:publish' => ['vendor:publish'],
        ];
    }

    // =========================================================================
    // Regex special characters safety
    // =========================================================================

    public function test_special_regex_characters_are_escaped_in_patterns(): void
    {
        config()->set('webartisan.blocked_commands', ['test.command']);

        // The dot should be literal, not "any character"
        $this->assertFalse(Webartisan::isCommandAllowed('test.command'));
        // Without escaping, 'testXcommand' would also match
        $this->assertTrue(Webartisan::isCommandAllowed('testXcommand'));
    }

    public function test_pattern_with_parentheses(): void
    {
        config()->set('webartisan.blocked_commands', ['test(cmd)']);

        $this->assertFalse(Webartisan::isCommandAllowed('test(cmd)'));
    }

    public function test_pattern_with_brackets(): void
    {
        config()->set('webartisan.blocked_commands', ['test[0]']);

        $this->assertFalse(Webartisan::isCommandAllowed('test[0]'));
    }
}
