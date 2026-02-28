<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FbForm;
use App\Models\FbLeadAdsSettings;
use App\Models\FbPage;
use App\Services\FacebookGraphService;
use App\Services\FacebookLeadMappingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacebookLeadAdsController extends Controller
{
    /**
     * Landing: link to settings and list of configured forms (standalone section).
     */
    public function index()
    {
        $settings = FbLeadAdsSettings::getSettings();
        $hasToken = !empty($settings->page_access_token);
        $forms = collect([]);
        if ($hasToken && $settings->page_id) {
            $page = FbPage::where('page_id', $settings->page_id)->first();
            if ($page) {
                $forms = FbForm::where('fb_page_id', $page->id)->orderBy('form_name')->get();
            }
        }
        $webhookUrl = url('/api/webhooks/facebook/leads');

        return view('integrations.facebook-lead-ads.index', compact('settings', 'hasToken', 'forms', 'webhookUrl'));
    }

    /**
     * Settings form (token, graph version, page select after test).
     */
    public function settings()
    {
        $settings = FbLeadAdsSettings::getSettings();
        $pageName = $settings->page_id ? optional(FbPage::where('page_id', $settings->page_id)->first())->page_name : '';

        return view('integrations.facebook-lead-ads.settings', compact('settings', 'pageName'));
    }

    /**
     * Save settings (token, graph_version, page_id, webhook_verify_token, etc.)
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'page_access_token' => 'nullable|string',
            'graph_version' => 'nullable|string|max:20',
            'page_id' => 'nullable|string|max:50',
            'page_name' => 'nullable|string|max:255',
            'webhook_verify_token' => 'nullable|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'signature_verification_enabled' => 'boolean',
        ]);

        $settings = FbLeadAdsSettings::getSettings();
        $settings->fill($request->only([
            'page_access_token', 'graph_version', 'page_id', 'webhook_verify_token',
            'app_secret', 'signature_verification_enabled',
        ]));
        $settings->signature_verification_enabled = $request->boolean('signature_verification_enabled');
        $settings->save();

        if ($request->filled('page_id')) {
            FbPage::updateOrCreate(
                ['page_id' => $request->page_id],
                ['page_name' => $request->page_name ?? null]
            );
        }

        return response()->json(['success' => true, 'message' => 'Settings saved.']);
    }

    /**
     * Test connection: call Graph API, return pages list.
     */
    public function testConnection(Request $request)
    {
        $request->validate(['page_access_token' => 'required|string']);
        $settings = FbLeadAdsSettings::getSettings();
        $settings->page_access_token = $request->page_access_token;
        $settings->graph_version = $request->input('graph_version', $settings->graph_version ?? 'v18.0');
        $settings->save();

        $client = FacebookGraphService::fromSettings($settings);
        $result = $client->testConnection();

        return response()->json($result);
    }

    /**
     * Form selector: show forms for the configured page.
     */
    public function forms()
    {
        $settings = FbLeadAdsSettings::getSettings();
        if (empty($settings->page_access_token) || empty($settings->page_id)) {
            return redirect()->route('integrations.facebook-lead-ads.settings')
                ->with('warning', 'Please set token and page first.');
        }

        $client = FacebookGraphService::fromSettings($settings);
        $result = $client->getLeadgenForms($settings->page_id);

        if (!$result['success']) {
            return redirect()->route('integrations.facebook-lead-ads.index')
                ->with('error', $result['error'] ?? 'Failed to fetch forms.');
        }

        $forms = $result['forms'];
        $existingFormIds = FbForm::whereIn('form_id', array_column($forms, 'id'))->pluck('form_id', 'id')->toArray();

        return view('integrations.facebook-lead-ads.forms', compact('forms', 'existingFormIds'));
    }

    /**
     * Mapping UI for a form (by Meta form_id). Create FbForm on first visit if needed.
     */
    public function mapping(Request $request, string $formId)
    {
        $settings = FbLeadAdsSettings::getSettings();
        if (empty($settings->page_id)) {
            return redirect()->route('integrations.facebook-lead-ads.settings');
        }

        $page = FbPage::where('page_id', $settings->page_id)->first();
        if (!$page) {
            $page = FbPage::create([
                'page_id' => $settings->page_id,
                'page_name' => $request->input('page_name'),
            ]);
        }

        $formName = $request->input('form_name', 'Form ' . $formId);
        $fbForm = FbForm::firstOrCreate(
            ['form_id' => $formId],
            ['fb_page_id' => $page->id, 'form_name' => $formName]
        );

        $client = FacebookGraphService::fromSettings($settings);
        $fieldsResult = $client->getFormFieldsSample($formId);
        $fieldNames = $fieldsResult['fields'] ? array_column($fieldsResult['fields'], 'name') : [];
        if (empty($fieldNames)) {
            $fieldNames = ['full_name', 'email', 'phone_number', 'city', 'state', 'zip_code'];
        }
        $suggestedMapping = FacebookLeadMappingService::suggestMapping($fieldNames);
        $crmKeys = FacebookLeadMappingService::getCrmFieldKeys();
        $currentMapping = $fbForm->mapping?->mapping_json ?? $suggestedMapping;

        return view('integrations.facebook-lead-ads.mapping', [
            'fbForm' => $fbForm,
            'fieldNames' => $fieldNames,
            'suggestedMapping' => $suggestedMapping,
            'currentMapping' => $currentMapping,
            'crmKeys' => $crmKeys,
        ]);
    }

    /**
     * Save mapping and enable form.
     */
    public function saveMapping(Request $request)
    {
        $request->validate([
            'fb_form_id' => 'required|exists:fb_forms,id',
            'mapping' => 'required|array',
            'mapping.*' => 'nullable|string|max:50',
        ]);

        $fbForm = FbForm::findOrFail($request->fb_form_id);

        DB::transaction(function () use ($fbForm, $request) {
            $fbForm->mappings()->create([
                'mapping_json' => $request->mapping,
                'created_by' => auth()->id(),
            ]);
            $fbForm->update(['is_enabled' => true]);
        });

        return response()->json(['success' => true, 'message' => 'Mapping saved and form enabled.', 'redirect' => route('integrations.facebook-lead-ads.index')]);
    }
}
