<?php

namespace App\Providers;

use App\Events\LeadAssigned;
use App\Events\LeadStatusUpdated;
use App\Events\ProspectSentForVerification;
use App\Events\SiteVisitCreated;
use App\Listeners\CreateManagerExecutiveTask;
use App\Listeners\CreateManagerVerificationCallTask;
use App\Listeners\CreateSiteVisitCallTask;
use App\Listeners\CreateTelecallerTask;
use App\Listeners\SendLeadAssignedNotification;
use App\Listeners\SendNewLeadNotification;
use App\Listeners\SendVerificationNotification;
use App\Listeners\SendFollowupNotification;
use App\Listeners\SendSiteVisitCreatedNotification;
use App\Listeners\UpdateGoogleSheetOnStatusChange;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LeadAssigned::class => [
            SendLeadAssignedNotification::class,
            SendNewLeadNotification::class,
            CreateTelecallerTask::class,
        ],
        LeadStatusUpdated::class => [
            UpdateGoogleSheetOnStatusChange::class,
        ],
        SiteVisitCreated::class => [
            SendSiteVisitCreatedNotification::class,
            CreateSiteVisitCallTask::class,
        ],
        ProspectSentForVerification::class => [
            CreateManagerVerificationCallTask::class,
            CreateManagerExecutiveTask::class,
            SendVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

