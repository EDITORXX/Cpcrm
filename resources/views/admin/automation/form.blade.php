@extends('layouts.app')

@section('title', isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule')

@push('styles')
<style>
/* ===== WIZARD STEPS ===== */
.wizard-steps {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 2rem;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    position: relative;
}
.wizard-step:not(:last-child)::after {
    content: '';
    flex: 1;
    height: 2px;
    background: #e0e0e0;
    margin: 0 12px;
}
.step-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
    background: #e9ecef;
    color: #6c757d;
    border: 2px solid #dee2e6;
    transition: all 0.3s;
}
.step-circle.active {
    background: var(--bs-primary, #198754);
    color: white;
    border-color: var(--bs-primary, #198754);
}
.step-label {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    white-space: nowrap;
}
.step-label.active { color: #212529; }

/* ===== SOURCE CARDS ===== */
.source-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
@media (max-width: 768px) { .source-cards-grid { grid-template-columns: repeat(2, 1fr); } }

.source-card-item {
    position: relative;
    cursor: pointer;
}
.source-card-item input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.source-card-inner {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px 12px;
    text-align: center;
    transition: all 0.2s ease;
    background: #fff;
    cursor: pointer;
    height: 100%;
}
.source-card-inner:hover {
    border-color: #adb5bd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}
.source-card-item input:checked + .source-card-inner {
    border-width: 2px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}
.source-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 1.4rem;
}
.source-card-name {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}
.source-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
}
.source-card-item input:checked ~ .source-check { display: flex; }

/* ===== METHOD CARDS ===== */
.method-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
@media (min-width: 992px) { .method-cards-grid { grid-template-columns: repeat(4, 1fr); } }

.method-card-item {
    position: relative;
    cursor: pointer;
}
.method-card-item input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.method-card-inner {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 14px;
    transition: all 0.2s;
    background: #fff;
    cursor: pointer;
    height: 100%;
}
.method-card-inner:hover {
    border-color: #adb5bd;
    background: #f8f9fa;
}
.method-card-item input:checked + .method-card-inner {
    border-color: #198754;
    background: #f0fdf4;
}
.method-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #f1f3f5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 14px;
    color: #495057;
}
.method-card-item input:checked + .method-card-inner .method-icon {
    background: #198754;
    color: white;
}
.method-title { font-size: 13px; font-weight: 700; color: #212529; margin: 0 0 2px; }
.method-desc  { font-size: 11px; color: #6c757d; margin: 0; line-height: 1.4; }

/* ===== USER TABLE ===== */
.users-table { border-collapse: separate; border-spacing: 0; }
.users-table thead th {
    background: #f8f9fa;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    border-bottom: 2px solid #e9ecef;
    padding: 10px 14px;
}
.users-table tbody td { padding: 10px 14px; vertical-align: middle; border-bottom: 1px solid #f1f3f5; }
.users-table tbody tr:last-child td { border-bottom: none; }
.users-table tbody tr:hover td { background: #fafafa; }

/* ===== SECTION CARD ===== */
.section-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    margin-bottom: 20px;
    overflow: hidden;
}
.section-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fafafa;
}
.section-num {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    background: #212529;
    color: white;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.section-title { font-size: 15px; font-weight: 700; color: #212529; margin: 0; }
.section-card-body { padding: 20px; }

/* ===== FB FORM PANEL ===== */
.fb-form-panel {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 16px;
}

/* ===== TOGGLE SWITCH ===== */
.toggle-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    background: #fff;
}
.toggle-row + .toggle-row { margin-top: 10px; }
.form-check-input { cursor: pointer; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:900px;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.automation.index') }}"
           class="btn btn-sm btn-outline-secondary rounded-circle"
           style="width:36px;height:36px;padding:0;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-bold">{{ isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule' }}</h5>
            <div class="text-muted small">Lead source → distribution → task settings</div>
        </div>
    </div>

    {{-- Progress Steps --}}
    <div class="wizard-steps mb-4">
        <div class="wizard-step">
            <div class="step-circle active">1</div>
            <span class="step-label active">Lead Source</span>
        </div>
        <div style="flex:1;height:2px;background:#e0e0e0;margin:0 8px;"></div>
        <div class="wizard-step">
            <div class="step-circle active">2</div>
            <span class="step-label active">Distribution</span>
        </div>
        <div style="flex:1;height:2px;background:#e0e0e0;margin:0 8px;"></div>
        <div class="wizard-step">
            <div class="step-circle active">3</div>
            <span class="step-label active">Settings</span>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Please fix these errors:</strong>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($rule) ? route('admin.automation.update', $rule) : route('admin.automation.store') }}">
        @csrf
        @if(isset($rule)) @method('PUT') @endif

        @php
            $selectedSource = old('source', $rule->source ?? '');
            $selectedMethod = old('assignment_method', $rule->assignment_method ?? 'round_robin');
            $existingUsers  = old('users', isset($rule) ? $rule->users->map(fn($ru) => [
                'user_id'     => $ru->user_id,
                'percentage'  => $ru->percentage,
                'daily_limit' => $ru->daily_limit,
            ])->toArray() : [['user_id'=>'','percentage'=>'','daily_limit'=>'']]);
        @endphp

        {{-- ===== STEP 1: Lead Source ===== --}}
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-num">1</div>
                <div>
                    <p class="section-title mb-0">Lead Source</p>
                    <div class="text-muted" style="font-size:12px;">Kahan se aaye lead ko is rule se handle karna hai?</div>
                </div>
            </div>
            <div class="section-card-body">
                @php
                    $sources = [
                        'facebook_lead_ads' => ['label'=>'Facebook Lead Ads','icon'=>'fab fa-facebook','color'=>'#1877f2','bg'=>'#e7f0fd'],
                        'pabbly'            => ['label'=>'Pabbly',           'icon'=>'fas fa-bolt',      'color'=>'#ff6600','bg'=>'#fff0e6'],
                        'mcube'             => ['label'=>'MCube',            'icon'=>'fas fa-phone',     'color'=>'#6f42c1','bg'=>'#f0ebfd'],
                        'google_sheets'     => ['label'=>'Google Sheets',    'icon'=>'fas fa-table',     'color'=>'#0f9d58','bg'=>'#e6f7ee'],
                        'csv'               => ['label'=>'CSV Import',       'icon'=>'fas fa-file-csv',  'color'=>'#0077b6','bg'=>'#e6f2fb'],
                        'all'               => ['label'=>'All Sources',      'icon'=>'fas fa-globe',     'color'=>'#28a745','bg'=>'#eafaf1'],
                    ];
                @endphp
                <div class="source-cards-grid">
                    @foreach($sources as $value => $s)
                    <label class="source-card-item">
                        <input type="radio" name="source" value="{{ $value }}"
                               class="source-radio"
                               {{ $selectedSource === $value ? 'checked' : '' }}>
                        <div class="source-card-inner"
                             style="{{ $selectedSource === $value ? "border-color:{$s['color']};background:{$s['bg']};" : '' }}">
                            <div class="source-icon-wrap" style="background:{{ $s['bg'] }};">
                                <i class="{{ $s['icon'] }}" style="color:{{ $s['color'] }};"></i>
                            </div>
                            <p class="source-card-name">{{ $s['label'] }}</p>
                        </div>
                        <div class="source-check" style="background:{{ $s['color'] }};">
                            <i class="fas fa-check"></i>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Facebook Form Panel --}}
                <div id="fbFormPanel" class="fb-form-panel {{ $selectedSource !== 'facebook_lead_ads' ? 'd-none' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fab fa-facebook" style="color:#1877f2;"></i>
                        <strong style="font-size:13px;">Facebook Form (optional)</strong>
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
                    <div class="text-muted mt-1" style="font-size:11px;">
                        <i class="fas fa-info-circle me-1"></i>
                        Ek specific form select karo to sirf us form ke leads yahan aayenge. Baaki forms ke liye alag rule banao.
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== STEP 2: Distribution Logic ===== --}}
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-num">2</div>
                <div>
                    <p class="section-title mb-0">Distribution Logic</p>
                    <div class="text-muted" style="font-size:12px;">Lead kaise assign karna hai?</div>
                </div>
            </div>
            <div class="section-card-body">
                @php
                    $methods = [
                        'round_robin'     => ['title'=>'Round Robin',     'desc'=>'Baari baari sabko — A→B→C→A',    'icon'=>'fas fa-sync-alt'],
                        'first_available' => ['title'=>'First Available', 'desc'=>'Jiske paas sabse kam leads ho', 'icon'=>'fas fa-user-check'],
                        'percentage'      => ['title'=>'Percentage',      'desc'=>'A ko 40%, B ko 30%, C ko 30%',  'icon'=>'fas fa-percent'],
                        'single_user'     => ['title'=>'Single User',     'desc'=>'Sab leads ek hi user ko',       'icon'=>'fas fa-user'],
                    ];
                @endphp
                <div class="method-cards-grid mb-4">
                    @foreach($methods as $val => $m)
                    <label class="method-card-item">
                        <input type="radio" name="assignment_method" value="{{ $val }}"
                               class="method-radio"
                               {{ $selectedMethod === $val ? 'checked' : '' }}>
                        <div class="method-card-inner">
                            <div class="method-icon">
                                <i class="{{ $m['icon'] }}"></i>
                            </div>
                            <p class="method-title">{{ $m['title'] }}</p>
                            <p class="method-desc">{{ $m['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Single User picker --}}
                <div id="singleUserSection" class="{{ $selectedMethod !== 'single_user' ? 'd-none' : '' }}">
                    <label class="form-label small fw-semibold text-muted text-uppercase" style="letter-spacing:.5px;">User</label>
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
                <div id="usersSection" class="{{ $selectedMethod === 'single_user' ? 'd-none' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-0" style="letter-spacing:.5px;">Users</label>
                        <button type="button" class="btn btn-sm btn-outline-primary px-3" id="addUserRow" style="font-size:12px;">
                            <i class="fas fa-plus me-1"></i>User Add Karo
                        </button>
                    </div>
                    <div class="border rounded-3 overflow-hidden">
                        <table class="table users-table mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th id="pctHeader" class="{{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}" style="width:130px;">
                                        Percentage
                                    </th>
                                    <th style="width:140px;">Daily Limit</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                @foreach($existingUsers as $i => $eu)
                                <tr class="user-row">
                                    <td>
                                        <select name="users[{{ $i }}][user_id]" class="form-select form-select-sm">
                                            <option value="">-- Select User --</option>
                                            @foreach($salesUsers as $u)
                                                <option value="{{ $u->id }}" {{ ($eu['user_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }} — {{ $u->role->name ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="pct-col {{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="users[{{ $i }}][percentage]"
                                                   class="form-control pct-input" placeholder="0"
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
                                    <td>
                                        <button type="button" class="btn btn-sm text-danger btn-remove-user p-1"
                                                style="line-height:1;" title="Remove">
                                            <i class="fas fa-trash-alt" style="font-size:13px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Percentage total --}}
                    <div id="pctTotalRow" class="mt-2 {{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;border-radius:4px;">
                                <div id="pctBar" class="progress-bar bg-success" style="width:0%;transition:width .3s;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:600;min-width:80px;">
                                Total: <span id="pctSum" class="text-success">0</span>%
                            </span>
                        </div>
                        <div style="font-size:11px;color:#6c757d;" class="mt-1">Total 100% hona chahiye</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== STEP 3: Settings ===== --}}
        <div class="section-card">
            <div class="section-card-header">
                <div class="section-num">3</div>
                <div>
                    <p class="section-title mb-0">Task & Settings</p>
                    <div class="text-muted" style="font-size:12px;">Rule ka naam aur task configuration</div>
                </div>
            </div>
            <div class="section-card-body">
                <div class="row g-3 mb-4">
                    {{-- Rule Name --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="letter-spacing:.5px;">
                            Rule Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g. Facebook Form A → Round Robin"
                               value="{{ old('name', $rule->name ?? '') }}" required>
                        <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                    </div>

                    {{-- Daily Limit --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="letter-spacing:.5px;">
                            Daily Limit (rule-level)
                        </label>
                        <div class="input-group">
                            <input type="number" name="daily_limit" class="form-control"
                                   placeholder="Unlimited" min="1"
                                   value="{{ old('daily_limit', $rule->daily_limit ?? '') }}">
                            <span class="input-group-text text-muted small">leads/day</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:11px;">Is rule se aaj kitne max leads assign honge</div>
                    </div>

                    {{-- Fallback User --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="letter-spacing:.5px;">
                            Fallback User
                        </label>
                        <select name="fallback_user_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($salesUsers as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('fallback_user_id', $rule->fallback_user_id ?? '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} — {{ $u->role->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted mt-1" style="font-size:11px;">Jab koi user available na ho to yahan send karo</div>
                    </div>
                </div>

                {{-- Toggles --}}
                <div class="toggle-row">
                    <div class="form-check form-switch mb-0 pt-1">
                        <input class="form-check-input" type="checkbox" id="autoCreateTask"
                               name="auto_create_task" value="1" style="width:40px;height:22px;"
                               {{ old('auto_create_task', $rule->auto_create_task ?? true) ? 'checked' : '' }}>
                    </div>
                    <div>
                        <label for="autoCreateTask" class="fw-semibold mb-0" style="font-size:14px;cursor:pointer;">
                            <i class="fas fa-tasks me-1 text-success"></i> Auto-create Calling Task
                        </label>
                        <div class="text-muted small">Lead assign hone ke baad telecaller dashboard pe calling task automatically banega</div>
                    </div>
                </div>

                <div class="toggle-row">
                    <div class="form-check form-switch mb-0 pt-1">
                        <input class="form-check-input" type="checkbox" id="isActive"
                               name="is_active" value="1" style="width:40px;height:22px;"
                               {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
                    </div>
                    <div>
                        <label for="isActive" class="fw-semibold mb-0" style="font-size:14px;cursor:pointer;">
                            <i class="fas fa-circle me-1 text-primary"></i> Rule Active
                        </label>
                        <div class="text-muted small">Band karo to is source ke leads unassigned rahenge</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
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
const sourceColors = {
    facebook_lead_ads: '#1877f2', pabbly: '#ff6600', mcube: '#6f42c1',
    google_sheets: '#0f9d58', csv: '#0077b6', all: '#28a745'
};
const sourceBgs = {
    facebook_lead_ads: '#e7f0fd', pabbly: '#fff0e6', mcube: '#f0ebfd',
    google_sheets: '#e6f7ee', csv: '#e6f2fb', all: '#eafaf1'
};

// Source card selection
document.querySelectorAll('.source-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.source-card-inner').forEach(c => {
            c.style.borderColor = '#e9ecef';
            c.style.background  = '#fff';
        });
        const color = sourceColors[this.value] || '#198754';
        const bg    = sourceBgs[this.value] || '#f0fdf4';
        const inner = this.closest('.source-card-item').querySelector('.source-card-inner');
        inner.style.borderColor = color;
        inner.style.background  = bg;

        document.getElementById('fbFormPanel').classList.toggle('d-none', this.value !== 'facebook_lead_ads');
    });
});

// Method card selection
const isPct = () => document.querySelector('.method-radio:checked')?.value === 'percentage';

document.querySelectorAll('.method-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        const isSingle = this.value === 'single_user';
        document.getElementById('singleUserSection').classList.toggle('d-none', !isSingle);
        document.getElementById('usersSection').classList.toggle('d-none', isSingle);
        document.getElementById('pctHeader').classList.toggle('d-none', !isPct());
        document.querySelectorAll('.pct-col').forEach(td => td.classList.toggle('d-none', !isPct()));
        document.getElementById('pctTotalRow').classList.toggle('d-none', !isPct());
        updatePctBar();
    });
});

// Add user row
let rowIdx = {{ count($existingUsers) }};
const salesOptions = `<option value="">-- Select User --</option>` +
    @json($salesUsers->map(fn($u) => ['id'=>$u->id,'label'=>$u->name.' — '.($u->role->name??'')]))
    .map(u => `<option value="${u.id}">${u.label}</option>`).join('');

document.getElementById('addUserRow').addEventListener('click', () => {
    const pctHide = isPct() ? '' : 'd-none';
    const tr = document.createElement('tr');
    tr.className = 'user-row';
    tr.innerHTML = `
        <td><select name="users[${rowIdx}][user_id]" class="form-select form-select-sm">${salesOptions}</select></td>
        <td class="pct-col ${pctHide}">
            <div class="input-group input-group-sm">
                <input type="number" name="users[${rowIdx}][percentage]" class="form-control pct-input" placeholder="0" min="0" max="100" step="0.1">
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td><input type="number" name="users[${rowIdx}][daily_limit]" class="form-control form-control-sm" placeholder="Unlimited" min="1"></td>
        <td><button type="button" class="btn btn-sm text-danger btn-remove-user p-1" style="line-height:1;">
            <i class="fas fa-trash-alt" style="font-size:13px;"></i></button></td>
    `;
    document.getElementById('usersTableBody').appendChild(tr);
    tr.querySelector('.btn-remove-user').addEventListener('click', () => { tr.remove(); updatePctBar(); });
    rowIdx++;
});

// Remove rows
document.querySelectorAll('.btn-remove-user').forEach(btn => {
    btn.addEventListener('click', () => { btn.closest('tr').remove(); updatePctBar(); });
});

// Percentage bar
function updatePctBar() {
    let sum = 0;
    document.querySelectorAll('.pct-input').forEach(inp => { sum += parseFloat(inp.value) || 0; });
    sum = Math.round(sum * 10) / 10;
    document.getElementById('pctSum').textContent = sum;
    document.getElementById('pctSum').style.color = Math.abs(sum - 100) < 0.1 ? '#198754' : '#dc3545';
    const bar = document.getElementById('pctBar');
    bar.style.width = Math.min(sum, 100) + '%';
    bar.className = 'progress-bar ' + (Math.abs(sum - 100) < 0.1 ? 'bg-success' : sum > 100 ? 'bg-danger' : 'bg-warning');
}
document.getElementById('usersTableBody').addEventListener('input', updatePctBar);
updatePctBar();
</script>
@endpush
