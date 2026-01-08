<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\LeadAssignment;
use App\Events\LeadAssigned;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function create()
    {
        $users = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->whereIn('slug', ['sales_manager', 'sales_executive', 'telecaller']);
            })
            ->with('role')
            ->get();

        return view('crm.automation.create-lead', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'preferred_location' => 'nullable|string|max:255',
            'preferred_size' => 'nullable|string|max:255',
            'use_end_use' => 'nullable|string',
            'investment' => 'nullable|numeric|min:0',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'source' => 'nullable|in:website,referral,walk_in,call,social_media,other',
            'property_type' => 'nullable|in:apartment,villa,plot,commercial,other',
            'requirements' => 'nullable|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'create_calling_task' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $validated['created_by'] = $request->user()->id;
            $validated['status'] = 'new';

            $lead = Lead::create($validated);

            // Assign lead if user selected
            if ($request->has('assigned_to') && $request->assigned_to) {
                // Checkbox: if present and equals '1', create task. If not present (unchecked), don't create task
                // Note: Checkbox is checked by default in the form, so when checked it sends '1'
                $createCallingTask = $request->has('create_calling_task') && $request->create_calling_task == '1';
                
                $this->assignLead($lead, $request->assigned_to, $request->user()->id, $createCallingTask);
            }

            DB::commit();

            return redirect()
                ->route('crm.automation.index')
                ->with('success', "Lead '{$lead->name}' created successfully" . ($request->assigned_to ? ' and assigned.' : '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to create lead: ' . $e->getMessage()])
                ->withInput();
        }
    }

    private function assignLead(Lead $lead, int $assignedTo, int $assignedBy, bool $createCallingTask = false): void
    {
        // Deactivate existing assignments
        $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);

        // Create new assignment
        LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedBy,
            'assignment_type' => 'primary',
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        // Create calling task if requested (for any role, not just telecallers)
        if ($createCallingTask) {
            try {
                // Check if task already exists
                $existingTask = \App\Models\Task::where('lead_id', $lead->id)
                    ->where('assigned_to', $assignedTo)
                    ->where('type', 'phone_call')
                    ->where('status', 'pending')
                    ->first();

                if (!$existingTask) {
                    $this->taskService->createPhoneCallTask($lead, $assignedTo, $assignedBy);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the lead creation
                \Illuminate\Support\Facades\Log::error("Failed to create calling task for lead {$lead->id}: " . $e->getMessage());
            }
        }

        // Fire event
        event(new LeadAssigned($lead, $assignedTo, $assignedBy));
    }
}

