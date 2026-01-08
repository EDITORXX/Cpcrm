<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use App\Events\LeadAssigned;
use App\Services\UserAvailabilityService;
use Illuminate\Support\Facades\Log;

class SmartAssignmentService
{
    protected $availabilityService;
    protected static $roundRobinCounters = [];

    public function __construct(UserAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Assign lead based on automation configuration
     */
    public function assignLead(
        Lead $lead,
        array $automationConfig,
        int $assignedBy
    ): ?int {
        // Get assignment mode
        $assignmentMode = $automationConfig['assignment_mode'] ?? 'percentage';
        $distributionConfig = $automationConfig['distribution_config'] ?? [];
        $conditions = $automationConfig['conditions'] ?? [];
        $fallbackUserId = $automationConfig['fallback_user_id'] ?? null;

        // First, evaluate conditions in priority order
        $matchedUser = $this->evaluateConditions($lead, $conditions);

        if ($matchedUser) {
            return $this->assignToUser($lead, $matchedUser, $assignedBy, 'condition', 'Condition matched');
        }

        // If no condition matched, use distribution mode
        $assignedUserId = null;

        switch ($assignmentMode) {
            case 'percentage':
                $assignedUserId = $this->assignByPercentage($lead, $distributionConfig, $assignedBy);
                break;

            case 'fixed_count':
                $assignedUserId = $this->assignByFixedCount($lead, $distributionConfig, $assignedBy);
                break;

            case 'round_robin':
                $assignedUserId = $this->assignByRoundRobin($lead, $distributionConfig, $assignedBy);
                break;
        }

        // If still no assignment, use fallback
        if (!$assignedUserId && $fallbackUserId) {
            return $this->assignToUser($lead, $fallbackUserId, $assignedBy, 'fallback', 'Fallback user');
        }

        return $assignedUserId;
    }

    /**
     * Evaluate conditions in priority order
     */
    protected function evaluateConditions(Lead $lead, array $conditions): ?int
    {
        if (empty($conditions)) {
            return null;
        }

        // Sort by priority (1 = highest)
        usort($conditions, function ($a, $b) {
            return ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999);
        });

        foreach ($conditions as $condition) {
            if ($this->evaluateCondition($lead, $condition)) {
                // Get user from condition
                $userId = $condition['assign_to'] ?? null;
                if ($userId && $this->availabilityService->isUserAvailable($userId)) {
                    return $userId;
                }
            }
        }

        return null;
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition(Lead $lead, array $condition): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;
        $value = $condition['value'] ?? null;
        $logic = $condition['logic'] ?? 'AND'; // AND or OR for grouped conditions

        if (!$field || !$operator) {
            return false;
        }

        // Handle grouped conditions
        if (isset($condition['conditions']) && is_array($condition['conditions'])) {
            $results = [];
            foreach ($condition['conditions'] as $subCondition) {
                $results[] = $this->evaluateCondition($lead, $subCondition);
            }

            if ($logic === 'OR') {
                return in_array(true, $results);
            } else {
                return !in_array(false, $results);
            }
        }

        // Get lead field value
        $leadValue = $lead->$field ?? null;

        // Convert to appropriate type
        if (is_numeric($value) && is_numeric($leadValue)) {
            $leadValue = (float) $leadValue;
            $value = (float) $value;
        } else {
            $leadValue = (string) $leadValue;
            $value = (string) $value;
        }

        // Evaluate operator
        switch ($operator) {
            case '=':
            case 'equals':
                return $leadValue == $value;

            case '!=':
            case 'not_equals':
                return $leadValue != $value;

            case '>':
            case 'greater_than':
                return $leadValue > $value;

            case '>=':
            case 'greater_than_equal':
                return $leadValue >= $value;

            case '<':
            case 'less_than':
                return $leadValue < $value;

            case '<=':
            case 'less_than_equal':
                return $leadValue <= $value;

            case 'contains':
                return stripos($leadValue, $value) !== false;

            case 'not_contains':
                return stripos($leadValue, $value) === false;

            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                return in_array($leadValue, array_map('trim', $values));

            case 'not_in':
                $values = is_array($value) ? $value : explode(',', $value);
                return !in_array($leadValue, array_map('trim', $values));

            case 'starts_with':
                return stripos($leadValue, $value) === 0;

            case 'ends_with':
                return stripos(strrev($leadValue), strrev($value)) === 0;

            case 'empty':
                return empty($leadValue);

            case 'not_empty':
                return !empty($leadValue);

            default:
                return false;
        }
    }

    /**
     * Assign by percentage distribution
     */
    protected function assignByPercentage(Lead $lead, array $distributionConfig, int $assignedBy): ?int
    {
        $users = $this->getAvailableUsers($distributionConfig);
        if (empty($users)) {
            return null;
        }

        // Build weighted distribution
        $distribution = [];
        foreach ($users as $userConfig) {
            $userId = $userConfig['user_id'] ?? null;
            $percentage = (float) ($userConfig['percentage'] ?? 0);

            if (!$userId || $percentage <= 0) {
                continue;
            }

            // Convert percentage to integer slots (e.g., 30% = 30 slots)
            $slots = (int) ($percentage * 10); // Use 10x for better precision
            for ($i = 0; $i < $slots; $i++) {
                $distribution[] = $userId;
            }
        }

        if (empty($distribution)) {
            return null;
        }

        // Use round-robin with counter
        $counterKey = 'percentage_' . md5(json_encode($distributionConfig));
        if (!isset(self::$roundRobinCounters[$counterKey])) {
            self::$roundRobinCounters[$counterKey] = 0;
        }

        $counter = self::$roundRobinCounters[$counterKey];
        $assignedUserId = $distribution[$counter % count($distribution)];
        self::$roundRobinCounters[$counterKey] = ($counter + 1) % count($distribution);

        return $this->assignToUser($lead, $assignedUserId, $assignedBy, 'percentage', 'Percentage distribution');
    }

