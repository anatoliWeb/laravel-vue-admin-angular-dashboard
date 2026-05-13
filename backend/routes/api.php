<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RealtimeController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\TranslationManagementController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\TranslationController;

/**
 * ----------------------------------------------------------------
 * API Routes
 * ----------------------------------------------------------------
 *
 * This file contains all API endpoints for the application.
 *
 * ARCHITECTURE:
 * - API-first backend
 * - Stateless authentication
 * - JSON responses only
 * - Shared contract for Angular/Vue/mobile clients
 *
 * IMPORTANT:
 * All API endpoints must follow the standardized
 * response structure defined in BaseController
 * and global exception handling.
 */

/**
 * ----------------------------------------------------------------
 * Legacy Flat API Routes
 * ----------------------------------------------------------------
 *
 * TEMPORARY:
 * Current routes are kept for backward compatibility
 * during transition to versioned API architecture.
 *
 * These routes will eventually be migrated to:
 * /api/v1/*
 */

/**
 * Health Check Endpoint
 */
Route::get('/health', [HealthController::class, 'show']);

/**
 * Authentication Endpoints
 */
Route::post('/token', [AuthController::class, 'token']);
Route::post('/login', [AuthController::class, 'token']);


Route::middleware(['web'])->prefix('v1/auth/session')->group(function () {

    Route::post('/login', [AuthController::class, 'sessionLogin']);
    Route::get('/me', [AuthController::class, 'sessionUser']);
    Route::post('/logout', [AuthController::class, 'sessionLogout']);

});

/**
 * Protected Legacy Routes
 */
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view');

    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('permission:users.view');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit');

    Route::patch('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete');

    Route::get('/stats', [StatsController::class, 'index']);

    Route::get('/meta', [MetaController::class, 'index']);

    Route::get('/tokens', [TokenController::class, 'index'])
        ->middleware('permission:tokens.view');

    Route::post('/tokens', [TokenController::class, 'store'])
        ->middleware('permission:tokens.create');

    Route::delete('/tokens/{id}', [TokenController::class, 'destroy'])
        ->middleware('permission:tokens.delete');
});

/**
 * ----------------------------------------------------------------
 * API Version 1
 * ----------------------------------------------------------------
 *
 * WHY:
 * Versioned APIs allow:
 * - safe future API changes
 * - frontend compatibility
 * - mobile app support
 * - easier microservice extraction
 * - long-term maintainability
 *
 * TARGET STRUCTURE:
 *
 * /api/v1/auth/login
 * /api/v1/users
 * /api/v1/stats
 *
 * NOTE:
 * Current implementation reuses existing controllers
 * during migration to versioned architecture.
 */

