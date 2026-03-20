@extends('layouts.app')

@section('title', 'Lead Automation Rules')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-magic me-2 text-primary"></i>Lead Automation</h4>
            <small class="text-muted">Kisi bhi source se lead aaye to auto-assign aur task create ho</small>
        </div>
        <a href="{{ route('admin.automation.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Rule
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($rules->isEmpty())
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-magic fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Koi automation rule nahi hai</h5>
                <p class="text-muted mb-3">Facebook, Pabbly, MCube ya kisi bhi source ke leads ke liye auto-assignment rule banao.</p>
                <a href="{{ route('admin.automation.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Pehla Rule Banao
                </a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($rules as $rule)
            <div class="col-12">
                <div class="card shadow-sm border-0 {{ !$rule->is_active ? 'opacity-75' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                            {{-- Left: Rule info --}}
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                {{-- Source icon --}}
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:48px;height:48px;background:{{ $rule->source === 'facebook_lead_ads' ? '#1877f2' : ($rule->source === 'pabbly' ? '#ff6600' : ($rule->source === 'mcube' ? '#6f42c1' : '#28a745')) }}20;">
                                    @if($rule->source === 'facebook_lead_ads')
                                        <i class="fab fa-facebook" style="color:#1877f2;font-size:1.3rem;"></i>
                                    @elseif($rule->source === 'pabbly')
                                        <i class="fas fa-bolt" style="color:#ff6600;font-size:1.3rem;"></i>
                                    @elseif($rule->source === 'mcube')
                                        <i class="fas fa-phone" style="color:#6f42c1;font-size:1.3rem;"></i>
                                    @elseif($rule->source === 'all')
                                        <i class="fas fa-globe" style="color:#28a745;font-size:1.3rem;"></i>
                                    @else
                                        <i class="fas fa-table" style="color:#17a2b8;font-size:1.3rem;"></i>
                                    @endif
                                </div>

                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <strong>{{ $rule->name }}</strong>
                                        @if(!$rule->is_active)
                                            <span class="badge bg-secondary">Inactive</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <span class="me-3"><i class="fas fa-layer-group me-1"></i>{{ \App\Models\SourceAutomationRule::getSourceLabel($rule->source) }}</span>
                                        @if($rule->fbForm)
                                            <span class="me-3"><i class="fas fa-file-alt me-1"></i>Form: {{ $rule->fbForm->form_name ?? $rule->fbForm->form_id }}</span>
                                        @endif
                                        <span class="me-3"><i class="fas fa-random me-1"></i>{{ \App\Models\SourceAutomationRule::getMethodLabel($rule->assignment_method) }}</span>
                                        @if($rule->auto_create_task)
                                            <span class="me-3 text-success"><i class="fas fa-tasks me-1"></i>Task: ON</span>
                                        @else
                                            <span class="me-3 text-muted"><i class="fas fa-tasks me-1"></i>Task: OFF</span>
                                        @endif
                                        @if($rule->daily_limit)
                                            <span class="me-3"><i class="fas fa-tachometer-alt me-1"></i>Limit: {{ $rule->daily_limit }}/day</span>
                                        @endif
                                    </div>

                                    {{-- Users --}}
                                    @if($rule->assignment_method === 'single_user' && $rule->singleUser)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-user me-1"></i>{{ $rule->singleUser->name }}
                                            </span>
                                        </div>
                                    @elseif($rule->users->isNotEmpty())
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            @foreach($rule->users->take(5) as $ru)
                                                <span class="badge bg-light text-dark border">
                                                    {{ $ru->user->name ?? 'Unknown' }}
                                                    @if($ru->percentage) ({{ $ru->percentage }}%) @endif
                                                </span>
                                            @endforeach
                                            @if($rule->users->count() > 5)
                                                <span class="badge bg-secondary">+{{ $rule->users->count() - 5 }} more</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Right: Actions --}}
                            <div class="d-flex gap-2">
                                {{-- Toggle active --}}
                                <button class="btn btn-sm {{ $rule->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} btn-toggle-rule"
                                        data-id="{{ $rule->id }}" data-active="{{ $rule->is_active ? 1 : 0 }}"
                                        title="{{ $rule->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas {{ $rule->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>

                                <a href="{{ route('admin.automation.edit', $rule) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button class="btn btn-sm btn-outline-danger btn-delete-rule"
                                        data-id="{{ $rule->id }}" data-name="{{ $rule->name }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Delete form (hidden) --}}
<form id="deleteForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
// Toggle active
document.querySelectorAll('.btn-toggle-rule').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`/admin/automation/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(() => location.reload())
        .catch(e => alert('Error: ' + e));
    });
});

// Delete
document.querySelectorAll('.btn-delete-rule').forEach(btn => {
    btn.addEventListener('click', function() {
        const name = this.dataset.name;
        if (!confirm(`"${name}" ko delete karo?`)) return;
        const form = document.getElementById('deleteForm');
        form.action = `/admin/automation/${this.dataset.id}`;
        form.submit();
    });
});
</script>
@endpush
