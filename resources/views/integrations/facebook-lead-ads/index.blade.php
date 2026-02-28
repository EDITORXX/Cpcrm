@extends('layouts.app')

@section('title', 'Facebook Lead Ads - Base CRM')
@section('page-title', 'Facebook Lead Ads')

@section('header-actions')
    <a href="{{ route('integrations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back to Integrations
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if(session('warning'))
        <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
            <i class="fab fa-facebook text-blue-600 text-2xl mr-3"></i>
            Facebook Lead Ads (Standalone)
        </h2>
        <p class="text-gray-600 text-sm mb-4">Direct webhook + Graph API. Leads sync to this section only; CRM leads table is not used until you enable integration later.</p>

        <div class="flex flex-wrap gap-4 mb-6">
            <a href="{{ route('integrations.facebook-lead-ads.settings') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                <i class="fas fa-cog mr-2"></i> Settings
            </a>
            @if($hasToken)
            <a href="{{ route('integrations.facebook-lead-ads.forms') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-list mr-2"></i> Select Form
            </a>
            @endif
        </div>

        <div class="border-t border-gray-100 pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL (for Meta App)</label>
            <div class="flex items-center gap-2">
                <input type="text" value="{{ $webhookUrl }}" readonly class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-700">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $webhookUrl }}'); alert('Copied');" class="px-3 py-2 bg-gray-200 rounded-lg text-sm">Copy</button>
            </div>
        </div>
    </div>

    @if($forms->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configured forms</h3>
        <ul class="space-y-2">
            @foreach($forms as $form)
            <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <span class="font-medium text-gray-800">{{ $form->form_name ?: $form->form_id }}</span>
                <span class="text-xs {{ $form->is_enabled ? 'text-green-600' : 'text-gray-500' }}">{{ $form->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                <a href="{{ route('integrations.facebook-lead-ads.mapping', ['formId' => $form->form_id, 'form_name' => $form->form_name, 'page_id' => $settings->page_id]) }}" class="text-sm text-blue-600 hover:underline">Edit mapping</a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
