<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
use App\Models\DynamicFormField;
use App\Services\FormDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DynamicFormController extends Controller
{
    protected $formDetectionService;

    public function __construct(FormDetectionService $formDetectionService)
    {
        $this->formDetectionService = $formDetectionService;
    }

    /**
     * Display a listing of forms (existing + custom)
     */
    public function index(Request $request)
    {
        $query = DynamicForm::with(['fields', 'creator', 'replacedForm']);
        
        // Apply filter if provided
        $filter = $request->input('filter', 'all');
        if ($filter === 'drafts') {
            $query->drafts();
        } elseif ($filter === 'published') {
            $query->published();
        }
        
        $customForms = $query->orderBy('created_at', 'desc')->get();

        // Get existing forms in the system
        $existingForms = $this->getExistingForms();

        return view('admin.forms.index', compact('customForms', 'existingForms', 'filter'));
    }

    /**
     * Show form builder
     */
    public function create(Request $request)
    {
        $existingForm = null;
        $detectedFields = [];
        
        // If creating from existing form
        if ($request->has('from_existing')) {
            $formType = $request->input('type', 'custom');
            $locationPath = $request->input('path', '');
            
            $existingForm = [
                'name' => $request->input('name', 'New Form'),
                'location_path' => $locationPath,
                'form_type' => $formType,
                'description' => 'Dynamic version of existing form: ' . $request->input('name', ''),
            ];
            
            // Detect fields from existing form
            $detectedFields = $this->formDetectionService->getFieldDefinitions($formType, $locationPath);
        }
        
        return view('admin.forms.builder', [
            'form' => null, 
            'existingForm' => $existingForm,
            'detectedFields' => $detectedFields
        ]);
    }

    /**
     * Show form builder for editing
     */
    public function edit(DynamicForm $dynamicForm)
    {
        $dynamicForm->load(['fields' => function($query) {
            $query->orderBy('order');
        }]);

        // #region agent log
        \Log::info('DynamicFormController:edit - Loading form for editing', [
            'form_id' => $dynamicForm->id,
            'fields_count' => $dynamicForm->fields->count(),
            'fields' => $dynamicForm->fields->map(function($f) {
                return ['id' => $f->id, 'key' => $f->field_key, 'type' => $f->field_type];
            })->toArray(),
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'H4'
        ]);
        // #endregion

        return view('admin.forms.builder', ['form' => $dynamicForm]);
    }

    /**
     * Store a newly created form
     */
    public function store(Request $request)
    {
        // Log to verify we're in store method
        \Log::info('DynamicFormController:store - Method called', [
            'request_method' => $request->method(),
            'has_method_field' => $request->has('_method'),
            'method_field_value' => $request->input('_method'),
            'form_name' => $request->input('name'),
        ]);
        
        // Decode JSON fields if sent as string
        $fieldsData = $request->input('fields');
        if (is_string($fieldsData)) {
            $fieldsData = json_decode($fieldsData, true);
            $request->merge(['fields' => $fieldsData]);
        }

        // Decode JSON settings if sent as string
        $settingsData = $request->input('settings');
        if (is_string($settingsData)) {
            $settingsData = json_decode($settingsData, true);
            $request->merge(['settings' => $settingsData ?? []]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:dynamic_forms,slug',
            'description' => 'nullable|string',
            'location_path' => 'required|string|max:255',
            'form_type' => 'required|string|max:50',
            'status' => 'nullable|in:draft,published',
            'settings' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.field_key' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|max:50',
            'fields.*.label' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $status = $validated['status'] ?? 'draft';
            $locationPath = $validated['location_path'];
            
            // If publishing, check for existing published form at same location
            $replacesFormId = null;
            if ($status === 'published') {
                $existingForm = DynamicForm::where('location_path', $locationPath)
                    ->where('status', 'published')
                    ->where('is_active', true)
                    ->first();
                
                if ($existingForm) {
                    // Mark old form as replaced
                    $existingForm->update([
                        'status' => 'draft',
                        'is_active' => false,
                    ]);
                    $replacesFormId = $existingForm->id;
                }
            }
            
            $form = DynamicForm::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'location_path' => $locationPath,
                'form_type' => $validated['form_type'],
                'status' => $status,
                'settings' => $validated['settings'] ?? [],
                'replaces_form_id' => $replacesFormId,
                'created_by' => auth()->id(),
            ]);

            // Create form fields
            foreach ($validated['fields'] as $index => $fieldData) {
                DynamicFormField::create([
                    'form_id' => $form->id,
                    'field_key' => $fieldData['field_key'],
                    'field_type' => $fieldData['field_type'],
                    'label' => $fieldData['label'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'options' => is_array($fieldData['options'] ?? null) ? $fieldData['options'] : null,
                    'validation' => is_array($fieldData['validation'] ?? null) ? $fieldData['validation'] : null,
                    'required' => isset($fieldData['required']) && $fieldData['required'],
                    'order' => $index,
                    'section' => $fieldData['section'] ?? 'default',
                    'styles' => is_array($fieldData['styles'] ?? null) ? $fieldData['styles'] : null,
                    'default_value' => $fieldData['default_value'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.forms.index')
                ->with('success', 'Form created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to create form: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update the specified form
     */
    public function update(Request $request, DynamicForm $dynamicForm)
    {
        // Log to verify we're in update method, not store
        \Log::info('DynamicFormController:update - Method called', [
            'form_id' => $dynamicForm->id,
            'form_name' => $dynamicForm->name,
            'request_method' => $request->method(),
            'has_method_field' => $request->has('_method'),
            'method_field_value' => $request->input('_method'),
        ]);
        
        // Decode JSON fields if sent as string
        $fieldsData = $request->input('fields');
        if (is_string($fieldsData)) {
            $fieldsData = json_decode($fieldsData, true);
            $request->merge(['fields' => $fieldsData]);
        }

        // Decode JSON settings if sent as string
        $settingsData = $request->input('settings');
        if (is_string($settingsData)) {
            $settingsData = json_decode($settingsData, true);
            $request->merge(['settings' => $settingsData ?? []]);
        }

        // #region agent log
        \Log::info('DynamicFormController:update - Raw request data', [
            'raw_fields' => $request->input('fields'),
            'fields_data_type' => gettype($request->input('fields')),
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'H3'
        ]);
        // #endregion
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:dynamic_forms,slug,' . $dynamicForm->id,
            'description' => 'nullable|string',
            'location_path' => 'required|string|max:255',
            'form_type' => 'required|string|max:50',
            'status' => 'nullable|in:draft,published',
            'settings' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.field_key' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|max:50',
            'fields.*.label' => 'required|string|max:255',
        ]);
        
        // #region agent log
        \Log::info('DynamicFormController:update - After validation', [
            'validated_fields_count' => count($validated['fields']),
            'validated_fields' => array_map(function($f) {
                return ['key' => $f['field_key'] ?? 'N/A', 'type' => $f['field_type'] ?? 'N/A'];
            }, $validated['fields']),
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'H3'
        ]);
        // #endregion

        DB::beginTransaction();
        try {
            $status = $validated['status'] ?? $dynamicForm->status ?? 'draft';
            $locationPath = $validated['location_path'];
            $isPublishing = ($status === 'published' && $dynamicForm->status !== 'published');
            
            // If changing from draft to published, handle replacement
            $replacesFormId = $dynamicForm->replaces_form_id;
            if ($isPublishing) {
                $existingForm = DynamicForm::where('location_path', $locationPath)
                    ->where('status', 'published')
                    ->where('is_active', true)
                    ->where('id', '!=', $dynamicForm->id)
                    ->first();
                
                if ($existingForm) {
                    // Mark old form as replaced
                    $existingForm->update([
                        'status' => 'draft',
                        'is_active' => false,
                    ]);
                    $replacesFormId = $existingForm->id;
                }
            }
            
            $dynamicForm->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? $dynamicForm->slug,
                'description' => $validated['description'] ?? null,
                'location_path' => $locationPath,
                'form_type' => $validated['form_type'],
                'status' => $status,
                'settings' => $validated['settings'] ?? [],
                'replaces_form_id' => $replacesFormId,
            ]);

            // Delete existing fields
            $dynamicForm->fields()->delete();

            // #region agent log
            \Log::info('DynamicFormController:update - Received fields', [
                'fields_count' => count($validated['fields']),
                'fields' => array_map(function($f) {
                    return ['key' => $f['field_key'] ?? 'N/A', 'type' => $f['field_type'] ?? 'N/A', 'raw_type' => $f['field_type'] ?? null];
                }, $validated['fields']),
                'form_id' => $dynamicForm->id,
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'H3'
            ]);
            // #endregion

            // Create updated form fields
            foreach ($validated['fields'] as $index => $fieldData) {
                // #region agent log
                \Log::info('DynamicFormController:update - Creating field', [
                    'index' => $index,
                    'field_key' => $fieldData['field_key'] ?? 'N/A',
                    'field_type' => $fieldData['field_type'] ?? 'N/A',
                    'field_type_type' => gettype($fieldData['field_type'] ?? null),
                    'label' => $fieldData['label'] ?? 'N/A',
                    'form_id' => $dynamicForm->id,
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'H3'
                ]);
                // #endregion
                
                $fieldTypeToSave = $fieldData['field_type'] ?? 'text';
                
                $createdField = DynamicFormField::create([
                    'form_id' => $dynamicForm->id,
                    'field_key' => $fieldData['field_key'],
                    'field_type' => $fieldTypeToSave, // Explicitly use the field type
                    'label' => $fieldData['label'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'options' => is_array($fieldData['options'] ?? null) ? $fieldData['options'] : null,
                    'validation' => is_array($fieldData['validation'] ?? null) ? $fieldData['validation'] : null,
                    'required' => isset($fieldData['required']) && $fieldData['required'],
                    'order' => $index,
                    'section' => $fieldData['section'] ?? 'default',
                    'styles' => is_array($fieldData['styles'] ?? null) ? $fieldData['styles'] : null,
                    'default_value' => $fieldData['default_value'] ?? null,
                ]);
                
                // #region agent log
                \Log::info('DynamicFormController:update - Field created in DB', [
                    'field_id' => $createdField->id,
                    'saved_field_type' => $createdField->field_type,
                    'saved_field_key' => $createdField->field_key,
                    'fresh_from_db' => $createdField->fresh()->field_type, // Re-fetch from DB to verify
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'H3'
                ]);
                // #endregion
            }
            
            // #region agent log
            // After all fields are created, reload and verify
            $dynamicForm->refresh();
            $dynamicForm->load('fields');
            \Log::info('DynamicFormController:update - After save, reloaded form fields', [
                'form_id' => $dynamicForm->id,
                'fields_count' => $dynamicForm->fields->count(),
                'fields_from_db' => $dynamicForm->fields->map(function($f) {
                    return ['id' => $f->id, 'key' => $f->field_key, 'type' => $f->field_type];
                })->toArray(),
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'H3'
            ]);
            // #endregion

            DB::commit();

            return redirect()
                ->route('admin.forms.index')
                ->with('success', 'Form updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to update form: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified form
     */
    public function destroy(DynamicForm $dynamicForm)
    {
        try {
            $dynamicForm->delete();
            return redirect()
                ->route('admin.forms.index')
                ->with('success', 'Form deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete form: ' . $e->getMessage()]);
        }
    }

    /**
     * Get existing forms in the system
     */
    private function getExistingForms(): array
    {
        $forms = [];
        
        // Helper function to safely get route URL
        $getRouteUrl = function($routeName, $fallbackUrl = '#') {
            try {
                return route($routeName);
            } catch (\Exception $e) {
                return $fallbackUrl;
            }
        };
        
        // Lead Creation Form - Check if route exists, if not skip it
        try {
            route('crm.automation.leads.create');
            $forms[] = [
                'name' => 'Lead Creation Form',
                'location' => 'CRM > Automation > Create Lead',
                'path' => 'crm.automation.create-lead',
                'type' => 'lead',
                'route' => route('crm.automation.leads.create'),
            ];
        } catch (\Exception $e) {
            // Route doesn't exist, skip this form
        }
        
        // Prospect Verification Form
        try {
            $forms[] = [
                'name' => 'Prospect Verification Form',
                'location' => 'Prospects > Verify',
                'path' => 'prospects.verify',
                'type' => 'prospect',
                'route' => $getRouteUrl('prospects.index', '#'),
            ];
        } catch (\Exception $e) {
            // Skip if route doesn't exist
        }
        
        // Meeting Form
        try {
            $forms[] = [
                'name' => 'Meeting Form',
                'location' => 'Senior Manager > Create Meeting',
                'path' => 'meetings.create',
                'type' => 'meeting',
                'route' => $getRouteUrl('sales-manager.meetings.create', '#'),
            ];
        } catch (\Exception $e) {
            // Skip if route doesn't exist
        }
        
        // Site Visit Form
        try {
            $forms[] = [
                'name' => 'Site Visit Form',
                'location' => 'Senior Manager > Create Site Visit',
                'path' => 'site-visits.create',
                'type' => 'site_visit',
                'route' => $getRouteUrl('sales-manager.site-visits.create', '#'),
            ];
        } catch (\Exception $e) {
            // Skip if route doesn't exist
        }
        
        // Task Lead Update Form
        try {
            $forms[] = [
                'name' => 'Task Lead Update Form',
                'location' => 'Senior Manager > Tasks > Update Lead',
                'path' => 'sales-manager.tasks.update-lead',
                'type' => 'task',
                'route' => $getRouteUrl('sales-manager.tasks', '#'),
            ];
        } catch (\Exception $e) {
            // Skip if route doesn't exist
        }
        
        // Prospect Details Form (Senior Manager Tasks)
        try {
            $forms[] = [
                'name' => 'Prospect Details Form',
                'location' => 'Senior Manager > Tasks > Prospect Details',
                'path' => 'sales-manager.tasks.prospect-details',
                'type' => 'prospect',
                'route' => $getRouteUrl('sales-manager.tasks', '#'),
            ];
        } catch (\Exception $e) {
            // Skip if route doesn't exist
        }
        
        // Lead Resource Form (standard leads.create)
        try {
            route('leads.create');
            $forms[] = [
                'name' => 'Lead Form (Standard)',
                'location' => 'Leads > Create',
                'path' => 'leads.create',
                'type' => 'lead',
                'route' => route('leads.create'),
            ];
        } catch (\Exception $e) {
            // Skip if route doesn't exist
        }
        
        return $forms;
    }
}
