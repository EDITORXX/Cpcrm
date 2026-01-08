<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartImportExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id',
        'status',
        'total_leads',
        'imported_leads',
        'skipped_leads',
        'failed_leads',
        'duplicate_leads',
        'queued_leads',
        'execution_log',
        'error_log',
        'started_at',
        'completed_at',
        'executed_by',
    ];

    protected $casts = [
        'total_leads' => 'integer',
        'imported_leads' => 'integer',
        'skipped_leads' => 'integer',
        'failed_leads' => 'integer',
        'duplicate_leads' => 'integer',
        'queued_leads' => 'integer',
        'execution_log' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(SmartImportAutomation::class, 'automation_id');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function leadAssignments(): HasMany
    {
        return $this->hasMany(SmartImportLeadAssignment::class, 'execution_id');
    }
}
