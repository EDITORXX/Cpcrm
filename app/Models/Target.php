<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use App\Models\Prospect;
use App\Models\Meeting;
use App\Models\SiteVisit;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_month',
        'target_visits',
        'target_meetings',
        'target_closers',
        'target_prospects_extract',
        'target_prospects_verified',
        'target_calls',
    ];

    protected $casts = [
        'target_month' => 'date',
        'target_visits' => 'integer',
        'target_meetings' => 'integer',
        'target_closers' => 'integer',
        'target_prospects_extract' => 'integer',
        'target_prospects_verified' => 'integer',
        'target_calls' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get count of prospects extracted by user in target month
     */
    public function getProspectsExtractedCount(): int
    {
        $targetMonth = \Carbon\Carbon::parse($this->target_month);
        return Prospect::where('telecaller_id', $this->user_id)
            ->whereYear('created_at', $targetMonth->year)
            ->whereMonth('created_at', $targetMonth->month)
            ->count();
    }

    /**
     * Get count of prospects verified by user in target month
     */
    public function getProspectsVerifiedCount(): int
    {
        $targetMonth = \Carbon\Carbon::parse($this->target_month);
        return Prospect::where('telecaller_id', $this->user_id)
            ->where('verification_status', 'verified')
            ->whereYear('verified_at', $targetMonth->year)
            ->whereMonth('verified_at', $targetMonth->month)
            ->count();
    }

    /**
     * Get count of calls completed by user in target month
     */
    public function getCallsCompletedCount(): int
    {
        $targetMonth = \Carbon\Carbon::parse($this->target_month);
        return \App\Models\TelecallerTask::where('assigned_to', $this->user_id)
            ->where('task_type', 'calling')
            ->where('status', 'completed')
            ->whereYear('completed_at', $targetMonth->year)
            ->whereMonth('completed_at', $targetMonth->month)
            ->count();
    }

    /**
     * Get progress percentage for a specific field
     */
    public function getProgressPercentage(string $field): float
    {
        $targetField = 'target_' . $field;
        $targetValue = $this->$targetField ?? 0;

        if ($targetValue == 0) {
            return 0;
        }

        $actualValue = match($field) {
            'prospects_extract' => $this->getProspectsExtractedCount(),
            'prospects_verified' => $this->getProspectsVerifiedCount(),
            'calls' => $this->getCallsCompletedCount(),
            default => 0,
        };

        return min(100, round(($actualValue / $targetValue) * 100, 2));
    }

    /**
     * Get count of meetings completed (verified) by user in target month
     * Excludes rescheduled meetings from achievement counts
     */
    public function getMeetingsCompletedCount(): int
    {
        $targetMonth = \Carbon\Carbon::parse($this->target_month);
        $query = Meeting::where('created_by', $this->user_id)
            ->where('verification_status', 'verified')
            ->where('is_converted', false) // Exclude converted meetings
            ->whereYear('verified_at', $targetMonth->year)
            ->whereMonth('verified_at', $targetMonth->month);
        
        // Only filter by is_rescheduled if column exists
        if (SchemaFacade::hasColumn('meetings', 'is_rescheduled')) {
            $query->where('is_rescheduled', false);
        }
        
        return $query->count();
    }

    /**
     * Get count of site visits completed (verified) by user in target month
     * Excludes rescheduled site visits from achievement counts
     */
    public function getSiteVisitsCompletedCount(): int
    {
        $targetMonth = \Carbon\Carbon::parse($this->target_month);
        $query = SiteVisit::where('created_by', $this->user_id)
            ->where('verification_status', 'verified')
            ->whereYear('verified_at', $targetMonth->year)
            ->whereMonth('verified_at', $targetMonth->month);
        
        // Only filter by is_rescheduled if column exists
        if (SchemaFacade::hasColumn('site_visits', 'is_rescheduled')) {
            $query->where('is_rescheduled', false);
        }
        
        return $query->count();
    }

    /**
     * Get count of closers (verified closers) by user in target month
     * Excludes rescheduled site visits from achievement counts
     */
    public function getClosersCount(): int
    {
        $targetMonth = \Carbon\Carbon::parse($this->target_month);
        // Closers = Site visits with closer_status = 'verified'
        $query = SiteVisit::where('created_by', $this->user_id)
            ->where('closer_status', 'verified')
            ->whereYear('closer_verified_at', $targetMonth->year)
            ->whereMonth('closer_verified_at', $targetMonth->month);
        
        // Only filter by is_rescheduled if column exists
        if (SchemaFacade::hasColumn('site_visits', 'is_rescheduled')) {
            $query->where('is_rescheduled', false);
        }
        
        return $query->count();
    }

    /**
     * Get progress percentage for meetings, visits, closers
     */
    public function getAchievementProgress(string $type): array
    {
        $targetField = match($type) {
            'meetings' => 'target_meetings',
            'visits' => 'target_visits',
            'closers' => 'target_closers',
            default => null,
        };

        if (!$targetField) {
            return ['target' => 0, 'achieved' => 0, 'percentage' => 0];
        }

        $target = $this->$targetField ?? 0;
        
        $achieved = match($type) {
            'meetings' => $this->getMeetingsCompletedCount(),
            'visits' => $this->getSiteVisitsCompletedCount(),
            'closers' => $this->getClosersCount(),
            default => 0,
        };

        $percentage = $target > 0 ? min(100, round(($achieved / $target) * 100, 2)) : 0;

        return [
            'target' => $target,
            'achieved' => $achieved,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get all progress data
     */
    public function getProgressData(): array
    {
        return [
            'prospects_extract' => [
                'target' => $this->target_prospects_extract,
                'actual' => $this->getProspectsExtractedCount(),
                'percentage' => $this->getProgressPercentage('prospects_extract'),
            ],
            'prospects_verified' => [
                'target' => $this->target_prospects_verified,
                'actual' => $this->getProspectsVerifiedCount(),
                'percentage' => $this->getProgressPercentage('prospects_verified'),
            ],
            'calls' => [
                'target' => $this->target_calls,
                'actual' => $this->getCallsCompletedCount(),
                'percentage' => $this->getProgressPercentage('calls'),
            ],
            'meetings' => $this->getAchievementProgress('meetings'),
            'visits' => $this->getAchievementProgress('visits'),
            'closers' => $this->getAchievementProgress('closers'),
        ];
    }
}
