<?php

namespace Emir\Webartisan\Tests\Feature;

use Emir\Webartisan\Tests\TestCase;
use Emir\Webartisan\Webartisan;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Gate;

class MiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Webartisan::auth(function () {
            return app()->environment(config('webartisan.enabled_environments', ['local']));
        });

        parent::tearDown();
    }

    // =========================================================================
    // Enabled/disabled switch
    // =========================================================================

    public function test_returns_404_when_webartisan_disabled(): void
    {
        config()->set('webartisan.enabled', false);

        $this->get('/webartisan')->assertStatus(404);
        $this->getJson('/webartisan/commands')->assertStatus(404);
        $this->postJson('/webartisan/run', ['command' => 'help'])->assertStatus(404);
    }

    public function test_returns_200_when_webartisan_enabled(): void
    {
        config()->set('webartisan.enabled', true);

        $this->get(route('webartisan.index'))->assertStatus(200);
    }

    // =========================================================================
    // Environment check
    // =========================================================================

    public function test_returns_403_when_environment_not_allowed(): void
    {
        // Custom auth that checks wrong environment
        Webartisan::auth(function () {
            return false;
        });

        $this->get(route('webartisan.index'))->assertStatus(403);
    }

    public function test_passes_when_environment_matches(): void
    {
        Webartisan::auth(function () {
            return true;
        });

        $this->get(route('webartisan.index'))->assertStatus(200);
    }

    // =========================================================================
    // Gate authorization
    // =========================================================================

    public function test_passes_when_no_gate_configured(): void
    {
        config()->set('webartisan.gate', null);

        $this->get(route('webartisan.index'))->assertStatus(200);
    }

    public function test_passes_when_gate_allows(): void
    {
        config()->set('webartisan.gate', 'viewWebartisan');

        $user = new class extends Authenticatable
        {
            protected $guarded = [];
        };
        $user->id = 1;

        Gate::define('viewWebartisan', function () {
            return true;
        });

        $this->actingAs($user)
            ->get(route('webartisan.index'))
            ->assertStatus(200);
    }

    public function test_returns_403_when_gate_denies(): void
    {
        config()->set('webartisan.gate', 'viewWebartisan');

        $user = new class extends Authenticatable
        {
            protected $guarded = [];
        };
        $user->id = 1;

        Gate::define('viewWebartisan', function () {
            return false;
        });

        $this->actingAs($user)
            ->get(route('webartisan.index'))
            ->assertStatus(403);
    }

    public function test_skips_gate_check_when_gate_not_defined(): void
    {
        // Set gate name but don't define it — should pass
        config()->set('webartisan.gate', 'nonExistentGate');

        $this->get(route('webartisan.index'))->assertStatus(200);
    }

    // =========================================================================
    // Custom auth callback via Webartisan::auth()
    // =========================================================================

    public function test_custom_auth_denies_all_routes(): void
    {
        Webartisan::auth(fn () => false);

        $this->get(route('webartisan.index'))->assertStatus(403);
        $this->getJson(route('webartisan.commands'))->assertStatus(403);
        $this->postJson(route('webartisan.run'), ['command' => 'help'])->assertStatus(403);
    }

    public function test_custom_auth_allows_all_routes(): void
    {
        Webartisan::auth(fn () => true);

        $this->get(route('webartisan.index'))->assertStatus(200);
        $this->getJson(route('webartisan.commands'))->assertStatus(200);
        $this->postJson(route('webartisan.run'), ['command' => 'help'])->assertStatus(200);
    }

    // =========================================================================
    // Middleware applied to all routes
    // =========================================================================

    public function test_middleware_blocks_index_route(): void
    {
        config()->set('webartisan.enabled', false);

        $this->get('/webartisan')->assertStatus(404);
    }

    public function test_middleware_blocks_run_route(): void
    {
        config()->set('webartisan.enabled', false);

        $this->postJson('/webartisan/run', ['command' => 'help'])->assertStatus(404);
    }

    public function test_middleware_blocks_commands_route(): void
    {
        config()->set('webartisan.enabled', false);

        $this->getJson('/webartisan/commands')->assertStatus(404);
    }
}
