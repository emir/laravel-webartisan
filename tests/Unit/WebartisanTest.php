<?php

namespace Emir\Webartisan\Tests\Unit;

use Emir\Webartisan\Tests\TestCase;
use Emir\Webartisan\Webartisan;

class WebartisanTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset static state between tests
        Webartisan::auth(function () {
            return app()->environment(config('webartisan.enabled_environments', ['local']));
        });

        parent::tearDown();
    }

    // =========================================================================
    // VERSION
    // =========================================================================

    public function test_version_constant_is_defined(): void
    {
        $this->assertNotEmpty(Webartisan::VERSION);
    }

    public function test_version_follows_semver_format(): void
    {
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', Webartisan::VERSION);
    }

    // =========================================================================
    // isEnabled()
    // =========================================================================

    public function test_is_enabled_returns_true_when_environment_matches(): void
    {
        $this->assertTrue(Webartisan::isEnabled());
    }

    public function test_is_enabled_returns_false_when_master_switch_disabled(): void
    {
        config()->set('webartisan.enabled', false);

        $this->assertFalse(Webartisan::isEnabled());
    }

    public function test_is_enabled_returns_false_when_environment_does_not_match(): void
    {
        config()->set('webartisan.enabled_environments', ['production']);

        $this->assertFalse(Webartisan::isEnabled());
    }

    public function test_is_enabled_with_wildcard_allows_any_environment(): void
    {
        config()->set('webartisan.enabled_environments', ['*']);

        $this->assertTrue(Webartisan::isEnabled());
    }

    public function test_is_enabled_with_multiple_environments(): void
    {
        config()->set('webartisan.enabled_environments', ['local', 'testing', 'staging']);

        $this->assertTrue(Webartisan::isEnabled());
    }

    public function test_is_enabled_with_empty_environments_returns_false(): void
    {
        config()->set('webartisan.enabled_environments', []);

        $this->assertFalse(Webartisan::isEnabled());
    }

    public function test_master_switch_overrides_environment_match(): void
    {
        config()->set('webartisan.enabled', false);
        config()->set('webartisan.enabled_environments', ['testing']);

        $this->assertFalse(Webartisan::isEnabled());
    }

    public function test_master_switch_true_enables(): void
    {
        config()->set('webartisan.enabled', true);

        $this->assertTrue(Webartisan::isEnabled());
    }

    public function test_master_switch_false_disables(): void
    {
        config()->set('webartisan.enabled', false);

        $this->assertFalse(Webartisan::isEnabled());
    }

    // =========================================================================
    // auth() & check()
    // =========================================================================

    public function test_custom_auth_callback_returning_false_denies_access(): void
    {
        Webartisan::auth(function ($request) {
            return false;
        });

        $this->assertFalse(Webartisan::check(null));
    }

    public function test_custom_auth_callback_returning_true_allows_access(): void
    {
        Webartisan::auth(function ($request) {
            return true;
        });

        $this->assertTrue(Webartisan::check(null));
    }

    public function test_auth_callback_receives_request_parameter(): void
    {
        $receivedRequest = null;

        Webartisan::auth(function ($request) use (&$receivedRequest) {
            $receivedRequest = $request;

            return true;
        });

        $fakeRequest = 'test-request';
        Webartisan::check($fakeRequest);

        $this->assertSame('test-request', $receivedRequest);
    }

    public function test_auth_returns_static_instance_for_chaining(): void
    {
        $result = Webartisan::auth(function () {
            return true;
        });

        $this->assertInstanceOf(Webartisan::class, $result);
    }

    public function test_default_check_uses_environment(): void
    {
        // Reset auth to default by setting it to null indirectly
        // The default fallback checks environment
        $reflection = new \ReflectionClass(Webartisan::class);
        $property = $reflection->getProperty('authUsing');
        $property->setAccessible(true);
        $property->setValue(null, null);

        // Environment is 'testing' and config has ['testing']
        $this->assertTrue(Webartisan::check(null));

        // Change to non-matching environment
        config()->set('webartisan.enabled_environments', ['production']);
        $this->assertFalse(Webartisan::check(null));
    }

    // =========================================================================
    // routesRegistered() & hasRoutes()
    // =========================================================================

    public function test_routes_are_registered_after_boot(): void
    {
        // The service provider registers routes during boot
        $this->assertTrue(Webartisan::hasRoutes());
    }

    public function test_routes_registered_marks_routes_as_registered(): void
    {
        $reflection = new \ReflectionClass(Webartisan::class);
        $property = $reflection->getProperty('routesRegistered');
        $property->setAccessible(true);

        // Reset
        $property->setValue(null, false);
        $this->assertFalse(Webartisan::hasRoutes());

        Webartisan::routesRegistered();
        $this->assertTrue(Webartisan::hasRoutes());
    }
}
