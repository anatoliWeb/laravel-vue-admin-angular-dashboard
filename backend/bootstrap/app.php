<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\LogRequestMiddleware;

/**
 * Application bootstrap configuration.
 *
 * This file is responsible for:
 * - routing setup (web, api, console, custom routes)
 * - global middleware registration
 * - exception handling configuration
 *
 * Acts as the central entry point for application configuration in modern Laravel.
 */
return Application::configure(basePath: dirname(__DIR__))

    /**
     * ------------------------------------------------------------
     * Routing Configuration
     * ------------------------------------------------------------
     *
     * Registers all route groups used in the application:
     * - web routes (session, CSRF, views)
     * - API routes (stateless, JSON)
     * - console commands
     * - health check endpoint
     */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        /**
         * Register additional route groups AFTER Laravel
         * finishes its internal routing setup.
         *
         * Used for admin panel with custom middleware stack.
         */
        then: function (): void {
            Route::middleware(['web', 'auth', 'permission:access_admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )

    /**
     * ------------------------------------------------------------
     * Middleware Configuration
     * ------------------------------------------------------------
     *
     * Registers global and aliased middleware.
     */
    ->withMiddleware(function (Middleware $middleware): void {

        /**
         * Global middleware configuration.
         *
         * WHY:
         * This is the central place to define middleware execution order
         * and aliases for the entire application.
         *
         * Order matters here — middleware are executed sequentially.
         */

        /**
         * CORS Middleware (must be FIRST).
         *
         * WHY:
         * - Handles preflight (OPTIONS) requests before hitting application logic
         * - Ensures CORS headers are always attached to responses
         * - Prevents frontend blocking due to missing headers
         *
         * NOTE:
         * This replaces any nginx-level CORS handling,
         * keeping behavior consistent across environments.
         */
        $middleware->prepend(CorsMiddleware::class);

        /**
         * Request logging middleware.
         *
         * WHY:
         * - Logs every incoming request for debugging and monitoring
         * - Captures method, URL, user, response status and execution time
         * - Helps identify performance bottlenecks and failing endpoints
         *
         * NOTE:
         * Placed AFTER CORS to ensure even preflight requests are handled properly.
         */
        $middleware->append(LogRequestMiddleware::class);

        /**
         * Middleware aliases.
         *
         * WHY:
         * Provides readable and maintainable route definitions:
         *
         * Example:
         * ->middleware('permission:users.edit')
         * ->middleware('role:admin')
         *
         * Instead of using full class names everywhere.
         */
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
        ]);
    })

    /**
     * ------------------------------------------------------------
     * Exception Handling
     * ------------------------------------------------------------
     *
     * Customize how exceptions are handled and rendered.
     * (currently default behavior is used)
     */
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    /**
     * Create and return application instance
     */
    ->create();
