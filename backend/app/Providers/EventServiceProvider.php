<?php

namespace App\Providers;

use App\Events\Users\UserCreated;
use App\Events\Users\UserUpdated;
use App\Listeners\Users\LogUserCreatedActivity;
use App\Listeners\Users\LogUserUpdatedActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider.
 *
 * Registers application events and listeners.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserCreated::class => [
            LogUserCreatedActivity::class,
        ],
        UserUpdated::class => [
            LogUserUpdatedActivity::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
