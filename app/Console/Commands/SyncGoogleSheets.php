<?php

namespace App\Console\Commands;

use App\Models\GoogleSheetsConfig;
use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGoogleSheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-sheets:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Google Sheets based on auto-sync settings';

    protected $sheetsService;

    public function __construct(GoogleSheetsService $sheetsService)
    {
        parent::__construct();
        $this->sheetsService = $sheetsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Google Sheets sync...');

        // Find all configs that need syncing
        $configs = GoogleSheetsConfig::where('is_active', true)
            ->where('auto_sync_enabled', true)
            ->get();

        $synced = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($configs as $config) {
            // Check if it's time to sync
            if ($config->last_sync_at) {
                $nextSyncTime = $config->last_sync_at->addMinutes($config->sync_interval_minutes);
                if (now()->lt($nextSyncTime)) {
                    $skipped++;
                    continue;
                }
            }

            try {
                $this->info("Syncing config ID: {$config->id} ({$config->sheet_name})...");
                
                $result = $this->sheetsService->syncGoogleSheets($config->id);
                
                $this->info("  ✓ Imported: {$result['imported']}, Skipped: {$result['skipped']}");
                $synced++;

            } catch (\Exception $e) {
                $this->error("  ✗ Error syncing config ID {$config->id}: " . $e->getMessage());
                Log::error("Google Sheets sync error for config {$config->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Sync completed. Synced: {$synced}, Skipped: {$skipped}, Errors: {$errors}");
        
        return Command::SUCCESS;
    }
}
