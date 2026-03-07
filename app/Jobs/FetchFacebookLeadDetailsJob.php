<?php

namespace App\Jobs;

use App\Models\FbForm;
use App\Models\FbLead;
use App\Models\FbWebhookEvent;
use App\Services\FacebookGraphService;
use App\Services\FacebookLeadMappingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchFacebookLeadDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $leadgenId,
        public int $fbFormId
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        if (FbLead::where('leadgen_id', $this->leadgenId)->exists()) {
            return;
        }

        $fbForm = FbForm::with('page')->find($this->fbFormId);
        if (!$fbForm || !$fbForm->page) {
            return;
        }

        $token = $fbForm->page->page_access_token;
        if (empty($token)) {
            $this->failEvent('No token for page');
            return;
        }

        $settings = \App\Models\FbLeadAdsSettings::getSettings();
        $graphVersion = $settings->graph_version ?? 'v18.0';
        $client = FacebookGraphService::fromToken($token, $graphVersion);
        $result = $client->getLeadDetails($this->leadgenId);

        if (!$result['success']) {
            $this->failEvent($result['error'] ?? 'Unknown error');
            return;
        }

        $data = $result['data'];
        $fieldData = $data['field_data'] ?? [];

        $mappingService = new FacebookLeadMappingService();
        $flatFieldData = $mappingService->fieldDataToFlat($fieldData);

        FbLead::create([
            'leadgen_id' => $this->leadgenId,
            'fb_form_id' => $this->fbFormId,
            'field_data_json' => $flatFieldData,
            'raw_response_json' => $data,
        ]);

        FbWebhookEvent::where('leadgen_id', $this->leadgenId)->where('status', 'received')->update(['status' => 'processed']);
    }

    protected function failEvent(string $error): void
    {
        FbWebhookEvent::where('leadgen_id', $this->leadgenId)->where('status', 'received')->update([
            'status' => 'failed',
            'error' => $error,
        ]);
        Log::warning('FetchFacebookLeadDetailsJob failed', ['leadgen_id' => $this->leadgenId, 'error' => $error]);
    }
}
