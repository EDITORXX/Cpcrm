<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeadAssignment;
use App\Models\TelecallerTask;
use App\Services\TelecallerTaskService;
use Illuminate\Support\Facades\DB;

class CreateTasksForExistingLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:create-for-existing-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create calling tasks for all existing leads that are assigned to telecallers';

    protected $taskService;

    /**
     * Create a new command instance.
     */
    public function __construct(TelecallerTaskService $taskService)
    {
        parent::__construct();
        $this->taskService = $taskService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to create tasks for existing leads...');
        
        // Get all active lead assignments
        $assignments = LeadAssignment::where('is_active', true)
            ->with(['lead', 'assignedTo.role'])
            ->get();

        $this->info("Found {$assignments->count()} active lead assignments");

        $created = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($assignments->count());
        $bar->start();

        foreach ($assignments as $assignment) {
            try {
                $lead = $assignment->lead;
                $telecaller = $assignment->assignedTo;

                // Skip if lead or telecaller doesn't exist
                if (!$lead || !$telecaller) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Skip if assigned user is not a telecaller
                if (!$telecaller->isTelecaller()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Skip if lead is blocked
                if ($lead->is_blocked) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Check if a pending task already exists for this lead and telecaller
                $existingTask = TelecallerTask::where('lead_id', $lead->id)
                    ->where('assigned_to', $telecaller->id)
                    ->whereIn('status', ['pending', 'rescheduled'])
                    ->first();

                if ($existingTask) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Create the task
                $this->taskService->createCallingTask(
                    $lead,
                    $telecaller,
                    $assignment->assigned_by ?? 1
                );

                $created++;
                $bar->advance();

            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError processing assignment ID {$assignment->id}: " . $e->getMessage());
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Task creation completed!");
        $this->info("Created: {$created} tasks");
        $this->info("Skipped: {$skipped} assignments (already have tasks, not telecallers, or blocked leads)");
        $this->info("Errors: {$errors}");

        return Command::SUCCESS;
    }
}
