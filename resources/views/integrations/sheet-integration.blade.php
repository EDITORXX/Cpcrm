@extends('layouts.app')
@section('title', 'Sheet Integration')

@push('styles')
<style>
.si-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    max-width: 900px;
    margin: 0 auto;
}
@media(max-width: 640px) { .si-grid { grid-template-columns: 1fr; } }

.si-card {
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 28px 24px;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
    transition: all .2s;
    position: relative;
    overflow: hidden;
}
.si-card:hover {
    border-color: var(--card-color);
    box-shadow: 0 8px 24px rgba(0,0,0,.1);
    transform: translateY(-3px);
    color: inherit;
    text-decoration: none;
}
.si-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--card-color);
}
.si-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 16px;
    background: var(--card-bg);
}
.si-title {
    font-size: 17px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 6px;
}
.si-desc {
    font-size: 13px;
    color: #6c757d;
    margin: 0 0 16px;
    line-height: 1.6;
}
.si-features {
    list-style: none;
    padding: 0;
    margin: 0 0 20px;
}
.si-features li {
    font-size: 12px;
    color: #495057;
    padding: 3px 0;
    display: flex;
    align-items: center;
    gap: 7px;
}
.si-features li::before {
    content: '✓';
    font-weight: 700;
    color: var(--card-color);
    flex-shrink: 0;
}
.si-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    background: var(--card-color);
    color: #fff;
    border: none;
    transition: opacity .15s;
}
.si-card:hover .si-btn { opacity: .9; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 px-4">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('integrations.index') }}"
           class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center"
           style="width:36px;height:36px;flex-shrink:0;">
            <i class="fas fa-arrow-left" style="font-size:12px;"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-table me-2 text-success"></i>Sheet Integration
            </h5>
            <div class="text-muted small">Google Sheets se leads import karne ka method chuno</div>
        </div>
    </div>

    {{-- 4 Option Cards --}}
    <div class="si-grid">

        {{-- 1. Lead Import --}}
        <a href="{{ route('lead-import.index') }}" class="si-card"
           style="--card-color:#0f9d58;--card-bg:#e6f7ee;">
            <div class="si-icon">
                <i class="fas fa-cloud-download-alt" style="color:#0f9d58;"></i>
            </div>
            <p class="si-title">Lead Import</p>
            <p class="si-desc">
                Google Sheet URL do, columns map karo aur leads automatically import hote rahenge CRM mein.
            </p>
            <ul class="si-features">
                <li>Sheet URL se direct sync</li>
                <li>Column mapping (name, phone, email, etc.)</li>
                <li>Auto-sync every few minutes</li>
                <li>Import history dekho</li>
            </ul>
            <span class="si-btn">
                <i class="fas fa-arrow-right"></i> Open
            </span>
        </a>

        {{-- 2. Smart Import --}}
        <a href="{{ route('smart-import.index') }}" class="si-card"
           style="--card-color:#6f42c1;--card-bg:#f0ebfd;">
            <div class="si-icon">
                <i class="fas fa-magic" style="color:#6f42c1;"></i>
            </div>
            <p class="si-title">Smart Import</p>
            <p class="si-desc">
                Advanced import — sheet ya CSV upload karo, distribution rules set karo aur bulk leads assign karo automatically.
            </p>
            <ul class="si-features">
                <li>CSV + Google Sheet dono support</li>
                <li>Auto-distribution rules</li>
                <li>Batch processing with analytics</li>
                <li>Import history & SLA tracking</li>
            </ul>
            <span class="si-btn">
                <i class="fas fa-arrow-right"></i> Open
            </span>
        </a>

        {{-- 3. Meta Sheet --}}
        <a href="{{ route('integrations.meta-sheet.index') }}" class="si-card"
           style="--card-color:#1877f2;--card-bg:#e7f0fd;">
            <div class="si-icon">
                <i class="fab fa-facebook" style="color:#1877f2;"></i>
            </div>
            <p class="si-title">Meta Sheet</p>
            <p class="si-desc">
                Facebook/Meta Lead Ads ke responses ek Google Sheet mein collect karo, phir wahan se CRM mein import karo.
            </p>
            <ul class="si-features">
                <li>Facebook Lead Ads connect karo</li>
                <li>Google Sheet as middle storage</li>
                <li>Auto-sync from sheet to CRM</li>
                <li>Column mapping support</li>
            </ul>
            <span class="si-btn">
                <i class="fas fa-arrow-right"></i> Open
            </span>
        </a>

        {{-- 4. Form Integration --}}
        <a href="{{ route('integrations.form-integration.index') }}" class="si-card"
           style="--card-color:#fd7e14;--card-bg:#fff3e0;">
            <div class="si-icon">
                <i class="fab fa-wpforms" style="color:#fd7e14;"></i>
            </div>
            <p class="si-title">Form Integration</p>
            <p class="si-desc">
                Google Form ya koi bhi website form ko CRM se connect karo — form submit hote hi lead directly CRM mein aaye.
            </p>
            <ul class="si-features">
                <li>Google Forms direct connect</li>
                <li>Custom website form webhook</li>
                <li>Real-time lead capture</li>
                <li>Script generate karo (copy-paste)</li>
            </ul>
            <span class="si-btn">
                <i class="fas fa-arrow-right"></i> Open
            </span>
        </a>

    </div>

</div>
@endsection
