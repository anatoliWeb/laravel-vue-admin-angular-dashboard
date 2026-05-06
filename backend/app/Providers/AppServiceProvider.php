<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\PersonalAccessTokenObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        PersonalAccessToken::observe(PersonalAccessTokenObserver::class);

        Gate::before(function (User $user, string $ability) {
            return $user->hasPermission($ability) ? true : null;
        });
    }
}