    /**
     * Assign by fixed count distribution
     */
    protected function assignByFixedCount(Lead $lead, array $distributionConfig, int $assignedBy): ?int
    {
        $users = $this->getAvailableUsers($distributionConfig);
        if (empty($users)) {
            return null;
        }

        // Find user with lowest current count
        $userCounts = [];
        foreach ($users as $userConfig) {
            $userId = $userConfig['user_id'] ?? null;
            $maxCount = (int) ($userConfig['count'] ?? 0);

            if (!$userId || $maxCount <= 0) {
                continue;
            }

            // Get current assignment count for this user (from today)
            $availability = $this->availabilityService->getOrCreateAvailability($userId);
            $currentCount = $availability->current_day_leads;

            if ($currentCount < $maxCount) {
                $userCounts[$userId] = $currentCount;
            }
        }

        if (empty($userCounts)) {
            return null; // All users at limit
        }

        // Assign to user with lowest count
        asort($userCounts);
        $assignedUserId = array_key_first($userCounts);

        return $this->assignToUser($lead, $assignedUserId, $assignedBy, 'fixed_count', 'Fixed count distribution');
    }

    /**
     * Assign by round robin
     */
    protected function assignByRoundRobin(Lead $lead, array $distributionConfig, int $assignedBy): ?int
    {
        $users = $this->getAvailableUsers($distributionConfig);
        if (empty($users)) {
            return null;
        }

        $userIds = array_column($users, 'user_id');
        $userIds = array_filter($userIds);

        if (empty($userIds)) {
            return null;
        }

        // Use round-robin with counter
        $counterKey = 'round_robin_' . md5(json_encode($userIds));
        if (!isset(self::$roundRobinCounters[$counterKey])) {
            self::$roundRobinCounters[$counterKey] = 0;
        }

        $counter = self::$roundRobinCounters[$counterKey];
        $assignedUserId = $userIds[array_keys($userIds)[$counter % count($userIds)]];
        self::$roundRobinCounters[$counterKey] = ($counter + 1) % count($userIds);

        return $this->assignToUser($lead, $assignedUserId, $assignedBy, 'round_robin', 'Round robin');
    }

    /**
     * Get available users from distribution config
     */
    protected function getAvailableUsers(array $distributionConfig): array
    {
        $available = [];
        
        foreach ($distributionConfig as $userConfig) {
            $userId = $userConfig['user_id'] ?? null;
            $isActive = $userConfig['is_active'] ?? true;

            if (!$userId || !$isActive) {
                continue;
            }

            // Check availability
            if ($this->availabilityService->isUserAvailable($userId)) {
                $available[] = $userConfig;
            }
        }

        return $available;
    }

    /**
     * Assign lead to specific user
     */
    protected function assignToUser(Lead $lead, int $userId, int $assignedBy, string $method, string $ruleApplied): ?int
    {
        try {
            // Check if user is still available
            if (!$this->availabilityService->isUserAvailable($userId)) {
                return null; // User not available, will be queued
            }

            // Deactivate existing assignments
            $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);

            // Create new assignment
            \App\Models\LeadAssignment::create([
                'lead_id' => $lead->id,
                'assigned_to' => $userId,
                'assigned_by' => $assignedBy,
                'assignment_type' => 'primary',
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            // Increment daily lead count
            $this->availabilityService->incrementDailyLeads($userId);

            // Fire LeadAssigned event to trigger task creation for telecallers
            event(new LeadAssigned($lead, $userId, $assignedBy));

            return $userId;

        } catch (\Exception $e) {
            Log::error("Assignment error for lead {$lead->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate distribution config
     */
    public function validateDistributionConfig(array $distributionConfig, string $mode): array
    {
        $errors = [];

        if (empty($distributionConfig)) {
            $errors[] = "At least one user must be configured for distribution.";
            return $errors;
        }

        $totalPercentage = 0;
        $activeUsers = 0;

        foreach ($distributionConfig as $index => $userConfig) {
            $userId = $userConfig['user_id'] ?? null;
            $isActive = $userConfig['is_active'] ?? true;

            if (!$userId) {
                $errors[] = "User configuration at index {$index} is missing user_id.";
                continue;
            }

            if (!$isActive) {
                continue; // Skip inactive users
            }

            $activeUsers++;

            if ($mode === 'percentage') {
                $percentage = (float) ($userConfig['percentage'] ?? 0);
                $totalPercentage += $percentage;

                if ($percentage < 0 || $percentage > 100) {
                    $errors[] = "User {$userId} has invalid percentage: {$percentage}";
                }
            } elseif ($mode === 'fixed_count') {
                $count = (int) ($userConfig['count'] ?? 0);
                if ($count < 0) {
                    $errors[] = "User {$userId} has invalid count: {$count}";
                }
            }
        }

        if ($activeUsers === 0) {
            $errors[] = "At least one active user is required.";
        }

        if ($mode === 'percentage' && abs($totalPercentage - 100) > 0.01) {
            $errors[] = "Total percentage must equal 100%. Current total: {$totalPercentage}%";
        }

        return $errors;
    }
}

