<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoogleSheetsConfig;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Events\LeadCreated;
use App\Services\FieldMappingService;
use App\Services\LeadAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GoogleSheetsLeadController extends Controller
{
    protected $fieldMappingService;
    protected $leadAssignmentService;

    public function __construct(FieldMappingService $fieldMappingService, LeadAssignmentService $leadAssignmentService)
    {
        $this->fieldMappingService = $fieldMappingService;
        $this->leadAssignmentService = $leadAssignmentService;
    }

    /**
     * Store a new lead from Google Apps Script
     */
    public function store(Request $request)
    {
        try {
            // Validate required fields
            $validator = Validator::make($request->all(), [
                'sheet_id' => 'required|string',
                'sheet_row_number' => 'required|integer|min:1',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get sheet config
            $sheetId = GoogleSheetsConfig::extractSheetId($request->sheet_id);
            $config = GoogleSheetsConfig::where('sheet_id', $sheetId)
                ->where('is_active', true)
                ->first();

            if (!$config) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google Sheet configuration not found or inactive',
                ], 404);
            }

            // Map fields using configuration
            $payload = $request->all();
            $mappedData = $this->fieldMappingService->mapFieldsFromPayload($payload, $config);

            // Ensure required fields
            $mappedData['name'] = $mappedData['name'] ?? $request->name;
            $mappedData['phone'] = $mappedData['phone'] ?? $request->phone;
            $mappedData['source'] = $mappedData['source'] ?? 'google_sheets';
            $mappedData['status'] = 'new';

            // Check for duplicate by phone
            $existingLead = Lead::where('phone', $mappedData['phone'])->first();
            if ($existingLead) {
                // Update sheet reference if exists
                $assignment = LeadAssignment::where('lead_id', $existingLead->id)
                    ->where('sheet_config_id', $config->id)
                    ->where('sheet_row_number', $request->sheet_row_number)
                    ->first();

                if (!$assignment) {
                    LeadAssignment::create([
                        'lead_id' => $existingLead->id,
                        'sheet_config_id' => $config->id,
                        'sheet_row_number' => $request->sheet_row_number,
                        'assigned_to' => null,
                        'assigned_by' => $config->created_by,
                        'assignment_type' => 'primary',
                        'assigned_at' => now(),
                        'is_active' => true,
                    ]);
                }

                return response()->json([
                    'status' => 'ok',
                    'message' => 'Lead already exists',
                    'lead_id' => $existingLead->id,
                    'assigned_to' => null,
                ]);
            }

            // Create lead
            $lead = Lead::create([
                'name' => $mappedData['name'],
                'phone' => $mappedData['phone'],
                'email' => $mappedData['email'] ?? null,
                'city' => $mappedData['city'] ?? null,
                'state' => $mappedData['state'] ?? null,
                'property_type' => $mappedData['property_type'] ?? null,
                'budget' => $mappedData['budget'] ?? null,
                'requirements' => $mappedData['requirements'] ?? null,
                'notes' => $mappedData['notes'] ?? null,
                'source' => $mappedData['source'] ?? 'google_sheets',
                'status' => $mappedData['status'] ?? 'new',
                'created_by' => $config->created_by,
            ]);

            // Store sheet reference
            LeadAssignment::create([
                'lead_id' => $lead->id,
                'sheet_config_id' => $config->id,
                'sheet_row_number' => $request->sheet_row_number,
                'assigned_to' => null,
                'assigned_by' => $config->created_by,
                'assignment_type' => 'primary',
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            // Fire LeadCreated event
            event(new LeadCreated($lead));

            // Auto-assign if assignment rule exists
            $assignedUser = null;
            if ($config->assignment_rule_id) {
                try {
                    // Use assignLead method with sheet config ID
                    $assignedUserId = $this->leadAssignmentService->assignLead($lead, $config->id, $config->created_by);
                    if ($assignedUserId) {
                        $assignedUser = \App\Models\User::find($assignedUserId);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to auto-assign lead from Google Sheet", [
                        'lead_id' => $lead->id,
                        'config_id' => $config->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("Lead created from Google Sheet", [
                'lead_id' => $lead->id,
                'sheet_id' => $sheetId,
                'row_number' => $request->sheet_row_number,
                'assigned_to' => $assignedUser?->id,
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Lead created successfully',
                'lead_id' => $lead->id,
                'assigned_to' => $assignedUser ? [
                    'id' => $assignedUser->id,
                    'name' => $assignedUser->name,
                ] : null,
            ]);

        } catch (\Exception $e) {
            Log::error("Error creating lead from Google Sheet", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create lead: ' . $e->getMessage(),
            ], 500);
        }
    }
}
