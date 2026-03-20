@extends('layouts.app')

@section('title', isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.automation.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-magic me-2 text-primary"></i>
                {{ isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule' }}
            </h4>
            <small class="text-muted">Lead source configure karo → users assign karo → task settings</small>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($rule) ? route('admin.automation.update', $rule) : route('admin.automation.store') }}">
        @csrf
        @if(isset($rule)) @method('PUT') @endif

        <div class="row g-4">

            {{-- Step 1: Lead Source --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <span class="badge bg-primary me-2">1</span>Lead Source
                        </h6>

                        <div class="row g-3">
                            @php
                                $sources = [
                                    'facebook_lead_ads' => ['label' => 'Facebook Lead Ads', 'icon' => 'fab fa-facebook', 'color' => '#1877f2'],
                                    'pabbly'            => ['label' => 'Pabbly',             'icon' => 'fas fa-bolt',       'color' => '#ff6600'],
                                    'mcube'             => ['label' => 'MCube',              'icon' => 'fas fa-phone',      'color' => '#6f42c1'],
                                    'google_sheets'     => ['label' => 'Google Sheets',      'icon' => 'fas fa-table',      'color' => '#0f9d58'],
                                    'csv'               => ['label' => 'CSV Import',         'icon' => 'fas fa-file-csv',   'color' => '#17a2b8'],
                                    'all'               => ['label' => 'All Sources',        'icon' => 'fas fa-globe',      'color' => '#28a745'],
                                ];
                                $selectedSource = old('source', $rule->source ?? '');
                            @endphp

                            @foreach($sources as $value => $s)
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="source-card w-100 {{ $selectedSource === $value ? 'selected' : '' }}"
                                       style="cursor:pointer;">
                                    <input type="radio" name="source" value="{{ $value }}"
                                           class="source-radio d-none"
                                           {{ $selectedSource === $value ? 'checked' : '' }}>
                                    <div class="card border-2 text-center p-3 h-100 source-option"
                                         style="border-color:{{ $selectedSource === $value ? $s['color'] : '#dee2e6' }} !important;
                                                background:{{ $selectedSource === $value ? $s['color'].'15' : 'white' }};">
                                        <i class="{{ $s['icon'] }} fa-2x mb-2" style="color:{{ $s['color'] }};"></i>
                                        <div class="small fw-semibold">{{ $s['label'] }}</div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        {{-- FB Form selector (only shows when Facebook selected) --}}
                        <div id="fbFormSection" class="mt-3 {{ $selectedSource !== 'facebook_lead_ads' ? 'd-none' : '' }}">
                            <label class="form-label fw-semibold">
                                <i class="fab fa-facebook me-1 text-primary"></i>
                                Facebook Form (optional)
                            </label>
                            <select name="fb_form_id" class="form-select">
                                <option value="">-- Kisi bhi form se aaye lead (any form) --</option>
                                @foreach($fbForms as $form)
                                    <option value="{{ $form->id }}"
                                        {{ old('fb_form_id', $rule->fb_form_id ?? '') == $form->id ? 'selected' : '' }}>
                                        {{ $form->page->page_name ?? 'Page' }} — {{ $form->form_name ?? $form->form_id }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Specific form select karo to sirf us form ke leads is rule se assign honge.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Distribution Logic --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <span class="badge bg-primary me-2">2</span>Distribution Logic
                        </h6>

                        @php
                            $methods = [
                                'round_robin'     => ['label' => 'Round Robin', 'desc' => 'Baari baari sabko — ek ke baad doosra', 'icon' => 'fas fa-sync-alt'],
                                'first_available' => ['label' => 'First Available', 'desc' => 'Jiske paas sabse kam leads ho usko', 'icon' => 'fas fa-user-check'],
                                'percentage'      => ['label' => 'Percentage', 'desc' => 'Custom % — A ko 40%, B ko 30%, C ko 30%', 'icon' => 'fas fa-percent'],
                                'single_user'     => ['label' => 'Single User', 'desc' => 'Sab leads ek hi user ko', 'icon' => 'fas fa-user'],
                            ];
                            $selectedMethod = old('assignment_method', $rule->assignment_method ?? 'round_robin');
                        @endphp

                        <div class="row g-2 mb-3">
                            @foreach($methods as $val => $m)
                            <div class="col-6 col-md-3">
                                <label class="method-card w-100" style="cursor:pointer;">
                                    <input type="radio" name="assignment_method" value="{{ $val }}"
                                           class="method-radio d-none"
                                           {{ $selectedMethod === $val ? 'checked' : '' }}>
                                    <div class="card border-2 p-3 method-option
                                                {{ $selectedMethod === $val ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="{{ $m['icon'] }} text-primary"></i>
                                            <strong class="small">{{ $m['label'] }}</strong>
                                        </div>
                                        <div class="text-muted" style="font-size:0.75rem;">{{ $m['desc'] }}</div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        {{-- Single user picker --}}
                        <div id="singleUserSection" class="{{ $selectedMethod !== 'single_user' ? 'd-none' : '' }}">
                            <label class="form-label fw-semibold">User select karo</label>
                            <select name="single_user_id" class="form-select">
                                <option value="">-- Select User --</option>
                                @foreach($salesUsers as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('single_user_id', $rule->single_user_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->role->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Multi-user table (Round Robin / First Available / Percentage) --}}
                        <div id="usersSection" class="{{ $selectedMethod === 'single_user' ? 'd-none' : '' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold mb-0">Users</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addUserRow">
                                    <i class="fas fa-plus me-1"></i> User Add Karo
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="usersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>User</th>
                                            <th id="pctHeader" class="{{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                                                Percentage (%)
                                            </th>
                                            <th>Daily Limit</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody">
                                        @php
                                            $existingUsers = old('users', isset($rule) ? $rule->users->map(fn($ru) => [
                                                'user_id' => $ru->user_id,
                                                'percentage' => $ru->percentage,
                                                'daily_limit' => $ru->daily_limit,
                                            ])->toArray() : []);
                                        @endphp

                                        @forelse($existingUsers as $i => $eu)
                                        <tr class="user-row">
                                            <td>
                                                <select name="users[{{ $i }}][user_id]" class="form-select form-select-sm" required>
                                                    <option value="">-- Select User --</option>
                                                    @foreach($salesUsers as $u)
                                                        <option value="{{ $u->id }}" {{ $eu['user_id'] == $u->id ? 'selected' : '' }}>
                                                            {{ $u->name }} ({{ $u->role->name ?? '' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="pct-col {{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                                                <input type="number" name="users[{{ $i }}][percentage]"
                                                       class="form-control form-control-sm" placeholder="e.g. 40"
                                                       min="0" max="100" step="0.01"
                                                       value="{{ $eu['percentage'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" name="users[{{ $i }}][daily_limit]"
                                                       class="form-control form-control-sm" placeholder="Unlimited"
                                                       min="1" value="{{ $eu['daily_limit'] ?? '' }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-user">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr class="user-row">
                                            <td>
                                                <select name="users[0][user_id]" class="form-select form-select-sm" required>
                                                    <option value="">-- Select User --</option>
                                                    @foreach($salesUsers as $u)
                                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role->name ?? '' }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="pct-col {{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                                                <input type="number" name="users[0][percentage]"
                                                       class="form-control form-control-sm" placeholder="e.g. 40"
                                                       min="0" max="100" step="0.01">
                                            </td>
                                            <td>
                                                <input type="number" name="users[0][daily_limit]"
                                                       class="form-control form-control-sm" placeholder="Unlimited" min="1">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-user">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Percentage total indicator --}}
                            <div id="pctTotal" class="{{ $selectedMethod !== 'percentage' ? 'd-none' : '' }}">
                                <small>Total: <strong id="pctSum" class="text-success">0</strong>% (100% hona chahiye)</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Step 3: Task & Settings --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <span class="badge bg-primary me-2">3</span>Task & Settings
                        </h6>

                        <div class="row g-3">
                            {{-- Rule Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rule Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="e.g. Facebook Form A → Round Robin"
                                       value="{{ old('name', $rule->name ?? '') }}" required>
                            </div>

                            {{-- Daily Limit --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Daily Limit (rule-level)</label>
                                <input type="number" name="daily_limit" class="form-control"
                                       placeholder="Unlimited" min="1"
                                       value="{{ old('daily_limit', $rule->daily_limit ?? '') }}">
                                <small class="text-muted">Is rule se aaj kitne leads assign honge (max)</small>
                            </div>

                            {{-- Fallback User --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Fallback User</label>
                                <select name="fallback_user_id" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($salesUsers as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('fallback_user_id', $rule->fallback_user_id ?? '') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jab koi user available na ho to yahan send karo</small>
                            </div>

                            {{-- Auto Create Task --}}
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="autoCreateTask"
                                           name="auto_create_task" value="1"
                                           {{ old('auto_create_task', $rule->auto_create_task ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="autoCreateTask">
                                        <i class="fas fa-tasks me-1 text-success"></i>
                                        Auto-create Calling Task
                                    </label>
                                    <div class="text-muted small">Lead assign hone k baad telecaller ke dashboard pe calling task auto-banao</div>
                                </div>
                            </div>

                            {{-- Is Active --}}
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="isActive"
                                           name="is_active" value="1"
                                           {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isActive">
                                        <i class="fas fa-toggle-on me-1 text-primary"></i>
                                        Rule Active
                                    </label>
                                    <div class="text-muted small">Band karo to is source ke leads unassigned rahenge</div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ isset($rule) ? 'Update Rule' : 'Create Rule' }}
                            </button>
                            <a href="{{ route('admin.automation.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ===== Source card selection =====
const sourceColors = {
    facebook_lead_ads: '#1877f2',
    pabbly: '#ff6600',
    mcube: '#6f42c1',
    google_sheets: '#0f9d58',
    csv: '#17a2b8',
    all: '#28a745',
};

document.querySelectorAll('.source-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.source-option').forEach(card => {
            card.style.borderColor = '#dee2e6';
            card.style.background = 'white';
        });
        const color = sourceColors[this.value] || '#007bff';
        const option = this.closest('.source-card').querySelector('.source-option');
        option.style.borderColor = color;
        option.style.background = color + '15';

        // Show/hide FB form section
        document.getElementById('fbFormSection').classList.toggle('d-none', this.value !== 'facebook_lead_ads');
    });
});

// ===== Method card selection =====
document.querySelectorAll('.method-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.method-option').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        });
        this.closest('.method-card').querySelector('.method-option')
            .classList.add('border-primary', 'bg-primary', 'bg-opacity-10');

        const isSingle = this.value === 'single_user';
        const isPct    = this.value === 'percentage';

        document.getElementById('singleUserSection').classList.toggle('d-none', !isSingle);
        document.getElementById('usersSection').classList.toggle('d-none', isSingle);

        // Show/hide percentage columns
        document.getElementById('pctHeader').classList.toggle('d-none', !isPct);
        document.querySelectorAll('.pct-col').forEach(td => td.classList.toggle('d-none', !isPct));
        document.getElementById('pctTotal').classList.toggle('d-none', !isPct);
    });
});

// ===== Add user row =====
let rowIndex = {{ count($existingUsers ?? [['user_id'=>'']]) }};
const salesUsersOptions = `
    <option value="">-- Select User --</option>
    @foreach($salesUsers as $u)
        <option value="{{ $u->id }}">{{ addslashes($u->name) }} ({{ addslashes($u->role->name ?? '') }})</option>
    @endforeach
`;

const isPercentage = () => document.querySelector('.method-radio:checked')?.value === 'percentage';

document.getElementById('addUserRow').addEventListener('click', function() {
    const tbody = document.getElementById('usersTableBody');
    const pctHide = isPercentage() ? '' : 'd-none';
    const tr = document.createElement('tr');
    tr.className = 'user-row';
    tr.innerHTML = `
        <td>
            <select name="users[${rowIndex}][user_id]" class="form-select form-select-sm" required>
                ${salesUsersOptions}
            </select>
        </td>
        <td class="pct-col ${pctHide}">
            <input type="number" name="users[${rowIndex}][percentage]"
                   class="form-control form-control-sm" placeholder="e.g. 40" min="0" max="100" step="0.01">
        </td>
        <td>
            <input type="number" name="users[${rowIndex}][daily_limit]"
                   class="form-control form-control-sm" placeholder="Unlimited" min="1">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-user">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    tr.querySelector('.btn-remove-user').addEventListener('click', () => tr.remove());
    rowIndex++;
    updatePctSum();
});

// Remove row
document.querySelectorAll('.btn-remove-user').forEach(btn => {
    btn.addEventListener('click', function() { this.closest('tr').remove(); updatePctSum(); });
});

// Percentage sum
function updatePctSum() {
    let sum = 0;
    document.querySelectorAll('.pct-col input').forEach(inp => { sum += parseFloat(inp.value) || 0; });
    const el = document.getElementById('pctSum');
    el.textContent = sum.toFixed(1);
    el.className = Math.abs(sum - 100) < 0.1 ? 'text-success' : 'text-danger';
}
document.getElementById('usersTableBody').addEventListener('input', updatePctSum);
updatePctSum();
</script>
@endpush
