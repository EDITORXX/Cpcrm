@extends('layouts.app')

@section('title', 'Automation - Base CRM')
@section('page-title', 'Lead Automation')
@section('page-subtitle', 'Automatically import and distribute leads from Google Sheets or CSV/Excel files')

@section('header-actions')
    <a href="{{ route('crm.automation.create-simple') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        + Create Automation
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Lead Automation Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Lead Automation</h2>
                <p class="text-sm text-gray-500 mt-1">Automatically import and distribute leads from Google Sheets or CSV/Excel files</p>
            </div>
            <a href="{{ route('crm.automation.create-simple') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                + Create Automation
            </a>
        </div>

        @php
            $automations = \App\Models\SmartImportAutomation::where('created_by', auth()->id())
                ->latest()
                ->take(6)
                ->get();
        @endphp

        @if($automations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($automations as $automation)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-semibold text-gray-800">{{ $automation->name }}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $automation->status === 'active' ? 'bg-green-100 text-green-800' : 
                                   ($automation->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($automation->status === 'paused' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800')) }}">
                                {{ ucfirst($automation->status) }}
                            </span>
                        </div>
                        
                        <div class="text-sm text-gray-600 mb-3">
                            <p class="mb-1">
                                <strong>Source:</strong> 
                                <span class="capitalize">{{ str_replace('_', ' ', $automation->lead_source ?? 'N/A') }}</span>
                            </p>
                            <p class="mb-1">
                                <strong>Distribution:</strong> 
                                <span class="capitalize">{{ str_replace('_', ' ', $automation->distribution_mode ?? 'N/A') }}</span>
                            </p>
                            @if($automation->execution_count > 0)
                                <p class="mb-1">
                                    <strong>Executions:</strong> {{ $automation->execution_count }}
                                </p>
                            @endif
                            @if($automation->last_executed_at)
                                <p class="text-xs text-gray-500">
                                    Last run: {{ $automation->last_executed_at->diffForHumans() }}
                                </p>
                            @endif
                        </div>

                        @if($automation->description)
                            <p class="text-xs text-gray-500 mt-2 mb-3">{{ Str::limit($automation->description, 60) }}</p>
                        @endif

                        <div class="flex gap-2 mt-4">
                            <a href="{{ route('smart-import.edit', $automation->id) }}" 
                               class="flex-1 text-center px-3 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded hover:bg-indigo-50 transition-colors">
                                Edit
                            </a>
                            @if($automation->status === 'draft' || $automation->status === 'active')
                                <form action="{{ route('smart-import.execute', $automation->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-3 py-1.5 text-xs font-medium text-green-600 hover:text-green-800 border border-green-200 rounded hover:bg-green-50 transition-colors">
                                        Run
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('smart-import.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                    View All Automations →
                </a>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <h3 class="text-lg font-medium text-gray-700 mb-2">No automations created yet</h3>
                <p class="text-gray-500 mb-4">Create your first automation to automatically import and distribute leads</p>
                <a href="{{ route('crm.automation.create-simple') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                    Create Automation
                </a>
            </div>
        @endif
    </div>

    <!-- Quick Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mt-8">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-800 mb-1">About Lead Automation</h3>
                <p class="text-sm text-blue-700">
                    Lead Automation automatically imports leads from Google Sheets or CSV/Excel files and distributes them to your team based on your configured distribution rules. 
                    You can set up percentage-based, random, or one-sheet-per-user distribution. After assignment, phone call tasks are automatically created for follow-up.
                </p>
            </div>
        </div>
    </div>
@endsection

