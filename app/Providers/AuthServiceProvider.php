<?php

namespace App\Providers;

use App\Models\CalendarEvent;
use App\Models\EmailAccount;
use App\Models\GoogleTask;
use App\Models\Ticket;
use App\Policies\CalendarEventPolicy;
use App\Policies\EmailAccountPolicy;
use App\Policies\GoogleTaskPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CalendarEvent::class => CalendarEventPolicy::class,
        EmailAccount::class => EmailAccountPolicy::class,
        GoogleTask::class => GoogleTaskPolicy::class,
        Ticket::class => TicketPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
