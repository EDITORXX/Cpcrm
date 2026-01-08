<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $lead;
    public $assignedBy;

    public function __construct(Lead $lead, int $assignedBy)
    {
        $this->lead = $lead;
        $this->assignedBy = $assignedBy;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'lead_assigned',
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'assigned_by' => $this->assignedBy,
            'message' => "A new lead '{$this->lead->name}' has been assigned to you.",
        ];
    }
}

