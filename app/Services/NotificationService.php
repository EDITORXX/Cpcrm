<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\BroadcastMessage;
use App\Models\User;
use App\Models\Role;
use App\Events\NewLeadNotification;
use App\Events\NewVerificationNotification;
use App\Events\FollowupNotification;
use App\Events\AdminBroadcast;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify user about new lead assignment
     */
    public function notifyNewLead(User $user, $lead, string $actionUrl): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_NEW_LEAD,
            'title' => 'New Lead Assigned',
            'message' => "New lead assigned: {$lead->name}",
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => $actionUrl,
            'data' => [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
            ],
        ]);

        // Broadcast via Pusher
        event(new NewLeadNotification($notification));

        return $notification;
    }

    /**
     * Notify user about new verification pending
     */
    public function notifyNewVerification(User $user, string $type, string $title, string $message, string $actionUrl, array $data = []): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_NEW_VERIFICATION,
            'title' => $title,
            'message' => $message,
            'action_type' => AppNotification::ACTION_VERIFICATION,
            'action_url' => $actionUrl,
            'data' => array_merge([
                'verification_type' => $type,
            ], $data),
        ]);

        // Broadcast via Pusher
        event(new NewVerificationNotification($notification));

        return $notification;
    }

    /**
     * Notify user about upcoming follow-up
     */
    public function notifyFollowup(User $user, $followup, string $actionUrl): AppNotification
    {
        $leadName = $followup->lead ? $followup->lead->name : 'Lead';
        $message = "Follow-up reminder: {$leadName}";
        if ($followup->scheduled_at) {
            $message .= " at " . $followup->scheduled_at->format('M d, Y h:i A');
        }
        
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_FOLLOWUP_REMINDER,
            'title' => 'Follow-up Reminder',
            'message' => $message,
            'action_type' => AppNotification::ACTION_FOLLOWUP,
            'action_url' => $actionUrl,
            'data' => [
                'followup_id' => $followup->id,
                'lead_id' => $followup->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $followup->scheduled_at ? $followup->scheduled_at->toIso8601String() : null,
            ],
        ]);

        // Broadcast via Pusher
        event(new FollowupNotification($notification));

        return $notification;
    }

    /**
     * Send admin broadcast message to users
     */
    public function sendBroadcast(User $sender, string $title, string $message, string $targetType = 'all_users', array $targetRoles = []): array
    {
        // Create broadcast message record
        $broadcast = BroadcastMessage::create([
            'sender_id' => $sender->id,
            'title' => $title,
            'message' => $message,
            'target_type' => $targetType,
            'target_roles' => $targetType === 'role_based' ? $targetRoles : null,
        ]);

        // Get target users
        $targetUsers = $this->getTargetUsers($targetType, $targetRoles);

        $notifications = [];
        foreach ($targetUsers as $user) {
            $notification = AppNotification::create([
                'user_id' => $user->id,
                'type' => AppNotification::TYPE_ADMIN_BROADCAST,
                'title' => $title,
                'message' => $message,
                'action_type' => AppNotification::ACTION_BROADCAST,
                'action_url' => null,
                'data' => [
                    'broadcast_id' => $broadcast->id,
                    'sender_name' => $sender->name,
                ],
            ]);

            $notifications[] = $notification;
        }

        // Broadcast via Pusher to all users
        event(new AdminBroadcast($broadcast, $notifications));

        return [
            'broadcast' => $broadcast,
            'notifications' => $notifications,
            'sent_to' => count($targetUsers),
        ];
    }

    /**
     * Get target users based on target type and roles
     */
    private function getTargetUsers(string $targetType, array $targetRoles = []): \Illuminate\Support\Collection
    {
        if ($targetType === 'all_users') {
            return User::where('is_active', true)->get();
        }

        // Role-based targeting
        if (!empty($targetRoles)) {
            $roleIds = Role::whereIn('slug', $targetRoles)->pluck('id');
            return User::where('is_active', true)
                ->whereIn('role_id', $roleIds)
                ->get();
        }

        return collect();
    }
}
