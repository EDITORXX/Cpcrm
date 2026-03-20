@extends('layouts.app')
@section('title', isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule')

@push('styles')
<style>
/* ─── STEP INDICATOR ─────────────────────────────── */
.step-bar { display:flex; align-items:center; margin-bottom:28px; }
.step-item { display:flex; align-items:center; gap:8px; }
.step-dot {
    width:32px; height:32px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:13px; flex-shrink:0;
    background:#198754; color:#fff; border:2px solid #198754;
}
.step-name { font-size:13px; font-weight:600; color:#212529; white-space:nowrap; }
.step-line { flex:1; height:2px; background:#dee2e6; margin:0 14px; }

/* ─── SECTION BOX ────────────────────────────────── */
.auto-box {
    background:#fff;
    border:1px solid #e3e6ea;
    border-radius:16px;
    margin-bottom:18px;
    overflow:hidden;
}
.auto-box-head {
    display:flex; align-items:center; gap:12px;
    padding:14px 20px;
    background:#f8f9fa;
    border-bottom:1px solid #e9ecef;
}
.auto-box-num {
    width:28px; height:28px; border-radius:8px;
    background:#212529; color:#fff;
    font-size:12px; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.auto-box-title { font-size:15px; font-weight:700; color:#212529; margin:0; line-height:1.2; }
.auto-box-sub   { font-size:12px; color:#6c757d; margin:0; }
.auto-box-body  { padding:22px 24px; }

/* ─── SOURCE CARDS ───────────────────────────────── */
.src-grid {
    display:grid;
    grid-template-columns:repeat(6,1fr);
    gap:10px;
}
@media(max-width:1100px){ .src-grid{ grid-template-columns:repeat(3,1fr); } }
@media(max-width:600px) { .src-grid{ grid-template-columns:repeat(2,1fr); } }

.src-item { position:relative; cursor:pointer; }
.src-item input { position:absolute; opacity:0; width:0; height:0; }
.src-card {
    border:2px solid #e9ecef;
    border-radius:12px;
    padding:16px 10px 12px;
    text-align:center;
    background:#fff;
    transition:all .2s;
    cursor:pointer;
    user-select:none;
}
.src-card:hover { border-color:#adb5bd; transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.07); }
.src-item input:checked + .src-card { transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,0,0,.12); }
.src-icon {
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 8px; font-size:1.3rem;
}
.src-label { font-size:12px; font-weight:600; color:#212529; margin:0; }
.src-tick {
    position:absolute; top:7px; right:7px;
    width:18px; height:18px; border-radius:50%;
    display:none; align-items:center; justify-content:center;
    font-size:9px; color:#fff;
}
.src-item input:checked ~ .src-tick { display:flex; }

/* ─── FB PANEL ───────────────────────────────────── */
.fb-panel {
    background:#eff6ff; border:1px solid #bfdbfe;
    border-radius:10px; padding:14px 16px; margin-top:16px;
}

/* ─── METHOD CARDS ───────────────────────────────── */
.method-grid {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    margin-bottom:20px;
}
@media(max-width:768px){ .method-grid{ grid-template-columns:repeat(2,1fr); } }

.method-item { position:relative; cursor:pointer; }
.method-item input { position:absolute; opacity:0; width:0; height:0; }
.method-card {
    border:2px solid #e9ecef;
    border-radius:10px;
    padding:14px;
    background:#fff;
    transition:all .2s;
    cursor:pointer;
    height:100%;
}
.method-card:hover { border-color:#adb5bd; background:#f8f9fa; }
.method-item input:checked + .method-card { border-color:#198754; background:#f0fdf4; }
.method-ico {
    width:36px; height:36px; border-radius:9px;
    background:#f1f3f5; display:flex; align-items:center; justify-content:center;
    font-size:14px; color:#495057; margin-bottom:9px;
    transition:all .2s;
}
.method-item input:checked + .method-card .method-ico { background:#198754; color:#fff; }
.method-ttl { font-size:13px; font-weight:700; color:#212529; margin:0 0 3px; }
.method-dsc { font-size:11px; color:#6c757d; margin:0; line-height:1.4; }

/* ─── USERS TABLE ────────────────────────────────── */
.usr-table { border-collapse:separate; border-spacing:0; width:100%; }
.usr-table thead th {
    background:#f8f9fa; font-size:11px; font-weight:700;
    text-transform:uppercase; letter-spacing:.6px; color:#6c757d;
    border-bottom:2px solid #e9ecef; padding:10px 14px;
}
.usr-table tbody td { padding:9px 14px; vertical-align:middle; border-bottom:1px solid #f1f3f5; }
.usr-table tbody tr:last-child td { border-bottom:none; }
.usr-table tbody tr:hover td { background:#fafafa; }

/* ─── SETTINGS FORM ──────────────────────────────── */
.settings-grid { display:grid; grid-template-columns:1fr 180px 1fr; gap:16px; margin-bottom:20px; }
@media(max-width:900px){ .settings-grid{ grid-template-columns:1fr 1fr; } }
@media(max-width:600px){ .settings-grid{ grid-template-columns:1fr; } }

.field-label {
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:#6c757d; margin-bottom:5px;
}
.field-hint { font-size:11px; color:#9ca3af; margin-top:4px; }

/* ─── TOGGLE ROW ─────────────────────────────────── */
.tog-row {
    display:flex; align-items:flex-start; gap:14px;
    padding:14px 16px; border-radius:10px;
    border:1px solid #e9ecef; background:#fff; cursor:pointer;
}
.tog-row + .tog-row { margin-top:10px; }
.tog-row:hover { background:#fafafa; }
.tog-ttl { font-size:14px; font-weight:600; color:#212529; margin:0 0 2px; cursor:pointer; }
.tog-dsc { font-size:12px; color:#6c757d; margin:0; }
.form-check-input[type=checkbox] { cursor:pointer; }

/* ─── SUBMIT BAR ─────────────────────────────────── */
.submit-bar {
    display:flex; gap:10px; align-items:center;
    padding:18px 0 4px;
    border-top:1px solid #e9ecef;
    margin-top:20px;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4 px-4">

    {{-- Page header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.automation.index') }}"
           class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center"
           style="width:36px;height:36px;">
            <i class="fas fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-bold">{{ isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule' }}</h5>
            <div class="text-muted small">Lead source configure karo → users assign karo → task settings</div>
        </div>
    </div>

    {{-- Step bar --}}
    <div class="step-bar">
        <div class="step-item"><div class="step-dot">1</div><span class="step-name">Lead Source</span></div>
        <div class="step-line"></div>
        <div class="step-item"><div class="step-dot">2</div><span class="step-name">Distribution</span></div>
        <div class="step-line"></div>
        <div class="step-item"><div class="step-dot">3</div><span class="step-name">Task & Settings</span></div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 rounded-3 mb-4 d-flex gap-3 align-items-start">
        <i class="fas fa-exclamation-circle mt-1 text-danger"></i>
        <div>
            <strong class="d-block mb-1">Please fix these errors:</strong>
            <ul class="mb-0 ps-3 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    </div>
    @endif

    <form method="POST"
          action="{{ isset($rule) ? route('admin.automation.update', $rule) : route('admin.automation.store') }}">
        @csrf @if(isset($rule)) @method('PUT') @endif

        @php
            $selectedSource = old('source', $rule->source ?? '');
            $selectedMethod = old('assignment_method', $rule->assignment_method ?? 'round_robin');
            $existingUsers  = old('users', isset($rule) ? $rule->users->map(fn($ru) => [
                'user_id'     => $ru->user_id,
                'percentage'  => $ru->percentage,
                'daily_limit' => $ru->daily_limit,
            ])->toArray() : [['user_id'=>'','percentage'=>'','daily_limit'=>'']]);
            $sources = [
                'facebook_lead_ads'=>['label'=>'Facebook Lead Ads','icon'=>'fab fa-facebook','color'=>'#1877f2','bg'=>'#e7f0fd'],
                'pabbly'           =>['label'=>'Pabbly',           'icon'=>'fas fa-bolt',     'color'=>'#ff6600','bg'=>'#fff0e6'],
                'mcube'            =>['label'=>'MCube',            'icon'=>'fas fa-phone',    'color'=>'#6f42c1','bg'=>'#f0ebfd'],
                'google_sheets'    =>['label'=>'Google Sheets',    'icon'=>'fas fa-table',    'color'=>'#0f9d58','bg'=>'#e6f7ee'],
                'csv'              =>['label'=>'CSV Import',       'icon'=>'fas fa-file-csv', 'color'=>'#0077b6','bg'=>'#e6f2fb'],
                'all'              =>['label'=>'All Sources',      'icon'=>'fas fa-globe',    'color'=>'#28a745','bg'=>'#eafaf1'],
            ];
            $methods = [
                'round_robin'    =>['title'=>'Round Robin',    'desc'=>'Baari baari sabko A→B→C→A',    'icon'=>'fas fa-sync-alt'],
                'first_available'=>['title'=>'First Available','desc'=>'Jiske paas sabse kam leads ho', 'icon'=>'fas fa-user-check'],
                'percentage'     =>['title'=>'Percentage',     'desc'=>'A ko 40%, B ko 30%, C ko 30%', 'icon'=>'fas fa-percent'],
                'single_user'    =>['title'=>'Single User',    'desc'=>'Sab leads ek hi user ko',      'icon'=>'fas fa-user'],
            ];
        @endphp

        {{-- ══════════════════════════════════════════
             STEP 1 — LEAD SOURCE
        ══════════════════════════════════════════ --}}
        <div class="auto-box">
            <div class="auto-box-head">
                <div class="auto-box-num">1</div>
                <div>
                    <p class="auto-box-title">Lead Source</p>
                    <p class="auto-box-sub">Kahan se aaye lead ko is rule se handle karna hai?</p>
                </div>
            </div>
            <div class="auto-box-body">
                <div class="src-grid">
                    @foreach($sources as $val => $s)
                    <label class="src-item">
                        <input type="radio" name="source" value="{{ $val }}" class="src-radio"
                               {{ $selectedSource === $val ? 'checked' : '' }}>
                        <div class="src-card"
                             style="{{ $selectedSource===$val ? "border-color:{$s['color']};background:{$s['bg']};" : '' }}">
                            <div class="src-icon" style="background:{{ $s['bg'] }};">
                                <i class="{{ $s['icon'] }}" style="color:{{ $s['color'] }};"></i>
                            </div>
                            <p class="src-label">{{ $s['label'] }}</p>
                        </div>
                        <div class="src-tick" style="background:{{ $s['color'] }};">
                            <i class="fas fa-check"></i>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Facebook form panel --}}
                <div id="fbPanel" class="fb-panel {{ $selectedSource !== 'facebook_lead_ads' ? 'd-none' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fab fa-facebook" style="color:#1877f2;font-size:15px;"></i>
                        <span style="font-size:13px;font-weight:600;">Facebook Form <span class="text-muted fw-normal">(optional)</span></span>
                    </div>
                    <select name="fb_form_id" class="form-select form-select-sm">
                        <option value="">-- Kisi bhi form se aaye lead (any form) --</option>
                        @foreach($fbForms as $form)
                        <option value="{{ $form->id }}"
                            {{ old('fb_form_id', $rule->fb_form_id ?? '') == $form->id ? 'selected' : '' }}>
                            {{ $form->page->page_name ?? 'Page' }} — {{ $form->form_name ?? $form->form_id }}
                        </option>
                        @endforeach
                    </select>
                    <div class="mt-2" style="font-size:11px;color:#3b82f6;">
                        <i class="fas fa-info-circle me-1"></i>
                        Specific form select karo to sirf us form ke leads yahan aayenge. Baaki forms ke liye alag rule banao.
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             STEP 2 — DISTRIBUTION
        ══════════════════════════════════════════ --}}
        <div class="auto-box">
            <div class="auto-box-head">
                <div class="auto-box-num">2</div>
                <div>
                    <p class="auto-box-title">Distribution Logic</p>
                    <p class="auto-box-sub">Lead kaise aur kisko assign karna hai?</p>
                </div>
            </div>
            <div class="auto-box-body">

                <div class="method-grid">
                    @foreach($methods as $val => $m)
                    <label class="method-item">
                        <input type="radio" name="assignment_method" value="{{ $val }}"
                               class="method-radio" {{ $selectedMethod===$val ? 'checked' : '' }}>
                        <div class="method-card">
                            <div class="method-ico"><i class="{{ $m['icon'] }}"></i></div>
                            <p class="method-ttl">{{ $m['title'] }}</p>
                            <p class="method-dsc">{{ $m['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Single user picker --}}
                <div id="singleSection" class="{{ $selectedMethod !== 'single_user' ? 'd-none' : '' }}">
                    <div class="field-label">User select karo</div>
                    <select name="single_user_id" class="form-select">
                        <option value="">-- Select User --</option>
                        @foreach($salesUsers as $u)
                        <option value="{{ $u->id }}"
                            {{ old('single_user_id', $rule->single_user_id ?? '') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} — {{ $u->role->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Multi-user table --}}
                <div id="multiSection" class="{{ $selectedMethod === 'single_user' ? 'd-none' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="field-label mb-0">Users</div>
                        <button type="button" id="addUserBtn"
                                class="btn btn-sm btn-outline-primary px-3" style="font-size:12px;">
                            <i class="fas fa-plus me-1"></i>User Add Karo
                        </button>
                    </div>
                    <div class="border rounded-3 overflow-hidden">
                        <table class="usr-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th id="pctHead" class="{{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}"
                                        style="width:150px;">Percentage</th>
                                    <th style="width:160px;">Daily Limit</th>
                                    <th style="width:46px;"></th>
                                </tr>
                            </thead>
                            <tbody id="usersBody">
                                @foreach($existingUsers as $i => $eu)
                                <tr class="usr-row">
                                    <td>
                                        <select name="users[{{ $i }}][user_id]" class="form-select form-select-sm">
                                            <option value="">-- Select User --</option>
                                            @foreach($salesUsers as $u)
                                            <option value="{{ $u->id }}" {{ ($eu['user_id']??'') == $u->id ? 'selected':'' }}>
                                                {{ $u->name }} — {{ $u->role->name ?? '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="pct-col {{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="users[{{ $i }}][percentage]"
                                                   class="form-control pct-inp" placeholder="0"
                                                   min="0" max="100" step="0.1"
                                                   value="{{ $eu['percentage'] ?? '' }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="users[{{ $i }}][daily_limit]"
                                               class="form-control form-control-sm"
                                               placeholder="Unlimited" min="1"
                                               value="{{ $eu['daily_limit'] ?? '' }}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-remove p-1 text-danger"
                                                title="Remove" style="line-height:1;">
                                            <i class="fas fa-trash-alt" style="font-size:12px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pct bar --}}
                    <div id="pctBarWrap" class="mt-2 {{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="progress flex-grow-1" style="height:7px;border-radius:6px;">
                                <div id="pctBar" class="progress-bar bg-success"
                                     style="width:0%;transition:width .3s;border-radius:6px;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:700;min-width:70px;">
                                Total: <span id="pctVal" class="text-danger">0</span>%
                            </span>
                        </div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:3px;">Total 100% hona chahiye</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ══════════════════════════════════════════
             STEP 3 — SETTINGS
        ══════════════════════════════════════════ --}}
        <div class="auto-box">
            <div class="auto-box-head">
                <div class="auto-box-num">3</div>
                <div>
                    <p class="auto-box-title">Task & Settings</p>
                    <p class="auto-box-sub">Rule ka naam, limits aur task configuration</p>
                </div>
            </div>
            <div class="auto-box-body">

                <div class="row g-3 mb-4">
                    {{-- Rule Name --}}
                    <div class="col-12 col-md-6">
                        <div class="field-label">Rule Name <span class="text-danger">*</span></div>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g. Facebook Form A → Round Robin"
                               value="{{ old('name', $rule->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Daily Limit --}}
                    <div class="col-6 col-md-3">
                        <div class="field-label">Daily Limit</div>
                        <div class="input-group">
                            <input type="number" name="daily_limit" class="form-control"
                                   placeholder="Unlimited" min="1"
                                   value="{{ old('daily_limit', $rule->daily_limit ?? '') }}">
                            <span class="input-group-text small text-muted">/ day</span>
                        </div>
                        <div class="field-hint">Rule se aaj max kitne leads assign honge</div>
                    </div>

                    {{-- Fallback User --}}
                    <div class="col-6 col-md-3">
                        <div class="field-label">Fallback User</div>
                        <select name="fallback_user_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($salesUsers as $u)
                            <option value="{{ $u->id }}"
                                {{ old('fallback_user_id', $rule->fallback_user_id ?? '') == $u->id ? 'selected':'' }}>
                                {{ $u->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="field-hint">Jab koi user available na ho</div>
                    </div>
                </div>

                {{-- Toggles --}}
                <label class="tog-row">
                    <div class="pt-1">
                        <input class="form-check-input" type="checkbox" id="autoTask"
                               name="auto_create_task" value="1"
                               style="width:42px;height:22px;"
                               {{ old('auto_create_task', $rule->auto_create_task ?? true) ? 'checked':'' }}>
                    </div>
                    <div>
                        <p class="tog-ttl">
                            <i class="fas fa-tasks me-1" style="color:#198754;"></i>
                            Auto-create Calling Task
                        </p>
                        <p class="tog-dsc">Lead assign hone ke baad telecaller dashboard pe calling task automatically banega</p>
                    </div>
                </label>

                <label class="tog-row">
                    <div class="pt-1">
                        <input class="form-check-input" type="checkbox" id="isActive"
                               name="is_active" value="1"
                               style="width:42px;height:22px;"
                               {{ old('is_active', $rule->is_active ?? true) ? 'checked':'' }}>
                    </div>
                    <div>
                        <p class="tog-ttl">
                            <i class="fas fa-circle me-1" style="color:#0d6efd;"></i>
                            Rule Active
                        </p>
                        <p class="tog-dsc">Band karo to is source ke leads unassigned rahenge</p>
                    </div>
                </label>

                <div class="submit-bar">
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-save me-2"></i>{{ isset($rule) ? 'Update Rule' : 'Create Rule' }}
                    </button>
                    <a href="{{ route('admin.automation.index') }}" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
/* ── Source card colors ── */
const SRC_COLOR = { facebook_lead_ads:'#1877f2', pabbly:'#ff6600', mcube:'#6f42c1', google_sheets:'#0f9d58', csv:'#0077b6', all:'#28a745' };
const SRC_BG    = { facebook_lead_ads:'#e7f0fd', pabbly:'#fff0e6', mcube:'#f0ebfd', google_sheets:'#e6f7ee', csv:'#e6f2fb', all:'#eafaf1' };

document.querySelectorAll('.src-radio').forEach(r => {
    r.addEventListener('change', function() {
        // Reset all
        document.querySelectorAll('.src-card').forEach(c => { c.style.borderColor='#e9ecef'; c.style.background='#fff'; });
        // Highlight selected
        const c = this.closest('.src-item').querySelector('.src-card');
        c.style.borderColor = SRC_COLOR[this.value] || '#198754';
        c.style.background  = SRC_BG[this.value]    || '#f0fdf4';
        // FB panel
        document.getElementById('fbPanel').classList.toggle('d-none', this.value !== 'facebook_lead_ads');
    });
});

/* ── Method card ── */
const isPct = () => document.querySelector('.method-radio:checked')?.value === 'percentage';

document.querySelectorAll('.method-radio').forEach(r => {
    r.addEventListener('change', function() {
        const isSingle = this.value === 'single_user';
        document.getElementById('singleSection').classList.toggle('d-none', !isSingle);
        document.getElementById('multiSection').classList.toggle('d-none', isSingle);
        document.getElementById('pctHead').classList.toggle('d-none', !isPct());
        document.querySelectorAll('.pct-col').forEach(td => td.classList.toggle('d-none', !isPct()));
        document.getElementById('pctBarWrap').classList.toggle('d-none', !isPct());
        updatePct();
    });
});

/* ── Sales users options (server-rendered) ── */
const OPTS = `<option value="">-- Select User --</option>` +
    @json($salesUsers->map(fn($u)=>['id'=>$u->id,'n'=>$u->name.' — '.($u->role->name??'')]))
    .map(u=>`<option value="${u.id}">${u.n}</option>`).join('');

let idx = {{ count($existingUsers) }};

document.getElementById('addUserBtn').addEventListener('click', () => {
    const pH = isPct() ? '' : 'd-none';
    const tr = document.createElement('tr');
    tr.className = 'usr-row';
    tr.innerHTML = `
        <td><select name="users[${idx}][user_id]" class="form-select form-select-sm">${OPTS}</select></td>
        <td class="pct-col ${pH}">
            <div class="input-group input-group-sm">
                <input type="number" name="users[${idx}][percentage]" class="form-control pct-inp" placeholder="0" min="0" max="100" step="0.1">
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td><input type="number" name="users[${idx}][daily_limit]" class="form-control form-control-sm" placeholder="Unlimited" min="1"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-remove p-1 text-danger" style="line-height:1;">
                <i class="fas fa-trash-alt" style="font-size:12px;"></i>
            </button>
        </td>`;
    document.getElementById('usersBody').appendChild(tr);
    tr.querySelector('.btn-remove').addEventListener('click', () => { tr.remove(); updatePct(); });
    idx++;
});

// Remove existing rows
document.querySelectorAll('.btn-remove').forEach(b => {
    b.addEventListener('click', () => { b.closest('tr').remove(); updatePct(); });
});

/* ── Percentage bar ── */
function updatePct() {
    let sum = 0;
    document.querySelectorAll('.pct-inp').forEach(i => { sum += parseFloat(i.value)||0; });
    sum = Math.round(sum*10)/10;
    const el  = document.getElementById('pctVal');
    const bar = document.getElementById('pctBar');
    el.textContent = sum;
    const ok = Math.abs(sum-100) < 0.1;
    el.style.color = ok ? '#198754' : (sum > 100 ? '#dc3545' : '#f59e0b');
    bar.style.width = Math.min(sum,100)+'%';
    bar.className   = 'progress-bar ' + (ok ? 'bg-success' : sum > 100 ? 'bg-danger' : 'bg-warning');
}
document.getElementById('usersBody').addEventListener('input', updatePct);
updatePct();
</script>
@endpush
