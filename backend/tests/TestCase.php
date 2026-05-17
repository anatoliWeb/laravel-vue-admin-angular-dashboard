<?php

namespace Tests;

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
    }
}
