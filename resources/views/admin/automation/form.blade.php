@extends('layouts.app')
@section('title', isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule')

@push('styles')
<style>
/* ─── STEP BAR ──────────────────────── */
.stepbar{display:flex;align-items:center;margin-bottom:24px;}
.sb-dot{width:34px;height:34px;border-radius:50%;background:#198754;color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sb-lbl{font-size:13px;font-weight:600;color:#212529;margin-left:8px;white-space:nowrap;}
.sb-line{flex:1;height:2px;background:#dee2e6;margin:0 14px;}

/* ─── SECTION CARD ──────────────────── */
.acard{background:#fff;border:1px solid #e3e6ea;border-radius:16px;margin-bottom:18px;overflow:hidden;}
.acard-head{display:flex;align-items:center;gap:12px;padding:14px 22px;background:#f8f9fa;border-bottom:1px solid #e9ecef;}
.acard-num{width:28px;height:28px;border-radius:8px;background:#212529;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.acard-ttl{font-size:15px;font-weight:700;color:#212529;margin:0;line-height:1.2;}
.acard-sub{font-size:12px;color:#6c757d;margin:0;}
.acard-body{padding:22px;}

/* ─── SOURCE CARDS ──────────────────── */
.src-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;}
@media(max-width:1100px){.src-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:600px){.src-grid{grid-template-columns:repeat(2,1fr);}}
.src-item{position:relative;cursor:pointer;}
.src-item input[type=radio]{position:absolute;opacity:0;width:0;height:0;}
.src-card{border:2px solid #e9ecef;border-radius:12px;padding:18px 10px 14px;text-align:center;background:#fff;transition:all .18s;cursor:pointer;}
.src-card:hover{border-color:#adb5bd;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.08);}
.src-item input:checked+.src-card{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.12);}
.src-ico{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;margin:0 auto 9px;font-size:1.3rem;}
.src-lbl{font-size:12px;font-weight:600;color:#212529;margin:0;}
.src-tick{position:absolute;top:8px;right:8px;width:18px;height:18px;border-radius:50%;display:none;align-items:center;justify-content:center;font-size:9px;color:#fff;}
.src-item input:checked~.src-tick{display:flex;}

/* ─── FB PANEL ──────────────────────── */
.fb-panel{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-top:16px;}

/* ─── METHOD CARDS ──────────────────── */
.mth-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:0;}
@media(max-width:768px){.mth-grid{grid-template-columns:repeat(2,1fr);}}
.mth-item{position:relative;cursor:pointer;}
.mth-item input[type=radio]{position:absolute;opacity:0;width:0;height:0;}
.mth-card{border:2px solid #e9ecef;border-radius:11px;padding:15px;background:#fff;transition:all .18s;cursor:pointer;height:100%;}
.mth-card:hover{border-color:#adb5bd;background:#f9f9f9;}
.mth-item input:checked+.mth-card{border-color:#198754;background:#f0fdf4;}
.mth-ico{width:36px;height:36px;border-radius:9px;background:#f1f3f5;display:flex;align-items:center;justify-content:center;font-size:14px;color:#495057;margin-bottom:9px;transition:all .18s;}
.mth-item input:checked+.mth-card .mth-ico{background:#198754;color:#fff;}
.mth-ttl{font-size:13px;font-weight:700;color:#212529;margin:0 0 3px;}
.mth-dsc{font-size:11px;color:#6c757d;margin:0;line-height:1.4;}

/* ─── USERS PANEL ───────────────────── */
.users-panel{background:#f8f9fa;border:1px solid #e9ecef;border-radius:12px;padding:18px;margin-top:16px;}
.users-panel .panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.users-panel .panel-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#495057;margin:0;}

/* ─── USERS TABLE ───────────────────── */
.usr-tbl{width:100%;border-collapse:separate;border-spacing:0;}
.usr-tbl thead th{background:#fff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;padding:8px 12px;border-bottom:2px solid #e9ecef;}
.usr-tbl tbody td{padding:9px 12px;vertical-align:middle;border-bottom:1px solid #f3f4f6;}
.usr-tbl tbody tr:last-child td{border-bottom:none;}
.usr-tbl tbody tr:hover td{background:#fff;}
.tbl-wrap{border:1px solid #e9ecef;border-radius:10px;overflow:hidden;background:#fff;}

/* ─── SETTINGS FIELDS ───────────────── */
.fields-grid{display:grid;grid-template-columns:1fr auto auto;gap:14px;align-items:start;}
@media(max-width:900px){.fields-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:600px){.fields-grid{grid-template-columns:1fr;}}
.field-box{background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:14px 16px;}
.field-box-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#6c757d;margin-bottom:6px;}
.field-box-hint{font-size:11px;color:#9ca3af;margin-top:5px;}

/* ─── TOGGLE ROW ────────────────────── */
.tog{display:flex;align-items:flex-start;gap:14px;padding:14px 16px;border-radius:10px;border:1px solid #e9ecef;background:#fff;cursor:pointer;transition:background .15s;}
.tog:hover{background:#fafafa;}
.tog+.tog{margin-top:10px;}
.tog-ttl{font-size:14px;font-weight:600;color:#212529;margin:0 0 2px;}
.tog-dsc{font-size:12px;color:#6c757d;margin:0;}

/* ─── SUBMIT ────────────────────────── */
.submit-row{display:flex;gap:10px;padding-top:20px;border-top:1px solid #e9ecef;margin-top:20px;}

/* ─── FIELD LABEL ───────────────────── */
.flabel{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#6c757d;margin-bottom:5px;}
</style>
@endpush

@section('content')
<div class="container-fluid py-4 px-4">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.automation.index') }}"
           class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center"
           style="width:36px;height:36px;flex-shrink:0;">
            <i class="fas fa-arrow-left" style="font-size:12px;"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-bold">{{ isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule' }}</h5>
            <div class="text-muted small">Lead source → distribution → task settings</div>
        </div>
    </div>

    {{-- Step Bar --}}
    <div class="stepbar">
        <div class="sb-dot">1</div><span class="sb-lbl">Lead Source</span>
        <div class="sb-line"></div>
        <div class="sb-dot">2</div><span class="sb-lbl">Distribution Logic</span>
        <div class="sb-line"></div>
        <div class="sb-dot">3</div><span class="sb-lbl">Task & Settings</span>
    </div>

    @if($errors->any())
    <div class="alert alert-danger border-0 rounded-3 mb-4">
        <strong><i class="fas fa-exclamation-circle me-1"></i>Errors:</strong>
        <ul class="mb-0 mt-1 ps-3 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @php
        $selSrc = old('source', $rule->source ?? '');
        $selMth = old('assignment_method', $rule->assignment_method ?? 'round_robin');
        $existUsers = old('users', isset($rule) ? $rule->users->map(fn($ru)=>[
            'user_id'=>$ru->user_id,'percentage'=>$ru->percentage
        ])->toArray() : [['user_id'=>'','percentage'=>'']]);
        $isSingleLoad = $selMth === 'single_user';
        $isPctLoad    = $selMth === 'percentage';
        $sources = [
            'facebook_lead_ads'=>['label'=>'Facebook Lead Ads','icon'=>'fab fa-facebook','color'=>'#1877f2','bg'=>'#e7f0fd'],
            'pabbly'           =>['label'=>'Pabbly',           'icon'=>'fas fa-bolt',     'color'=>'#ff6600','bg'=>'#fff0e6'],
            'mcube'            =>['label'=>'MCube',            'icon'=>'fas fa-phone',    'color'=>'#6f42c1','bg'=>'#f0ebfd'],
            'google_sheets'    =>['label'=>'Google Sheets',    'icon'=>'fas fa-table',    'color'=>'#0f9d58','bg'=>'#e6f7ee'],
            'csv'              =>['label'=>'CSV Import',       'icon'=>'fas fa-file-csv', 'color'=>'#0077b6','bg'=>'#e6f2fb'],
            'all'              =>['label'=>'All Sources',      'icon'=>'fas fa-globe',    'color'=>'#28a745','bg'=>'#eafaf1'],
        ];
        $methods = [
            'round_robin'    =>['title'=>'Round Robin',    'desc'=>'Baari baari — A→B→C→A',        'icon'=>'fas fa-sync-alt'],
            'first_available'=>['title'=>'First Available','desc'=>'Jiske paas kam leads ho usko',  'icon'=>'fas fa-user-check'],
            'percentage'     =>['title'=>'Percentage',     'desc'=>'A ko 40%, B ko 30%, C ko 30%', 'icon'=>'fas fa-percent'],
            'single_user'    =>['title'=>'Single User',    'desc'=>'Sab leads ek hi user ko',      'icon'=>'fas fa-user'],
        ];
    @endphp

    <form method="POST"
          action="{{ isset($rule) ? route('admin.automation.update', $rule) : route('admin.automation.store') }}">
        @csrf @if(isset($rule)) @method('PUT') @endif

        {{-- ════════════════════════════════
             1  LEAD SOURCE
        ════════════════════════════════ --}}
        <div class="acard">
            <div class="acard-head">
                <div class="acard-num">1</div>
                <div>
                    <p class="acard-ttl">Lead Source</p>
                    <p class="acard-sub">Kahan se aaye leads ko is rule se handle karna hai?</p>
                </div>
            </div>
            <div class="acard-body">
                <div class="src-grid">
                    @foreach($sources as $val => $s)
                    <label class="src-item">
                        <input type="radio" name="source" value="{{ $val }}" class="src-radio"
                               {{ $selSrc === $val ? 'checked' : '' }}>
                        <div class="src-card"
                             style="{{ $selSrc===$val ? "border-color:{$s['color']};background:{$s['bg']};" : '' }}">
                            <div class="src-ico" style="background:{{ $s['bg'] }};">
                                <i class="{{ $s['icon'] }}" style="color:{{ $s['color'] }};"></i>
                            </div>
                            <p class="src-lbl">{{ $s['label'] }}</p>
                        </div>
                        <div class="src-tick" style="background:{{ $s['color'] }};">
                            <i class="fas fa-check"></i>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- FB Panel --}}
                <div id="fbPanel" class="fb-panel" style="{{ $selSrc !== 'facebook_lead_ads' ? 'display:none;' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fab fa-facebook" style="color:#1877f2;font-size:15px;"></i>
                        <span style="font-size:13px;font-weight:600;">
                            Facebook Form <span class="text-muted fw-normal">(optional)</span>
                        </span>
                    </div>
                    <select name="fb_form_id" class="form-select form-select-sm">
                        <option value="">-- Kisi bhi Facebook form se aaye lead --</option>
                        @foreach($fbForms as $form)
                        <option value="{{ $form->id }}"
                            {{ old('fb_form_id', $rule->fb_form_id ?? '') == $form->id ? 'selected':'' }}>
                            {{ $form->page->page_name ?? 'Page' }} — {{ $form->form_name ?? $form->form_id }}
                        </option>
                        @endforeach
                    </select>
                    <div class="mt-2" style="font-size:11px;color:#3b82f6;">
                        <i class="fas fa-info-circle me-1"></i>
                        Specific form select karo to sirf us form ke leads is rule se assign honge.
                    </div>
                </div>

                {{-- Google Sheets Panel --}}
                <div id="gsPanel" style="background:#e6f7ee;border:1px solid #b7dfcb;border-radius:10px;padding:14px 16px;margin-top:16px;{{ $selSrc !== 'google_sheets' ? 'display:none;' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-table" style="color:#0f9d58;font-size:15px;"></i>
                        <span style="font-size:13px;font-weight:600;">
                            Google Sheet <span class="text-muted fw-normal">(optional)</span>
                        </span>
                    </div>
                    <select name="google_sheet_config_id" class="form-select form-select-sm">
                        <option value="">-- Kisi bhi sheet se aaye lead --</option>
                        @foreach($googleSheets as $sheet)
                        <option value="{{ $sheet->id }}"
                            {{ old('google_sheet_config_id', $rule->google_sheet_config_id ?? '') == $sheet->id ? 'selected':'' }}>
                            {{ $sheet->sheet_name ?: ('Sheet #' . $sheet->id) }}
                        </option>
                        @endforeach
                    </select>
                    <div class="mt-2" style="font-size:11px;color:#0f9d58;">
                        <i class="fas fa-info-circle me-1"></i>
                        Specific sheet select karo to sirf us sheet ke leads is rule se assign honge.
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════
             2  DISTRIBUTION
        ════════════════════════════════ --}}
        <div class="acard">
            <div class="acard-head">
                <div class="acard-num">2</div>
                <div>
                    <p class="acard-ttl">Distribution Logic</p>
                    <p class="acard-sub">Lead kaise aur kisko assign karna hai?</p>
                </div>
            </div>
            <div class="acard-body">

                {{-- Method cards --}}
                <div class="mth-grid">
                    @foreach($methods as $val => $m)
                    <label class="mth-item">
                        <input type="radio" name="assignment_method" value="{{ $val }}"
                               class="mth-radio" {{ $selMth===$val ? 'checked':'' }}>
                        <div class="mth-card">
                            <div class="mth-ico"><i class="{{ $m['icon'] }}"></i></div>
                            <p class="mth-ttl">{{ $m['title'] }}</p>
                            <p class="mth-dsc">{{ $m['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- ─── PANEL: Single User ─── --}}
                <div id="panelSingle" class="users-panel" style="{{ !$isSingleLoad ? 'display:none;' : '' }}">
                    <div class="flabel">User select karo</div>
                    <select name="single_user_id" class="form-select">
                        <option value="">-- Select User --</option>
                        @foreach($salesUsers as $u)
                        <option value="{{ $u->id }}"
                            {{ old('single_user_id', $rule->single_user_id ?? '') == $u->id ? 'selected':'' }}>
                            {{ $u->name }} — {{ $u->role->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- ─── PANEL: Multi Users (Round Robin / First Available / Percentage) ─── --}}
                <div id="panelMulti" class="users-panel" style="{{ $isSingleLoad ? 'display:none;' : '' }}">
                    <div class="panel-head">
                        <span class="panel-label">Users configure karo</span>
                        <button type="button" id="addUserBtn"
                                class="btn btn-sm btn-primary px-3" style="font-size:12px;">
                            <i class="fas fa-plus me-1"></i>User Add Karo
                        </button>
                    </div>

                    <div class="tbl-wrap">
                        <table class="usr-tbl">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th id="pctHead" style="{{ !$isPctLoad ? 'display:none;' : '' }}width:160px;">
                                        Percentage
                                    </th>
                                    <th style="width:46px;"></th>
                                </tr>
                            </thead>
                            <tbody id="usersBody">
                                @foreach($existUsers as $i => $eu)
                                <tr class="usr-row">
                                    <td>
                                        <select name="users[{{ $i }}][user_id]" class="form-select form-select-sm">
                                            <option value="">-- Select User --</option>
                                            @foreach($salesUsers as $u)
                                            <option value="{{ $u->id }}"
                                                {{ ($eu['user_id']??'')==$u->id?'selected':'' }}>
                                                {{ $u->name }} — {{ $u->role->name ?? '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="pct-col" style="{{ !$isPctLoad ? 'display:none;' : '' }}">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="users[{{ $i }}][percentage]"
                                                   class="form-control pct-inp" placeholder="0"
                                                   min="0" max="100" step="0.1"
                                                   value="{{ $eu['percentage'] ?? '' }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn-remove btn btn-sm text-danger p-1"
                                                style="line-height:1;">
                                            <i class="fas fa-trash-alt" style="font-size:12px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pct progress bar --}}
                    <div id="pctWrap" style="{{ !$isPctLoad ? 'display:none;' : '' }}margin-top:12px;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="progress flex-grow-1" style="height:7px;border-radius:6px;">
                                <div id="pctBar" class="progress-bar bg-warning"
                                     style="width:0%;transition:width .3s;border-radius:6px;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:700;min-width:80px;">
                                Total: <span id="pctVal">0</span>%
                            </span>
                        </div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:4px;">
                            100% hona chahiye — abhi <span id="pctRemain">100</span>% baaki hai
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ════════════════════════════════
             3  TASK & SETTINGS
        ════════════════════════════════ --}}
        <div class="acard">
            <div class="acard-head">
                <div class="acard-num">3</div>
                <div>
                    <p class="acard-ttl">Task & Settings</p>
                    <p class="acard-sub">Rule ka naam, limits aur task configuration</p>
                </div>
            </div>
            <div class="acard-body">

                {{-- Fields row --}}
                <div class="row g-3 mb-4">

                    <div class="col-12 col-lg-6">
                        <div class="field-box h-100">
                            <div class="field-box-label">
                                Rule Name <span class="text-danger">*</span>
                            </div>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Facebook Form A → Round Robin"
                                   value="{{ old('name', $rule->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="field-box-hint">Is rule ko ek clear naam do taki identify kar sako</div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="field-box h-100">
                            <div class="field-box-label">Daily Limit</div>
                            <div class="input-group">
                                <input type="number" name="daily_limit" class="form-control"
                                       placeholder="∞ Unlimited" min="1"
                                       value="{{ old('daily_limit', $rule->daily_limit ?? '') }}">
                                <span class="input-group-text small">/day</span>
                            </div>
                            <div class="field-box-hint">Is rule se aaj max kitne leads assign honge</div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="field-box h-100">
                            <div class="field-box-label">Fallback User</div>
                            <select name="fallback_user_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($salesUsers as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('fallback_user_id', $rule->fallback_user_id ?? '')==$u->id?'selected':'' }}>
                                    {{ $u->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="field-box-hint">Jab koi user available na ho to yahan send karo</div>
                        </div>
                    </div>

                </div>

                {{-- Toggles --}}
                <label class="tog">
                    <div style="padding-top:2px;">
                        <input class="form-check-input" type="checkbox" name="auto_create_task" value="1"
                               style="width:42px;height:22px;cursor:pointer;"
                               {{ old('auto_create_task', $rule->auto_create_task ?? true) ? 'checked':'' }}>
                    </div>
                    <div>
                        <p class="tog-ttl">
                            <i class="fas fa-tasks me-1" style="color:#198754;"></i>
                            Auto-create Calling Task
                        </p>
                        <p class="tog-dsc">
                            Lead assign hone ke baad telecaller dashboard pe calling task automatically create hoga
                        </p>
                    </div>
                </label>

                <label class="tog">
                    <div style="padding-top:2px;">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               style="width:42px;height:22px;cursor:pointer;"
                               {{ old('is_active', $rule->is_active ?? true) ? 'checked':'' }}>
                    </div>
                    <div>
                        <p class="tog-ttl">
                            <i class="fas fa-circle me-1" style="color:#0d6efd;"></i>
                            Rule Active
                        </p>
                        <p class="tog-dsc">
                            Band karo to is source ke leads unassigned rahenge — koi task bhi nahi banega
                        </p>
                    </div>
                </label>

                <div class="submit-row">
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="fas fa-save me-2"></i>
                        {{ isset($rule) ? 'Update Rule' : 'Create Rule' }}
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
/* ── Source colors ── */
const SC = {facebook_lead_ads:'#1877f2',pabbly:'#ff6600',mcube:'#6f42c1',google_sheets:'#0f9d58',csv:'#0077b6',all:'#28a745'};
const SB = {facebook_lead_ads:'#e7f0fd',pabbly:'#fff0e6',mcube:'#f0ebfd',google_sheets:'#e6f7ee',csv:'#e6f2fb',all:'#eafaf1'};

document.querySelectorAll('.src-radio').forEach(r=>{
    r.addEventListener('change',function(){
        document.querySelectorAll('.src-card').forEach(c=>{c.style.borderColor='#e9ecef';c.style.background='#fff';});
        const card = this.closest('.src-item').querySelector('.src-card');
        card.style.borderColor = SC[this.value]||'#198754';
        card.style.background  = SB[this.value]||'#f0fdf4';
        document.getElementById('fbPanel').style.display = this.value==='facebook_lead_ads' ? 'block' : 'none';
        document.getElementById('gsPanel').style.display = this.value==='google_sheets'      ? 'block' : 'none';
    });
});

/* ── Method switch ── */
function showPanel(method){
    const isSingle = method === 'single_user';
    const isPct    = method === 'percentage';

    // Show correct panel
    document.getElementById('panelSingle').style.display = isSingle ? 'block' : 'none';
    document.getElementById('panelMulti').style.display  = isSingle ? 'none'  : 'block';

    // Percentage column
    document.getElementById('pctHead').style.display = isPct ? '' : 'none';
    document.querySelectorAll('.pct-col').forEach(td=>{ td.style.display = isPct ? '' : 'none'; });
    document.getElementById('pctWrap').style.display = isPct ? 'block' : 'none';

    updatePct();
}

document.querySelectorAll('.mth-radio').forEach(r=>{
    r.addEventListener('change',function(){ showPanel(this.value); });
});

/* ── Add user row ── */
const OPTS = `<option value="">-- Select User --</option>` +
    @json($salesUsers->map(fn($u)=>['id'=>$u->id,'n'=>$u->name.' — '.($u->role->name??'')]))
    .map(u=>`<option value="${u.id}">${u.n}</option>`).join('');

let RI = {{ count($existUsers) }};
const isPct = ()=> document.querySelector('.mth-radio:checked')?.value === 'percentage';

document.getElementById('addUserBtn').addEventListener('click',()=>{
    const pctHide = isPct() ? '' : 'none';
    const tr = document.createElement('tr');
    tr.className='usr-row';
    tr.innerHTML=`
        <td><select name="users[${RI}][user_id]" class="form-select form-select-sm">${OPTS}</select></td>
        <td class="pct-col" style="display:${pctHide}">
            <div class="input-group input-group-sm">
                <input type="number" name="users[${RI}][percentage]" class="form-control pct-inp" placeholder="0" min="0" max="100" step="0.1">
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn-remove btn btn-sm text-danger p-1" style="line-height:1;">
                <i class="fas fa-trash-alt" style="font-size:12px;"></i>
            </button>
        </td>`;
    document.getElementById('usersBody').appendChild(tr);
    tr.querySelector('.btn-remove').addEventListener('click',()=>{tr.remove();updatePct();});
    RI++;
});

document.querySelectorAll('.btn-remove').forEach(b=>{
    b.addEventListener('click',()=>{b.closest('tr').remove();updatePct();});
});

/* ── Pct bar ── */
function updatePct(){
    let sum=0;
    document.querySelectorAll('.pct-inp').forEach(i=>{sum+=parseFloat(i.value)||0;});
    sum=Math.round(sum*10)/10;
    const ok = Math.abs(sum-100)<0.1;
    const ov = sum>100;
    document.getElementById('pctVal').textContent=sum;
    document.getElementById('pctVal').style.color = ok?'#198754':ov?'#dc3545':'#f59e0b';
    document.getElementById('pctRemain').textContent = Math.max(0,Math.round((100-sum)*10)/10);
    const bar=document.getElementById('pctBar');
    bar.style.width=Math.min(sum,100)+'%';
    bar.className='progress-bar '+(ok?'bg-success':ov?'bg-danger':'bg-warning');
}
document.getElementById('usersBody').addEventListener('input',updatePct);
updatePct();
</script>
@endpush
