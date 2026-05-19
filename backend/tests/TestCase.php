<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        // Ensure test process boots with isolated testing database variables
        // before Laravel initializes connections for RefreshDatabase.
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';

        $_ENV['DB_CONNECTION'] = 'mysql';
        $_SERVER['DB_CONNECTION'] = 'mysql';
        $_ENV['DB_HOST'] = 'mysql';
        $_SERVER['DB_HOST'] = 'mysql';
        $_ENV['DB_PORT'] = '3306';
        $_SERVER['DB_PORT'] = '3306';
        $_ENV['DB_DATABASE'] = 'saas_testing';
        $_SERVER['DB_DATABASE'] = 'saas_testing';
        $_ENV['DB_TEST_DATABASE'] = 'saas_testing';
        $_SERVER['DB_TEST_DATABASE'] = 'saas_testing';
        $_ENV['DB_USERNAME'] = 'saas';
        $_SERVER['DB_USERNAME'] = 'saas';
        $_ENV['DB_PASSWORD'] = 'secret';
        $_SERVER['DB_PASSWORD'] = 'secret';

        return parent::createApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // WHY:
        // Web feature tests in this project post directly to auth/profile routes
        // and expect Laravel default behavior without manual CSRF token plumbing.
        $this->withoutMiddleware(PreventRequestForgery::class);

        $activeDatabase = (string) config('database.connections.mysql.database');

        // Fail fast if a test process points at non-testing database.
        if (! app()->environment('testing') || $activeDatabase !== 'saas_testing') {
            $this->fail(sprintf(
                'Unsafe test database configuration detected. APP_ENV=%s, DB=%s (expected testing/saas_testing).',
                app()->environment(),
                $activeDatabase,
            ));
        }
    }
}
