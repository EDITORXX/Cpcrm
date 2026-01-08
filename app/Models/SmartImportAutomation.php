<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartImportAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'lead_source',
        'file_path',
        'file_name',
        'file_type',
        'total_rows',
        'column_mapping',
        'duplicate_handling',
        'assignment_mode',
        'distribution_mode',
        'distribution_config',
        'conditions',
        'fallback_user_id',
        'sla_minutes',
        'escalation_user_id',
        'auto_create_call_task',
        'status',
        'created_by',
        'last_executed_at',
        'execution_count',
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'distribution_config' => 'array',
        'conditions' => 'array',
        'total_rows' => 'integer',
        'execution_count' => 'integer',
        'sla_minutes' => 'integer',
        'auto_create_call_task' => 'boolean',
        'last_executed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fallbackUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fallback_user_id');
    }

    public function escalationUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalation_user_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(SmartImportExecution::class, 'automation_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AutomationAuditLog::class, 'automation_id');
    }
}
