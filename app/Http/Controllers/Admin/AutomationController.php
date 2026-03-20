<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FbForm;
use App\Models\Role;
use App\Models\SourceAutomationRule;
use App\Models\SourceAutomationRuleUser;
use App\Models\User;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index()
    {
        $rules = SourceAutomationRule::with(['users.user', 'fbForm', 'singleUser', 'fallbackUser'])
            ->latest()
            ->get();

        return view('admin.automation.index', compact('rules'));
    }

    public function create()
    {
        $fbForms  = FbForm::with('page')->where('is_enabled', true)->get();
        $salesUsers = $this->getSalesUsers();

        return view('admin.automation.form', compact('fbForms', 'salesUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'source'            => 'required|in:facebook_lead_ads,pabbly,mcube,google_sheets,csv,all',
            'fb_form_id'        => 'nullable|exists:fb_forms,id',
            'assignment_method' => 'required|in:round_robin,first_available,percentage,single_user',
            'single_user_id'    => 'nullable|required_if:assignment_method,single_user|exists:users,id',
            'auto_create_task'  => 'boolean',
            'daily_limit'       => 'nullable|integer|min:1',
            'fallback_user_id'  => 'nullable|exists:users,id',
            'is_active'         => 'boolean',
            'users'             => 'nullable|array',
            'users.*.user_id'   => 'required|exists:users,id',
            'users.*.percentage'=> 'nullable|numeric|min:0|max:100',
            'users.*.daily_limit' => 'nullable|integer|min:1',
        ]);

        $rule = SourceAutomationRule::create([
            'name'              => $data['name'],
            'source'            => $data['source'],
            'fb_form_id'        => $data['fb_form_id'] ?? null,
            'assignment_method' => $data['assignment_method'],
            'single_user_id'    => $data['single_user_id'] ?? null,
            'auto_create_task'  => $request->boolean('auto_create_task', true),
            'daily_limit'       => $data['daily_limit'] ?? null,
            'fallback_user_id'  => $data['fallback_user_id'] ?? null,
            'is_active'         => $request->boolean('is_active', true),
            'created_by'        => auth()->id(),
        ]);

        if (!empty($data['users']) && $data['assignment_method'] !== 'single_user') {
            foreach ($data['users'] as $u) {
                SourceAutomationRuleUser::create([
                    'rule_id'    => $rule->id,
                    'user_id'    => $u['user_id'],
                    'percentage' => $u['percentage'] ?? null,
                    'daily_limit'=> $u['daily_limit'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.automation.index')
            ->with('success', 'Automation rule created successfully.');
    }

    public function edit(SourceAutomationRule $rule)
    {
        $rule->load('users.user', 'fbForm', 'singleUser', 'fallbackUser');
        $fbForms    = FbForm::with('page')->where('is_enabled', true)->get();
        $salesUsers = $this->getSalesUsers();

        return view('admin.automation.form', compact('rule', 'fbForms', 'salesUsers'));
    }

    public function update(Request $request, SourceAutomationRule $rule)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'source'            => 'required|in:facebook_lead_ads,pabbly,mcube,google_sheets,csv,all',
            'fb_form_id'        => 'nullable|exists:fb_forms,id',
            'assignment_method' => 'required|in:round_robin,first_available,percentage,single_user',
            'single_user_id'    => 'nullable|required_if:assignment_method,single_user|exists:users,id',
            'auto_create_task'  => 'boolean',
            'daily_limit'       => 'nullable|integer|min:1',
            'fallback_user_id'  => 'nullable|exists:users,id',
            'is_active'         => 'boolean',
            'users'             => 'nullable|array',
            'users.*.user_id'   => 'required|exists:users,id',
            'users.*.percentage'=> 'nullable|numeric|min:0|max:100',
            'users.*.daily_limit' => 'nullable|integer|min:1',
        ]);

        $rule->update([
            'name'              => $data['name'],
            'source'            => $data['source'],
            'fb_form_id'        => $data['fb_form_id'] ?? null,
            'assignment_method' => $data['assignment_method'],
            'single_user_id'    => $data['single_user_id'] ?? null,
            'auto_create_task'  => $request->boolean('auto_create_task', true),
            'daily_limit'       => $data['daily_limit'] ?? null,
            'fallback_user_id'  => $data['fallback_user_id'] ?? null,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        // Sync users
        $rule->users()->delete();
        if (!empty($data['users']) && $data['assignment_method'] !== 'single_user') {
            foreach ($data['users'] as $u) {
                SourceAutomationRuleUser::create([
                    'rule_id'    => $rule->id,
                    'user_id'    => $u['user_id'],
                    'percentage' => $u['percentage'] ?? null,
                    'daily_limit'=> $u['daily_limit'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.automation.index')
            ->with('success', 'Automation rule updated successfully.');
    }

    public function destroy(SourceAutomationRule $rule)
    {
        $rule->users()->delete();
        $rule->delete();

        return redirect()->route('admin.automation.index')
            ->with('success', 'Automation rule deleted.');
    }

    public function toggle(SourceAutomationRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);

        return response()->json(['is_active' => $rule->is_active]);
    }

    protected function getSalesUsers()
    {
        return User::with('role')
            ->where('is_active', true)
            ->whereHas('role', fn($q) => $q->whereIn('slug', [
                Role::SALES_EXECUTIVE,
                Role::SALES_MANAGER,
                Role::ASSISTANT_SALES_MANAGER,
                Role::SENIOR_MANAGER,
            ]))
            ->orderBy('name')
            ->get();
    }
}
