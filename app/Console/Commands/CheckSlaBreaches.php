<?php

namespace App\Console\Commands;

use App\Services\SlaTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSlaBreaches extends Command
{
    protected $signature = 'smart-import:check-sla';
    protected $description = 'Check for SLA breaches and trigger escalations';

    protected $slaService;

    public function __construct(SlaTrackingService $slaService)
    {
        parent::__construct();
        $this->slaService = $slaService;
    }

    public function handle()
    {
        $this->info('Checking for SLA breaches...');

        $breached = $this->slaService->checkSlaBreaches();

        if (empty($breached)) {
            $this->info('No SLA breaches found.');
            return 0;
        }

        $this->info('Found ' . count($breached) . ' breached SLA(s).');
        
        foreach ($breached as $sla) {
            $this->line("SLA breached for assignment ID: {$sla->lead_assignment_id}");
        }

        return 0;
    }
}
