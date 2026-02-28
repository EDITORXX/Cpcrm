@extends('layouts.app')

@section('title', 'Facebook Lead Ads Settings - Base CRM')
@section('page-title', 'Facebook Lead Ads – Settings')

@section('header-actions')
    <a href="{{ route('integrations.facebook-lead-ads.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div id="message-container" class="mb-4" style="display: none;">
        <div id="message-alert" class="p-4 rounded-lg"></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Connection settings</h2>

        <form id="settings-form">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Page Access Token</label>
                <textarea name="page_access_token" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Paste long-lived Page Access Token">{{ old('page_access_token', $settings->page_access_token) }}</textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Graph API version</label>
                <input type="text" name="graph_version" value="{{ old('graph_version', $settings->graph_version ?? 'v18.0') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="v18.0">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Page ID (optional – set after Test Connection)</label>
                <input type="text" name="page_id" value="{{ old('page_id', $settings->page_id) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Page ID">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Page name (optional)</label>
                <input type="text" name="page_name" value="{{ old('page_name', $pageName ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook verify token (for Meta subscription)</label>
                <input type="text" name="webhook_verify_token" value="{{ old('webhook_verify_token', $settings->webhook_verify_token) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Any string you choose">
            </div>
            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="signature_verification_enabled" value="1" {{ $settings->signature_verification_enabled ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Verify webhook signature (X-Hub-Signature-256)</span>
                </label>
            </div>
            <div class="mb-4" id="app-secret-wrap" style="{{ $settings->signature_verification_enabled ? '' : 'display:none' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1">App secret</label>
                <input type="password" name="app_secret" value="{{ old('app_secret', $settings->app_secret) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="App Secret">
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" id="btn-test" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Test connection</button>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg text-sm font-medium">Save settings</button>
            </div>
        </form>
    </div>

    <div id="test-result" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6" style="display: none;">
        <h3 class="font-semibold text-gray-900 mb-2">Pages (select one and save settings with that Page ID)</h3>
        <ul id="pages-list" class="space-y-2"></ul>
    </div>
</div>

@php $fbPage = $settings->page_id ? \App\Models\FbPage::where('page_id', $settings->page_id)->first() : null; @endphp
<script>
document.querySelector('input[name="signature_verification_enabled"]').addEventListener('change', function() {
    document.getElementById('app-secret-wrap').style.display = this.checked ? 'block' : 'none';
});
document.getElementById('btn-test').addEventListener('click', function() {
    var token = document.querySelector('textarea[name="page_access_token"]').value.trim();
    var graphVersion = document.querySelector('input[name="graph_version"]').value.trim() || 'v18.0';
    if (!token) { alert('Enter Page Access Token first'); return; }
    this.disabled = true;
    fetch('{{ route("integrations.facebook-lead-ads.test-connection") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify({ page_access_token: token, graph_version: graphVersion })
    }).then(r => r.json()).then(function(data) {
        document.getElementById('btn-test').disabled = false;
        var container = document.getElementById('message-container');
        var alert = document.getElementById('message-alert');
        var resultDiv = document.getElementById('test-result');
        var list = document.getElementById('pages-list');
        if (data.success) {
            alert.className = 'p-4 rounded-lg bg-green-50 border border-green-200 text-green-800';
            alert.textContent = 'Connection successful. Pages listed below.';
            container.style.display = 'block';
            resultDiv.style.display = 'block';
            list.innerHTML = (data.pages || []).map(function(p) {
                return '<li class="flex items-center justify-between py-2 border-b border-gray-100"><span>' + (p.name || p.id) + '</span><span class="text-xs text-gray-500">' + p.id + '</span><button type="button" class="text-sm text-blue-600 hover:underline" onclick="document.querySelector(\'input[name=page_id]\').value=\'' + p.id + '\'; document.querySelector(\'input[name=page_name]\').value=\'' + (p.name || '').replace(/'/g, "\\'") + '\';">Use this page</button></li>';
            }).join('') || '<li class="text-gray-500">No pages returned</li>';
        } else {
            alert.className = 'p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
            alert.textContent = data.error || 'Connection failed';
            container.style.display = 'block';
            resultDiv.style.display = 'none';
        }
    }).catch(function() {
        document.getElementById('btn-test').disabled = false;
        document.getElementById('message-alert').className = 'p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
        document.getElementById('message-alert').textContent = 'Request failed';
        document.getElementById('message-container').style.display = 'block';
    });
});
document.getElementById('settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    fetch('{{ route("integrations.facebook-lead-ads.settings.update") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: fd
    }).then(r => r.json()).then(function(data) {
        if (data.success) {
            alert('Settings saved.');
            window.location.href = '{{ route("integrations.facebook-lead-ads.index") }}';
        } else {
            alert(data.message || 'Save failed');
        }
    }).catch(function() { alert('Save failed'); });
});
</script>
@endsection
