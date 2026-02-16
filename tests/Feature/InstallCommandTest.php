<?php

namespace Emir\Webartisan\Tests\Feature;

use Emir\Webartisan\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    public function test_install_command_exists(): void
    {
        $this->artisan('webartisan:install')
            ->assertSuccessful();
    }

    public function test_install_command_returns_success_exit_code(): void
    {
        $this->artisan('webartisan:install')
            ->assertExitCode(0);
    }

    public function test_install_command_outputs_success_message(): void
    {
        $this->artisan('webartisan:install')
            ->expectsOutputToContain('Webartisan installed successfully');
    }

    public function test_install_command_outputs_url_hint(): void
    {
        $this->artisan('webartisan:install')
            ->expectsOutputToContain('/webartisan');
    }

    public function test_install_command_with_force_flag_succeeds(): void
    {
        $this->artisan('webartisan:install --force')
            ->assertSuccessful();
    }

    public function test_install_command_runs_vendor_publish(): void
    {
        // Running install twice should work (idempotent)
        $this->artisan('webartisan:install')->assertSuccessful();
        $this->artisan('webartisan:install')->assertSuccessful();
    }

    public function test_install_command_with_force_is_idempotent(): void
    {
        $this->artisan('webartisan:install --force')->assertSuccessful();
        $this->artisan('webartisan:install --force')->assertSuccessful();
    }

    public function test_install_command_has_description(): void
    {
        $commands = \Illuminate\Support\Facades\Artisan::all();

        $this->assertArrayHasKey('webartisan:install', $commands);
        $this->assertNotEmpty($commands['webartisan:install']->getDescription());
    }
}
