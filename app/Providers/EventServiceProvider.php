<?php

namespace App\Providers;

use App\Events\RegisteredEvent;
use App\Events\PlanOfferEvent;
use App\Listeners\SendEmailVerificationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\UserRegisteredEvent;
use App\Listeners\SendPlanOfferEmailListener;
use App\Listeners\SendUserRegisteredEmailListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        RegisteredEvent::class => [
            SendEmailVerificationListener::class,
        ],
        UserRegisteredEvent::class => [
            SendUserRegisteredEmailListener::class,
        ],
        PlanOfferEvent::class => [
            SendPlanOfferEmailListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
