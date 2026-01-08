<?php

namespace App\Listeners;

use App\Events\SiteVisitCreated;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSiteVisitCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SiteVisitCreated $event): void
    {
        $usersToNotify = [];

        // Notify assigned user
        if ($event->siteVisit->assigned_to) {
            $usersToNotify[] = $event->siteVisit->assigned_to;
        }

        // Notify manager
        if ($event->siteVisit->assignedTo && $event->siteVisit->assignedTo->manager_id) {
            $usersToNotify[] = $event->siteVisit->assignedTo->manager_id;
        }

        foreach ($usersToNotify as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->notify(new \App\Notifications\SiteVisitCreatedNotification($event->siteVisit));
            }
        }
    }
}