Route::prefix('v1')
    ->as('api.v1.')
    ->group(function () {

        /**
         * --------------------------------------------------------
         * Public v1 Endpoints
         * --------------------------------------------------------
         */

        Route::get('/health', [HealthController::class, 'show'])
            ->name('health');

        /**
         * --------------------------------------------------------
         * Authentication
         * --------------------------------------------------------
         */

        Route::prefix('auth')
            ->as('auth.')
            ->group(function () {

                Route::post('/login', [AuthController::class, 'token'])
                    ->name('login');

                Route::post('/token', [AuthController::class, 'token'])
                    ->name('token');

                Route::post('/session/login', [AuthController::class, 'sessionLogin'])
                    ->name('session.login');
            });

        /**
         * --------------------------------------------------------
         * Public runtime localization preload
         * --------------------------------------------------------
         *
         * WHY:
         * Bootstrap must work for guests (login screen included), so runtime
         * translation preload cannot be protected by auth:sanctum.
         */
        Route::prefix('translations')
            ->as('translations.')
            ->group(function () {
                Route::get('/', [TranslationController::class, 'index'])
                    ->name('index');
            });

        /**
         * --------------------------------------------------------
         * Protected v1 API
         * --------------------------------------------------------
         */

        Route::middleware('auth:sanctum')
            ->group(function () {
                Route::prefix('auth')
                    ->as('auth.')
                    ->group(function () {
                        Route::get('/me', [AuthController::class, 'me'])
                            ->name('me');
                        Route::post('/logout', [AuthController::class, 'logout'])
                            ->name('logout');
                        Route::get('/session/me', [AuthController::class, 'sessionUser'])
                            ->name('session.me');
                        Route::post('/session/logout', [AuthController::class, 'sessionLogout'])
                            ->name('session.logout');
                    });

                /**
                 * ------------------------------------------------
                 * Users
                 * ------------------------------------------------
                 */

                Route::prefix('users')
                    ->as('users.')
                    ->group(function () {

                        Route::get('/', [UserController::class, 'index'])
                            ->middleware('permission:users.view')
                            ->name('index');

                        Route::get('/{user}', [UserController::class, 'show'])
                            ->middleware('permission:users.view')
                            ->name('show');

                        Route::post('/', [UserController::class, 'store'])
                            ->middleware('permission:users.create')
                            ->name('store');

                        Route::put('/{user}', [UserController::class, 'update'])
                            ->middleware('permission:users.edit')
                            ->name('update');

                        Route::patch('/{user}', [UserController::class, 'update'])
                            ->middleware('permission:users.edit')
                            ->name('patch');

                        Route::delete('/{user}', [UserController::class, 'destroy'])
                            ->middleware('permission:users.delete')
                            ->name('destroy');
                    });

                Route::prefix('roles')
                    ->as('roles.')
                    ->group(function () {
                        Route::get('/', [RoleController::class, 'index'])
                            ->middleware('permission:roles.view')
                            ->name('index');
                        Route::post('/', [RoleController::class, 'store'])
                            ->middleware('permission:roles.create')
                            ->name('store');
                        Route::put('/{role}', [RoleController::class, 'update'])
                            ->middleware('permission:roles.edit')
                            ->name('update');
                        Route::patch('/{role}', [RoleController::class, 'update'])
                            ->middleware('permission:roles.edit')
                            ->name('patch');
                    });

                Route::prefix('permissions')
                    ->as('permissions.')
                    ->group(function () {
                        Route::get('/', [PermissionController::class, 'index'])
                            ->middleware('permission:permissions.view')
                            ->name('index');
                        Route::post('/', [PermissionController::class, 'store'])
                            ->middleware('permission:permissions.create')
                            ->name('store');
                        Route::put('/{permission}', [PermissionController::class, 'update'])
                            ->middleware('permission:permissions.edit')
                            ->name('update');
                        Route::patch('/{permission}', [PermissionController::class, 'update'])
                            ->middleware('permission:permissions.edit')
                            ->name('patch');
                    });

                /**
                 * ------------------------------------------------
                 * Dashboard / System
                 * ------------------------------------------------
                 */

                Route::get('/stats', [StatsController::class, 'index'])
                    ->name('stats');

                Route::get('/meta', [MetaController::class, 'index'])
                    ->name('meta');

                /**
                 * ------------------------------------------------
                 * Settings
                 * ------------------------------------------------
                 */

                Route::prefix('settings')
                    ->as('settings.')
                    ->group(function () {
                        Route::get('/', [SettingsController::class, 'index'])
//                            ->middleware('permission:settings.view')
                            ->name('index');

                        Route::get('/preload', [SettingsController::class, 'preload'])
                            ->middleware('auth:sanctum')
                            ->name('preload');

                        Route::get('/effective', [SettingsController::class, 'effective'])
//                            ->middleware('permission:settings.view')
                            ->name('effective');

                        Route::post('/', [SettingsController::class, 'store'])
                            ->middleware('permission:settings.edit')
                            ->name('store');

                        Route::put('/{setting}', [SettingsController::class, 'update'])
                            ->middleware('permission:settings.edit')
                            ->name('update');

                        Route::patch('/{setting}', [SettingsController::class, 'update'])
                            ->middleware('permission:settings.edit')
                            ->name('patch');

                        Route::delete('/{setting}', [SettingsController::class, 'destroy'])
                            ->middleware('permission:settings.edit')
                            ->name('destroy');
                    });

                /**
                 * ------------------------------------------------
                 * API Tokens
                 * ------------------------------------------------
                 */

                Route::prefix('tokens')
                    ->as('tokens.')
                    ->group(function () {

                        Route::get('/', [TokenController::class, 'index'])
                            ->middleware('permission:tokens.view')
                            ->name('index');

                        Route::post('/', [TokenController::class, 'store'])
                            ->middleware('permission:tokens.create')
                            ->name('store');

                        Route::delete('/{id}', [TokenController::class, 'destroy'])
                            ->middleware('permission:tokens.delete')
                            ->name('destroy');
                    });

                /**
                 * ------------------------------------------------
                 * Localization / Translations
                 * ------------------------------------------------
                 */

                Route::prefix('translations')
                    ->as('translations.')
                    ->group(function () {
                        Route::get('/manage', [TranslationManagementController::class, 'index'])
//                            ->middleware('permission:translations.view')
                            ->name('manage.index');

                        Route::post('/manage', [TranslationManagementController::class, 'store'])
                            ->middleware('permission:translations.create')
                            ->name('manage.store');

                        Route::put('/manage/{translation}', [TranslationManagementController::class, 'update'])
                            ->middleware('permission:translations.edit')
                            ->name('manage.update');

                        Route::delete('/manage/{translation}', [TranslationManagementController::class, 'destroy'])
                            ->middleware('permission:translations.delete')
                            ->name('manage.destroy');
                    });

                /**
                 * ------------------------------------------------
                 * Realtime Debug
                 * ------------------------------------------------
                 */
                Route::prefix('realtime')
                    ->as('realtime.')
                    ->group(function () {
                        Route::post('/notify', [RealtimeController::class, 'notify'])
                            ->name('notify');
                    });

            });
    });
