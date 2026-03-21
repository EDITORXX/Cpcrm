@extends('layouts.app')

@section('title', ($user ? 'Edit User' : 'Create User') . ' - Base CRM')
@section('page-title', $user ? 'Edit User' : 'Create User')

@push('styles')
<style>
.uf-wrap { max-width: 640px; margin: 0 auto; }
.uf-card { background:#fff; border-radius:16px; border:1px solid #e5e7eb; padding:32px; box-shadow:0 2px 8px rgba(0,0,0,.06); }
.uf-section-label {
    font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase;
    letter-spacing:.6px; margin-bottom:16px; padding-bottom:10px;
    border-bottom:1.5px solid #f3f4f6; display:flex; align-items:center; gap:8px;
}
.uf-field { margin-bottom:20px; }
.uf-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:7px; display:block; }
.uf-label span.req { color:#ef4444; margin-left:2px; }
.uf-label span.opt { color:#9ca3af; font-weight:400; font-size:12px; margin-left:4px; }
.uf-input {
    width:100%; padding:10px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
    font-size:13.5px; color:#111827; outline:none; transition:.2s; background:#f9fafb;
    box-sizing:border-box;
}
.uf-input:focus { border-color:#205A44; background:#fff; box-shadow:0 0 0 3px rgba(32,90,68,.08); }
.uf-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 14px center; padding-right:36px; cursor:pointer; }

/* Role option badges */
.role-option-group { pointer-events:none; font-size:11px; font-weight:700; color:#9ca3af; letter-spacing:.5px; background:#f9fafb; padding:8px 14px 4px; }

/* Manager field slide animation */
#manager-field { transition: all .3s; overflow:hidden; }
#manager-field.hidden-field { max-height:0; margin:0; opacity:0; pointer-events:none; }
#manager-field.visible-field { max-height:200px; opacity:1; }

.uf-manager-hint {
    display:flex; align-items:center; gap:7px; background:#f0fdf4;
    border:1px solid #bbf7d0; border-radius:8px; padding:9px 12px;
    font-size:12px; color:#065f46; margin-top:8px;
}

.role-badge {
    display:inline-flex; align-items:center; gap:5px; padding:3px 10px;
    border-radius:20px; font-size:11px; font-weight:600;
}

/* Password toggle */
.uf-pw-wrap { position:relative; }
.uf-pw-toggle { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#9ca3af; padding:2px; }
.uf-pw-toggle:hover { color:#374151; }

/* Status toggle */
.uf-toggle-row { display:flex; align-items:center; justify-content:space-between; background:#f9fafb; border:1.5px solid #e5e7eb; border-radius:10px; padding:12px 16px; }
.uf-toggle-label { font-size:13.5px; font-weight:600; color:#374151; }
.uf-toggle-sub { font-size:12px; color:#6b7280; margin-top:2px; }
.toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; cursor:pointer; transition:.3s; }
.toggle-slider:before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.toggle-switch input:checked + .toggle-slider { background:#205A44; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(20px); }

.uf-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:28px; padding-top:20px; border-top:1.5px solid #f3f4f6; }
.uf-btn-cancel { padding:10px 22px; border:1.5px solid #e5e7eb; border-radius:10px; color:#374151; font-size:13.5px; font-weight:600; background:#fff; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:.2s; }
.uf-btn-cancel:hover { background:#f3f4f6; }
.uf-btn-save { padding:10px 28px; background:linear-gradient(135deg,#063A1C,#205A44); color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:.2s; }
.uf-btn-save:hover { opacity:.9; transform:translateY(-1px); }
</style>
@endpush

@php
/* Hierarchy order for role dropdown */
$roleOrder = [
    'admin', 'crm', 'hr_manager', 'finance_manager',
    'sales_manager', 'senior_manager', 'assistant_sales_manager', 'sales_executive'
];

$roleGroups = [
    'Management'   => ['admin', 'crm'],
    'Support'      => ['hr_manager', 'finance_manager'],
    'Sales Team'   => ['sales_manager', 'senior_manager', 'assistant_sales_manager', 'sales_executive'],
];

$roleIcons = [
    'admin'                   => '👑',
    'crm'                     => '🖥️',
    'hr_manager'              => '👥',
    'finance_manager'         => '💰',
    'sales_manager'           => '🎯',
    'senior_manager'          => '📊',
    'assistant_sales_manager' => '🤝',
    'sales_executive'         => '📞',
];

/* Sorted roles collection */
$rolesBySlugs = $roles->keyBy('slug');
$sortedRoles = collect($roleOrder)->filter(fn($s) => $rolesBySlugs->has($s))->map(fn($s) => $rolesBySlugs[$s]);

/* Roles that DON'T need a manager */
$noManagerRoles = ['admin', 'crm', 'hr_manager', 'finance_manager', 'sales_manager'];

/* Manager filter rules per role */
$managerRoleMap = [
    'senior_manager'          => ['sales_manager'],
    'assistant_sales_manager' => ['senior_manager'],
    'sales_executive'         => ['assistant_sales_manager'],
];

/* Current selected role slug (for edit) */
$currentRoleSlug = $user ? ($user->role->slug ?? '') : old('role_id_slug', '');
@endphp

@section('content')
<div class="uf-wrap">

    @if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:13.5px;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="uf-card">
        <form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if($user) @method('PUT') @endif

            {{-- ── Basic Info ─────────────────────────────── --}}
            <div class="uf-section-label">
                <i class="fas fa-user" style="color:#205A44;"></i> Basic Information
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="uf-field">
                    <label class="uf-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="name" class="uf-input"
                           value="{{ old('name', $user?->name) }}" required
                           placeholder="Enter full name">
                </div>
                <div class="uf-field">
                    <label class="uf-label">Phone <span class="opt">(optional)</span></label>
                    <input type="text" name="phone" class="uf-input"
                           value="{{ old('phone', $user?->phone) }}"
                           placeholder="+91 XXXXX XXXXX">
                </div>
            </div>

            <div class="uf-field">
                <label class="uf-label">Email Address <span class="req">*</span></label>
                <input type="email" name="email" class="uf-input"
                       value="{{ old('email', $user?->email) }}" required
                       placeholder="user@example.com">
            </div>

            <div class="uf-field">
                <label class="uf-label">
                    Password
                    @if(!$user)<span class="req">*</span>@else<span class="opt">(leave blank to keep current)</span>@endif
                </label>
                <div class="uf-pw-wrap">
                    <input type="password" name="password" id="passwordInput" class="uf-input"
                           @if(!$user) required @endif minlength="8"
                           placeholder="{{ $user ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}">
                    <button type="button" class="uf-pw-toggle" onclick="togglePw()">
                        <i class="fas fa-eye" id="pwEyeIcon"></i>
                    </button>
                </div>
            </div>

            {{-- ── Role & Hierarchy ────────────────────────── --}}
            <div class="uf-section-label" style="margin-top:8px;">
                <i class="fas fa-sitemap" style="color:#205A44;"></i> Role & Hierarchy
            </div>

            <div class="uf-field">
                <label class="uf-label">Role <span class="req">*</span></label>
                <select name="role_id" id="roleSelect" class="uf-input uf-select" required
                        onchange="handleRoleChange(this)">
                    <option value="">— Select Role —</option>
                    @foreach($roleGroups as $groupName => $groupSlugs)
                        @php $groupRoles = $sortedRoles->filter(fn($r) => in_array($r->slug, $groupSlugs)); @endphp
                        @if($groupRoles->count())
                        <optgroup label="{{ $groupName }}">
                            @foreach($groupRoles as $role)
                            <option value="{{ $role->id }}"
                                    data-slug="{{ $role->slug }}"
                                    {{ old('role_id', $user?->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $roleIcons[$role->slug] ?? '•' }}  {{ $role->name }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endif
                    @endforeach
                </select>
                <div id="roleHint" style="margin-top:7px;font-size:12px;color:#6b7280;display:none;"></div>
            </div>

            {{-- Manager field — shown/hidden by JS --}}
            <div id="manager-field" class="{{ in_array($currentRoleSlug, $noManagerRoles) ? 'hidden-field' : 'visible-field' }} uf-field">
                <label class="uf-label" id="managerLabel">Manager <span class="opt">(optional)</span></label>
                <select name="manager_id" id="managerSelect" class="uf-input uf-select">
                    <option value="">— No Manager —</option>
                    @foreach($managers as $manager)
                    <option value="{{ $manager->id }}"
                            data-role-slug="{{ $manager->role->slug }}"
                            {{ old('manager_id', $user?->manager_id) == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }}
                        <span style="color:#9ca3af;">· {{ $manager->role->name }}</span>
                    </option>
                    @endforeach
                </select>
                <div id="managerHint" class="uf-manager-hint" style="display:none;">
                    <i class="fas fa-info-circle"></i>
                    <span id="managerHintText"></span>
                </div>
            </div>

            {{-- ── Status ──────────────────────────────────── --}}
            <div class="uf-section-label" style="margin-top:8px;">
                <i class="fas fa-toggle-on" style="color:#205A44;"></i> Account Status
            </div>

            <div class="uf-toggle-row">
                <div>
                    <div class="uf-toggle-label">Active User</div>
                    <div class="uf-toggle-sub">Inactive users cannot log in to the CRM</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $user ? $user->is_active : true) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            {{-- ── Actions ─────────────────────────────────── --}}
            <div class="uf-actions">
                <a href="{{ route('users.index') }}" class="uf-btn-cancel">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="uf-btn-save">
                    <i class="fas fa-{{ $user ? 'save' : 'user-plus' }}"></i>
                    {{ $user ? 'Update User' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* Role rules */
const noManagerSlugs  = @json($noManagerRoles);
const managerRoleMap  = @json($managerRoleMap);

const roleHints = {
    'admin':                   'Full system access — no manager needed.',
    'crm':                     'Operations manager — manages all leads and users.',
    'hr_manager':              'HR access — manages HR-related tasks.',
    'finance_manager':         'Finance access — approves incentive requests.',
    'sales_manager':           'Top-level sales role (Sales Head) — no manager required.',
    'senior_manager':          'Reports to Sales Manager.',
    'assistant_sales_manager': 'Reports to Senior Manager.',
    'sales_executive':         'Reports to Assistant Sales Manager.',
};

const managerHints = {
    'senior_manager':          'Select the Sales Manager this person reports to.',
    'assistant_sales_manager': 'Select the Senior Manager this person reports to.',
    'sales_executive':         'Select the Assistant Sales Manager this person reports to.',
};

function handleRoleChange(selectEl) {
    const option   = selectEl.options[selectEl.selectedIndex];
    const slug     = option ? option.dataset.slug : '';
    const mField   = document.getElementById('manager-field');
    const roleHint = document.getElementById('roleHint');
    const mHint    = document.getElementById('managerHint');
    const mHintTxt = document.getElementById('managerHintText');
    const mSelect  = document.getElementById('managerSelect');
    const mLabel   = document.getElementById('managerLabel');

    /* Role hint */
    if (slug && roleHints[slug]) {
        roleHint.textContent = '↳ ' + roleHints[slug];
        roleHint.style.display = 'block';
    } else {
        roleHint.style.display = 'none';
    }

    if (!slug || noManagerSlugs.includes(slug)) {
        /* Hide manager */
        mField.classList.remove('visible-field');
        mField.classList.add('hidden-field');
        mSelect.value = '';
        mHint.style.display = 'none';
        return;
    }

    /* Show manager */
    mField.classList.remove('hidden-field');
    mField.classList.add('visible-field');

    /* Filter manager options */
    const allowedRoles = managerRoleMap[slug] || null;
    let anyVisible = false;
    Array.from(mSelect.options).forEach(opt => {
        if (!opt.value) return; // keep "No Manager" always
        const optSlug = opt.dataset.roleSlug;
        if (!allowedRoles || allowedRoles.includes(optSlug)) {
            opt.style.display = '';
            anyVisible = true;
        } else {
            opt.style.display = 'none';
            if (opt.selected) { opt.selected = false; mSelect.value = ''; }
        }
    });

    /* Manager label */
    mLabel.innerHTML = 'Manager <span style="color:#ef4444;margin-left:2px;">*</span>';

    /* Manager hint */
    if (managerHints[slug]) {
        mHintTxt.textContent = managerHints[slug];
        mHint.style.display = 'flex';
    } else {
        mHint.style.display = 'none';
    }
}

function togglePw() {
    const inp  = document.getElementById('passwordInput');
    const icon = document.getElementById('pwEyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

/* Init on page load (edit mode) */
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('roleSelect');
    if (sel.value) handleRoleChange(sel);
});
</script>
@endsection
