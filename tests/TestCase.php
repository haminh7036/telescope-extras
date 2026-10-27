<?php

namespace Haminh7036\TelescopeExtras\Tests;

use Haminh7036\TelescopeExtras\TelescopeExtrasServiceProvider;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TelescopeServiceProvider::class,
            TelescopeExtrasServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:yk+bUVuZa1p86Dqjk9OjVK2R1pm6XHxC6xEKFq8utH0=');
        // Nothing here needs recorded entries, so keep Telescope away from the database.
        $app['config']->set('telescope.watchers', []);

        Telescope::auth(fn () => true);
    }
}
