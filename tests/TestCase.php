<?php

namespace Emir\Webartisan\Tests;

use Emir\Webartisan\WebartisanServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            WebartisanServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('webartisan.enabled', true);
        $app['config']->set('webartisan.enabled_environments', ['testing']);
    }
}
