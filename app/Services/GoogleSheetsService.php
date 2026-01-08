<?php

namespace App\Services;

use App\Models\GoogleSheetsConfig;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\ImportBatch;
use App\Models\ImportedLead;
use App\Services\LeadAssignmentService;
use App\Services\DuplicateDetectionService;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class GoogleSheetsService
{
    protected $assignmentService;
    protected $duplicateService;
    protected $client;

    public function __construct(
        LeadAssignmentService $assignmentService,
        DuplicateDetectionService $duplicateService
    ) {
        $this->assignmentService = $assignmentService;
        $this->duplicateService = $duplicateService;
    }

    /**
     * Get access token from service account JSON file
     */
    public function getGoogleAccessTokenFromServiceAccount(string $jsonPath): string
    {
        // Check cache first (tokens are valid for 1 hour)
        $cacheKey = 'google_access_token_' . md5($jsonPath);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Try to find file in storage or config directory
        $fullPath = null;
        if (Storage::exists($jsonPath)) {
            $fullPath = Storage::path($jsonPath);
        } elseif (file_exists(config_path($jsonPath))) {
            $fullPath = config_path($jsonPath);
        } elseif (file_exists($jsonPath)) {
            $fullPath = $jsonPath;
        } else {
            throw new \Exception("Service Account JSON file not found: {$jsonPath}");
        }

        // Read and validate JSON
        $jsonContent = file_get_contents($fullPath);
        $serviceAccount = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid service account JSON format");
        }

        if (!isset($serviceAccount['type']) || $serviceAccount['type'] !== 'service_account') {
            throw new \Exception("Invalid service account JSON: type must be 'service_account'");
        }

        if (!isset($serviceAccount['private_key']) || !isset($serviceAccount['client_email']) || !isset($serviceAccount['token_uri'])) {
            throw new \Exception("Invalid service account JSON: missing required fields");
        }

        // Use Google Client to get access token
        $client = new Client();
        $client->setAuthConfig($fullPath);
        $client->addScope('https://www.googleapis.com/auth/spreadsheets');
        
        $accessToken = $client->fetchAccessTokenWithAssertion();
        
        if (isset($accessToken['error'])) {
            throw new \Exception("Failed to get access token: " . $accessToken['error_description']);
        }

        $token = $accessToken['access_token'];
        
        // Cache token for 55 minutes (tokens are valid for 1 hour)
        Cache::put($cacheKey, $token, now()->addMinutes(55));

        return $token;
    }

    /**
     * Fetch data from Google Sheets
     */
    public function fetchSheetData(
        string $sheetId,
        string $sheetName,
        string $range,
        ?string $apiKey = null,
        ?string $serviceAccountPath = null,
        ?int $startRow = null
    ): array {
        // Build range string
        $rangeString = "{$sheetName}!{$range}";
        if ($startRow !== null && $startRow > 1) {
            // Adjust range to start from specific row
            $rangeParts = explode(':', $range);
            $startCol = $rangeParts[0];
            $endCol = $rangeParts[1] ?? $rangeParts[0];
            $rangeString = "{$sheetName}!{$startCol}{$startRow}:{$endCol}";
        }

        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}/values/{$rangeString}";

        // Try service account first, then API key, then CSV fallback
        $headers = [];
        if ($serviceAccountPath) {
            try {
                $accessToken = $this->getGoogleAccessTokenFromServiceAccount($serviceAccountPath);
                $headers['Authorization'] = "Bearer {$accessToken}";
            } catch (\Exception $e) {
                Log::warning("Service account auth failed: " . $e->getMessage());
            }
        }

        if (empty($headers) && $apiKey) {
            $url .= "?key={$apiKey}";
        }

        // Fetch data using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function($key, $value) {
            return "{$key}: {$value}";
        }, array_keys($headers), $headers));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL error: {$error}");
        }

        if ($httpCode === 400) {
            throw new \Exception("Invalid sheet ID, sheet name, or range");
        }

        if ($httpCode === 403) {
            throw new \Exception("Access denied. Share sheet as 'Anyone with link' or provide valid API key/service account.");
        }

        if ($httpCode === 404) {
            throw new \Exception("Sheet not found. Check sheet ID and sheet name (tab name).");
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? "HTTP {$httpCode} error";
            throw new \Exception($errorMsg);
        }

        $data = json_decode($response, true);

        if (!isset($data['values']) || empty($data['values'])) {
            // Try CSV fallback for public sheets
            return $this->fetchSheetDataCsv($sheetId, $sheetName);
        }

        return $data['values'];
    }

    /**
     * Fetch data using CSV fallback
     */
    protected function fetchSheetDataCsv(string $sheetId, string $sheetName): array
    {
        $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/gviz/tq?tqx=out:csv&sheet=" . urlencode($sheetName);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $csvData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($csvData)) {
            throw new \Exception("Sheet has no data. Check column mapping and ensure data exists.");
        }

        // Parse CSV
        $lines = str_getcsv($csvData, "\n");
        $rows = [];
        foreach ($lines as $line) {
            $rows[] = str_getcsv($line);
        }

        return $rows;
    }

    /**
     * Sync Google Sheets - incremental sync with row tracking
     */
    public function syncGoogleSheets(int $configId): array
    {
        $config = GoogleSheetsConfig::findOrFail($configId);

        if (!$config->is_active) {
            throw new \Exception("Google Sheets config is not active");
        }

        // Extract sheet ID if URL provided
        $sheetId = GoogleSheetsConfig::extractSheetId($config->sheet_id);
        if (!$sheetId) {
            throw new \Exception("Invalid sheet ID format");
        }

        // Determine start row
        $startRow = $config->last_synced_row > 0 ? $config->last_synced_row + 1 : 2; // Skip header if first sync

        // Fetch data
        try {
            $rows = $this->fetchSheetData(
                $sheetId,
                $config->sheet_name,
                $config->range,
                $config->api_key,
                $config->service_account_json_path,
                $startRow
            );
        } catch (\Exception $e) {
            Log::error("Google Sheets fetch error for config {$configId}: " . $e->getMessage());
            throw $e;
        }

        if (empty($rows)) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [],
                'last_synced_row' => $config->last_synced_row,
            ];
        }

        // Convert column letters to indices
        $nameIndex = GoogleSheetsConfig::columnLetterToIndex($config->name_column);
        $phoneIndex = GoogleSheetsConfig::columnLetterToIndex($config->phone_column);
        $notesIndex = $config->notes_column ? GoogleSheetsConfig::columnLetterToIndex($config->notes_column) : null;

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $currentRow = $startRow;
        $lastSuccessfulRow = $config->last_synced_row;

        // Create import batch
        $batch = ImportBatch::create([
            'user_id' => $config->created_by,
            'source_type' => 'google_sheets',
            'google_sheet_id' => $sheetId,
            'google_sheet_name' => $config->sheet_name,
            'total_leads' => count($rows),
            'status' => 'processing',
            'assignment_rule_id' => $config->assignment_rule_id,
        ]);

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $currentRow = $startRow + $rowIndex;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Extract data
                    $name = trim($row[$nameIndex] ?? '');
                    $phone = trim($row[$phoneIndex] ?? '');
                    $notes = $notesIndex !== null ? trim($row[$notesIndex] ?? '') : null;

                    // Debug: Log raw data
                    Log::info("Row {$currentRow} - Raw data", [
                        'name_index' => $nameIndex,
                        'phone_index' => $phoneIndex,
                        'notes_index' => $notesIndex,
                        'row_data' => $row,
                        'extracted_name' => $name,
                        'extracted_phone' => $phone,
                        'extracted_notes' => $notes,
                    ]);

                    // Validate required fields
                    if (empty($name) || empty($phone)) {
                        $skipped++;
                        $errorMsg = "Row {$currentRow}: Missing name or phone (Name: '{$name}', Phone: '{$phone}')";
                        $errors[] = $errorMsg;
                        Log::warning($errorMsg, ['row' => $currentRow, 'row_data' => $row]);
                        continue;
                    }

                    // Sanitize and validate phone
                    $originalPhone = $phone;
                    $phone = $this->duplicateService->sanitizePhone($phone);
                    if (!$this->duplicateService->isValidPhone($phone)) {
                        $skipped++;
                        $errorMsg = "Row {$currentRow}: Invalid phone number (Original: '{$originalPhone}', Sanitized: '{$phone}')";
                        $errors[] = $errorMsg;
                        Log::warning($errorMsg, ['row' => $currentRow, 'original_phone' => $originalPhone, 'sanitized_phone' => $phone]);
                        continue;
                    }

                    // Check if blacklisted
                    if ($this->duplicateService->isBlacklisted($phone)) {
                        $skipped++;
                        $errorMsg = "Row {$currentRow}: Phone number is blacklisted ({$phone})";
                        $errors[] = $errorMsg;
                        Log::info("Row {$currentRow}: Blacklisted number skipped", ['phone' => $phone]);
                        continue;
                    }

                    // Check for duplicates
                    if ($this->duplicateService->isDuplicate($phone)) {
                        $skipped++;
                        $errorMsg = "Row {$currentRow}: Duplicate phone number ({$phone})";
                        $errors[] = $errorMsg;
                        Log::info($errorMsg, ['row' => $currentRow, 'phone' => $phone]);
                        continue;
                    }

                    // Create lead
                    $lead = Lead::create([
                        'name' => $name,
                        'phone' => $phone,
                        'notes' => $notes,
                        'source' => 'google_sheets',
                        'status' => 'new',
                        'created_by' => $config->created_by,
                    ]);

                    // Assign lead using new assignment system
                    $assignedTo = null;
                    try {
                        // Use new LeadAssignmentService with sheet config
                        $newAssignmentService = app(\App\Services\LeadAssignmentService::class);
                        $assignedTo = $newAssignmentService->assignLead($lead, $config->id, $config->created_by);
                        
                        // Update assignment with sheet tracking info if assigned
                        if ($assignedTo) {
                            LeadAssignment::where('lead_id', $lead->id)
                                ->where('assigned_to', $assignedTo)
                                ->where('is_active', true)
                                ->update([
                                    'sheet_config_id' => $config->id,
                                    'sheet_row_number' => $currentRow,
                                ]);
                        }
                    } catch (\Exception $e) {
                        Log::error("Assignment error for lead {$lead->id}: " . $e->getMessage());
                    }

                    // Track imported lead
                    ImportedLead::create([
                        'import_batch_id' => $batch->id,
                        'lead_id' => $lead->id,
                        'assigned_to' => $assignedTo,
                        'assigned_at' => $assignedTo ? now() : null,
                        'import_data' => [
                            'name' => $name,
                            'phone' => $phone,
                            'notes' => $notes,
                            'row' => $currentRow,
                        ],
                    ]);

                    $imported++;
                    $lastSuccessfulRow = $currentRow;

                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row {$currentRow}: " . $e->getMessage();
                    Log::error("Lead import error for row {$currentRow}: " . $e->getMessage());
                }
            }

            // Update config with last synced row
            $config->update([
                'last_sync_at' => now(),
                'last_synced_row' => $lastSuccessfulRow,
            ]);

            // Update batch
            $batch->update([
                'imported_leads' => $imported,
                'failed_leads' => $skipped,
                'status' => 'completed',
                'error_log' => array_slice($errors, 0, 20), // Limit to first 20 errors
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $batch->update([
                'status' => 'failed',
                'error_log' => array_merge($errors, [$e->getMessage()]),
            ]);
            throw $e;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 20),
            'last_synced_row' => $lastSuccessfulRow,
        ];
    }

    /**
     * Update Google Sheet status and notes (two-way sync)
     */
    public function updateGoogleSheetStatus(
        int $sheetConfigId,
        int $rowNumber,
        string $status,
        ?string $notes = null,
        ?string $username = null
    ): bool {
        try {
            $config = GoogleSheetsConfig::findOrFail($sheetConfigId);
            
            // Extract sheet ID
            $sheetId = GoogleSheetsConfig::extractSheetId($config->sheet_id);
            if (!$sheetId) {
                Log::error("Invalid sheet ID for config {$sheetConfigId}");
                return false;
            }

            // Map status - handle both custom and standard statuses
            $statusMap = [
                'called_interested' => 'Interested',
                'called_not_interested' => 'Not Interested',
                'completed' => 'Completed',
                'cnp' => 'CNP',
                'broker' => 'Broker',
                'closed_won' => 'Interested', // Map closed_won to Interested
                'closed_lost' => 'Not Interested', // Map closed_lost to Not Interested
            ];
            $mappedStatus = $statusMap[$status] ?? $status;

            // Format notes
            $formattedNotes = $notes;
            if ($username && $notes) {
                $formattedNotes = "Remark by {$username}: {$notes}";
            }

            // Get access token
            $accessToken = null;
            if ($config->service_account_json_path) {
                $accessToken = $this->getGoogleAccessTokenFromServiceAccount($config->service_account_json_path);
            }

            if (!$accessToken && !$config->api_key) {
                Log::warning("No authentication method available for config {$sheetConfigId}");
                return false;
            }

            // Build update request
            $statusCol = $config->status_column . $rowNumber;
            $notesCol = $config->notes_column_sync . $rowNumber;

            $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}/values:batchUpdate";
            
            $body = [
                'valueInputOption' => 'USER_ENTERED',
                'data' => [
                    [
                        'range' => "{$config->sheet_name}!{$statusCol}",
                        'values' => [[$mappedStatus]],
                    ],
                ],
            ];

            if ($formattedNotes) {
                $body['data'][] = [
                    'range' => "{$config->sheet_name}!{$notesCol}",
                    'values' => [[$formattedNotes]],
                ];
            }

            $headers = [
                'Content-Type: application/json',
            ];

            if ($accessToken) {
                $headers[] = "Authorization: Bearer {$accessToken}";
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . ($config->api_key ? "?key={$config->api_key}" : ''));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                Log::error("Google Sheets update error: " . ($errorData['error']['message'] ?? "HTTP {$httpCode}"));
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error updating Google Sheet status: " . $e->getMessage());
            return false;
        }
    }
}

