@extends('layouts.app')

@section('title', 'Analytics - Base CRM')
@section('page-title', 'Smart Import Analytics')
@section('page-subtitle', 'Performance metrics and insights')

@section('header-actions')
    <form method="GET" action="{{ route('smart-import.analytics') }}" class="flex items-center space-x-2">
        <select name="date_range" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-900" onchange="this.form.submit()">
            <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 days</option>
            <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 days</option>
            <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last year</option>
        </select>
    </form>
@endsection

@section('content')
    <!-- Overall Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-600">Total Executions</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_executions']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-600">Leads Imported</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($stats['total_leads_imported']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-600">Queued Leads</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($stats['total_leads_queued']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-600">Failed Leads</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($stats['total_leads_failed']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-600">Success Rate</p>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['success_rate'] }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Leads per Agent -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Leads per Agent</h3>
            <div class="space-y-3">
                @forelse($leadsPerAgent as $item)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ $item['user_name'] }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-[#063A1C] to-[#205A44] h-2 rounded-full" style="width: {{ min(100, ($item['total'] / max(1, $leadsPerAgent->max('total'))) * 100) }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-12 text-right">{{ $item['total'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No data available</p>
                @endforelse
            </div>
        </div>

        <!-- Conversion by Source -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Leads by Source</h3>
            <div class="space-y-3">
                @forelse($conversionBySource as $item)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ $item->source ?: 'Unknown' }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(100, ($item->total / max(1, $conversionBySource->max('total'))) * 100) }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-12 text-right">{{ $item->total }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SLA Compliance -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">SLA Compliance</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">Total SLAs</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $slaMetrics['total'] }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Met</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $slaMetrics['met'] }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Breached</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $slaMetrics['breached'] }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600">Compliance Rate</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $slaMetrics['compliance_rate'] }}%</p>
            </div>
        </div>
    </div>

    <!-- Daily Trends -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Import Trends</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Executions</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leads Imported</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Failed</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dailyTrends as $trend)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($trend->date)->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $trend->executions }}</td>
                            <td class="px-4 py-3 text-sm text-green-600 font-semibold">{{ $trend->leads_imported }}</td>
                            <td class="px-4 py-3 text-sm text-red-600">{{ $trend->leads_failed }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Overloaded Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                Overloaded Agents
            </h3>
            <div class="space-y-2">
                @forelse($overloadedUsers as $user)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-900">{{ $user['user_name'] }}</span>
                        <span class="text-sm font-semibold text-red-600">{{ $user['assigned_count'] }} leads</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No overloaded agents</p>
                @endforelse
            </div>
        </div>

        <!-- Idle Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                Idle Agents
            </h3>
            <div class="space-y-2">
                @forelse($idleUsers as $user)
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-900">{{ $user['user_name'] }}</span>
                        <span class="text-sm font-semibold text-yellow-600">{{ $user['assigned_count'] }} leads</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No idle agents</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Performing Automations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performing Automations</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Automation</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Executions</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Leads</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg per Execution</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topAutomations as $automation)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $automation->automation->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $automation->execution_count }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($automation->total_leads) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $automation->execution_count > 0 ? number_format($automation->total_leads / $automation->execution_count, 1) : 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

