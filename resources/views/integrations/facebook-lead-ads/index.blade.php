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
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configured forms</h3>
        <ul class="space-y-2">
            @foreach($forms as $form)
            <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <span class="font-medium text-gray-800">{{ $form->form_name ?: $form->form_id }}</span>
                <span class="text-xs text-gray-500 mr-2">{{ $form->page?->page_name ?? 'Unknown page' }}</span>
                <span class="text-xs {{ $form->is_enabled ? 'text-green-600' : 'text-gray-500' }}">{{ $form->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                <a href="{{ route('integrations.facebook-lead-ads.mapping', ['formId' => $form->form_id, 'form_name' => $form->form_name, 'page_id' => $form->page?->page_id]) }}" class="text-sm text-blue-600 hover:underline">Edit mapping</a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-users text-blue-600 mr-2"></i>
            Recent leads from Meta (last 20)
        </h3>
        @if(isset($recentLeads) && $recentLeads->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-600 font-medium">
                        <th class="py-3 pr-4">Date</th>
                        <th class="py-3 pr-4">Form</th>
                        <th class="py-3 pr-4">Name</th>
                        <th class="py-3 pr-4">Email</th>
                        <th class="py-3 pr-4">Phone</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLeads as $lead)
                    @php
                        $data = $lead->field_data_json ?? [];
                        $name = $data['name'] ?? $data['full_name'] ?? '-';
                        $email = $data['email'] ?? '-';
                        $phone = $data['phone'] ?? $data['phone_number'] ?? '-';
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 pr-4 text-gray-600">{{ $lead->created_at->format('d M Y, H:i') }}</td>
                        <td class="py-3 pr-4">{{ $lead->form?->form_name ?: $lead->form_id }}</td>
                        <td class="py-3 pr-4">{{ $name }}</td>
                        <td class="py-3 pr-4">{{ $email }}</td>
                        <td class="py-3 pr-4">{{ $phone }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 py-4">No leads from Meta yet. Leads will appear here once the webhook receives submissions and the queue worker processes them.</p>
        @endif
    </div>
</div>
@endsection
