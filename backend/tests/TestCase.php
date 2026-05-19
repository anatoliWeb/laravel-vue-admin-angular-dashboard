<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // WHY:
        // Web feature tests in this project post directly to auth/profile routes
        // and expect Laravel default behavior without manual CSRF token plumbing.
        $this->withoutMiddleware(PreventRequestForgery::class);

        // WHY:
        // Docker env variables can force DB_DATABASE=saas at process startup.
        // We hard-switch test runtime to a dedicated database to prevent dev DB
        // cleanup during RefreshDatabase/migration test cycles.
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => env('DB_HOST', 'mysql'),
            'database.connections.mysql.port' => env('DB_PORT', '3306'),
            'database.connections.mysql.database' => env('DB_TEST_DATABASE', 'saas_testing'),
            'database.connections.mysql.username' => env('DB_USERNAME', 'saas'),
            'database.connections.mysql.password' => env('DB_PASSWORD', 'secret'),
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }
}
