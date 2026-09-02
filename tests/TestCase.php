<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Boot the application under the `testing` environment.
     *
     * `php artisan test` boots the console app from .env (e.g. APP_ENV=local)
     * before PHPUnit's phpunit.xml env overrides are applied, which leaves CSRF
     * active inside feature tests. Seeding the environment here guarantees the
     * suite always runs the way framework unit tests expect.
     */
    public function createApplication()
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
