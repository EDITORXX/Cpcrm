<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SmartImportLeadAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_id',
        'lead_id',
        'assigned_to',
        'assigned_by',
        'rule_applied',
        'priority_level',
        'assignment_method',
        'is_queued',
        'queued_reason',
        'sla_started_at',
        'sla_met_at',
        'sla_breached',
        'escalated_at',
        'escalated_to',
        'override_user_id',
        'override_reason',
        'override_at',
    ];

    protected $casts = [
        'priority_level' => 'integer',
        'is_queued' => 'boolean',
        'sla_breached' => 'boolean',
        'sla_started_at' => 'datetime',
        'sla_met_at' => 'datetime',
        'escalated_at' => 'datetime',
        'override_at' => 'datetime',
    ];

    public function execution(): BelongsTo
    {
        return $this->belongsTo(SmartImportExecution::class, 'execution_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function escalatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function overrideUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_user_id');
    }

    public function slaTracking(): HasOne
    {
        return $this->hasOne(SlaTracking::class, 'lead_assignment_id');
    }
}
