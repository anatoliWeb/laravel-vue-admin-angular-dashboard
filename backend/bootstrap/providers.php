<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;

/**
 * Application Service Providers
 *
 * WHY:
 * This file defines all service providers that are loaded on application boot.
 * Providers are responsible for:
 * - binding services into the container
 * - registering event listeners
 * - bootstrapping core application logic
 *
 * Keep this list minimal and explicit to maintain clarity of application lifecycle.
 */
return [

    // Core application bindings and boot logic
    AppServiceProvider::class,

    // Event → Listener mappings (e.g. activity logging, observers)
    EventServiceProvider::class,

];
