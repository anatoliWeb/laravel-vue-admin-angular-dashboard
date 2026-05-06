<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

/**
 * Admin routes.
 *
 * All routes are:
 * - prefixed with /admin (in bootstrap/app.php)
 * - protected by auth (in bootstrap/app.php) + permission middleware here
 */
Route::group([], function () {

        /**
         * Dashboard
         */
        // WHY:
        // Routes are grouped by permission to enforce RBAC at route level.
        Route::middleware('permission:users.view')->group(function (): void {
            Route::get('/', [DashboardController::class, 'index'])
                ->name('dashboard');

            /**
             * Users read access
             */
            Route::get('/users', [UserController::class, 'index'])
                ->name('users.index');
        });

        Route::middleware('permission:users.create')->group(function (): void {
            Route::get('/users/create', [UserController::class, 'create'])
                ->name('users.create');

            Route::post('/users', [UserController::class, 'store'])
                ->name('users.store');
        });

        Route::middleware('permission:users.edit')->group(function (): void {
            Route::get('/users/{id}', [UserController::class, 'edit'])
                ->name('users.edit');

            Route::put('/users/{id}', [UserController::class, 'update'])
                ->name('users.update');
        });

        Route::middleware('permission:users.delete')->group(function (): void {
            Route::delete('/users/{id}', [UserController::class, 'destroy'])
                ->name('users.destroy');
        });

        /**
         * Token management
         */
        Route::middleware('permission:tokens.view')->group(function (): void {
            Route::get('/tokens', [TokenController::class, 'index'])
                ->name('tokens.index');
        });

        Route::middleware('permission:tokens.create')->group(function (): void {
            Route::post('/tokens', [TokenController::class, 'store'])
                ->name('tokens.store');
        });

        Route::middleware('permission:tokens.delete')->group(function (): void {
            Route::delete('/tokens/{id}', [TokenController::class, 'destroy'])
                ->name('tokens.destroy');
        });

        /**
         * Roles management
         */
        Route::middleware('permission:roles.view')->group(function (): void {
            Route::get('/roles', [RoleController::class, 'index'])
                ->name('roles.index');
        });

        Route::middleware('permission:roles.create')->group(function (): void {
            Route::get('/roles/create', [RoleController::class, 'create'])
                ->name('roles.create');

            Route::post('/roles', [RoleController::class, 'store'])
                ->name('roles.store');
        });

        Route::middleware('permission:roles.edit')->group(function (): void {
            Route::get('/roles/{id}', [RoleController::class, 'edit'])
                ->name('roles.edit');

            Route::put('/roles/{id}', [RoleController::class, 'update'])
                ->name('roles.update');
        });

        Route::middleware('permission:roles.delete')->group(function (): void {
            Route::delete('/roles/{id}', [RoleController::class, 'destroy'])
                ->name('roles.destroy');
        });

        /**
         * Permissions management
         */
        Route::middleware('permission:permissions.view')->group(function (): void {
            Route::get('/permissions', [PermissionController::class, 'index'])
                ->name('permissions.index');
        });

        Route::middleware('permission:permissions.create')->group(function (): void {
            Route::get('/permissions/create', [PermissionController::class, 'create'])
                ->name('permissions.create');

            Route::post('/permissions', [PermissionController::class, 'store'])
                ->name('permissions.store');
        });

        Route::middleware('permission:permissions.edit')->group(function (): void {
            Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])
                ->name('permissions.edit');

            Route::put('/permissions/{permission}', [PermissionController::class, 'update'])
                ->name('permissions.update');
        });

        Route::middleware('permission:permissions.delete')->group(function (): void {
            Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
                ->name('permissions.destroy');
        });
});
