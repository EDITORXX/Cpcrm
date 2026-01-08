<?php

namespace App\Http\Controllers;

use App\Models\GoogleSheetsConfig;
use App\Models\SheetAssignmentConfig;
use App\Models\SheetPercentageConfig;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SheetAssignmentController extends Controller
{
    /**
     * Index - show all sheet assignments
     */
    public function index(Request $request)
    {
        try {
            $sheets = GoogleSheetsConfig::where('is_active', true)
                ->with(['linkedTelecaller', 'sheetAssignmentConfig.percentageConfigs.user', 'creator'])
                ->latest()
                ->get();

            $telecallerRoleId = Role::where('slug', Role::TELECALLER)->value('id');
            $telecallers = User::where('role_id', $telecallerRoleId)
                ->where('is_active', true)
                ->get();

            // If requesting specific sheet config via AJAX
            if ($request->has('sheet_id') && $request->ajax()) {
                $sheet = GoogleSheetsConfig::where('id', $request->sheet_id)
                    ->where('is_active', true)
                    ->with(['linkedTelecaller', 'sheetAssignmentConfig.percentageConfigs.user'])
                    ->first();
                    
                if ($sheet && $sheet->sheetAssignmentConfig) {
                    return response()->json([
                        'success' => true,
                        'config' => [
                            'linked_telecaller_id' => $sheet->linked_telecaller_id,
                            'per_sheet_daily_limit' => $sheet->per_sheet_daily_limit,
                            'assignment_method' => $sheet->sheetAssignmentConfig->assignment_method,
                            'auto_assign_enabled' => $sheet->sheetAssignmentConfig->auto_assign_enabled,
                            'percentage_configs' => $sheet->sheetAssignmentConfig->percentageConfigs->map(function($pc) {
                                return [
                                    'user_id' => $pc->user_id,
                                    'percentage' => $pc->percentage,
                                    'daily_limit' => $pc->daily_limit,
                                ];
                            })->toArray(),
                        ]
                    ]);
                } else if ($sheet) {
                    // Return sheet data even if no config exists
                    return response()->json([
                        'success' => true,
                        'config' => [
                            'linked_telecaller_id' => $sheet->linked_telecaller_id,
                            'per_sheet_daily_limit' => $sheet->per_sheet_daily_limit,
                            'assignment_method' => null,
                            'auto_assign_enabled' => false,
                            'percentage_configs' => [],
                        ]
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sheet not found',
                        'config' => null
                    ], 404);
                }
            }

            return view('lead-assignment.sheet-assignments', compact('sheets', 'telecallers'));
        } catch (\Exception $e) {
            \Log::error('SheetAssignmentController@index error: ' . $e->getMessage());
            return view('lead-assignment.sheet-assignments', [
                'sheets' => collect([]),
                'telecallers' => collect([]),
                'error' => 'Error loading sheet assignments: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Assign sheet to telecaller
     */
    public function assignSheetToTelecaller(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sheet_id' => 'required|exists:google_sheets_config,id',
            'telecaller_id' => 'nullable|exists:users,id',
            'per_sheet_daily_limit' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sheet = GoogleSheetsConfig::findOrFail($request->sheet_id);

        if ($request->telecaller_id) {
            $telecaller = User::findOrFail($request->telecaller_id);
            if (!$telecaller->isTelecaller()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a telecaller.'
                ], 422);
            }
        }

        $sheet->update([
            'linked_telecaller_id' => $request->telecaller_id,
            'per_sheet_daily_limit' => $request->per_sheet_daily_limit,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sheet assignment updated successfully.',
            'sheet' => $sheet->fresh(['linkedTelecaller']),
        ]);
    }

    /**
     * Update sheet assignment config
     */
    public function updateSheetConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sheet_id' => 'required|exists:google_sheets_config,id',
            'assignment_method' => 'required|in:manual,round_robin,first_available,percentage',
            'auto_assign_enabled' => 'boolean',
            'per_sheet_daily_limit' => 'nullable|integer|min:0',
            'percentage_config' => 'required_if:assignment_method,percentage|array',
            'percentage_config.*.user_id' => 'required|exists:users,id',
            'percentage_config.*.percentage' => 'required|numeric|min:0|max:100',
            'percentage_config.*.daily_limit' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate percentage sum
        if ($request->assignment_method === 'percentage') {
            $totalPercentage = array_sum(array_column($request->percentage_config, 'percentage'));
            if (abs($totalPercentage - 100) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Percentages must sum to 100%.'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $sheet = GoogleSheetsConfig::findOrFail($request->sheet_id);

            // Create or update sheet assignment config
            $config = SheetAssignmentConfig::updateOrCreate(
                ['google_sheets_config_id' => $sheet->id],
                [
                    'assignment_method' => $request->assignment_method,
                    'auto_assign_enabled' => $request->boolean('auto_assign_enabled', false),
                    'per_sheet_daily_limit' => $request->per_sheet_daily_limit,
                ]
            );

            // Update percentage configs if method is percentage
            if ($request->assignment_method === 'percentage' && $request->has('percentage_config')) {
                // Delete existing configs
                SheetPercentageConfig::where('sheet_assignment_config_id', $config->id)->delete();

                // Create new configs
                foreach ($request->percentage_config as $pc) {
                    SheetPercentageConfig::create([
                        'sheet_assignment_config_id' => $config->id,
                        'user_id' => $pc['user_id'],
                        'percentage' => $pc['percentage'],
                        'daily_limit' => $pc['daily_limit'] ?? null,
                        'assigned_count_today' => 0,
                        'last_reset_date' => now()->toDateString(),
                    ]);
                }
            } else {
                // Delete percentage configs if method changed
                SheetPercentageConfig::where('sheet_assignment_config_id', $config->id)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sheet assignment config updated successfully.',
                'config' => $config->fresh(['percentageConfigs.user']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update config: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle auto-assign
     */
    public function toggleAutoAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sheet_id' => 'required|exists:google_sheets_config,id',
            'enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $config = SheetAssignmentConfig::where('google_sheets_config_id', $request->sheet_id)->first();

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Sheet assignment config not found. Please configure assignment method first.'
            ], 404);
        }

        $config->update([
            'auto_assign_enabled' => $request->enabled,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Auto-assign ' . ($request->enabled ? 'enabled' : 'disabled') . ' successfully.',
            'config' => $config,
        ]);
    }
}
