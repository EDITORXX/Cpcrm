@php
    $user = auth()->user();
    if ($user && !$user->relationLoaded('role')) {
        $user->load('role');
    }
    $ownerTransferUsersJson = ($ownerTransferUsers ?? collect())->map(function ($transferUser) {
        return [
            'id' => $transferUser->id,
            'name' => $transferUser->name,
            'role' => $transferUser->role->name ?? 'User',
        ];
    })->values()->all();
@endphp
@if($user && ($user->isAdmin() || $user->isCrm()))
    @extends('layouts.app')
@elseif($user && $user->isSalesHead() && !$user->isAdmin() && !$user->isCrm())
    @extends('sales-head.layout')
@elseif($user && $user->isSalesManager())
    @extends('sales-manager.layout')
@elseif($user && $user->isTelecaller())
    @extends('telecaller.layout')
@else
    @extends('layouts.app')
@endif

@section('title', 'Leads - Base CRM')
@section('page-title', 'Leads')

@push('styles')
<style>
@media (max-width: 767px) {
    .header h1 { font-size: 18px !important; font-weight: 600; }
    .leads-filter-form { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; align-items: end; }
    .leads-filter-form > div { min-width: 0; }
    .leads-filter-form label { font-size: 11px; margin-bottom: 4px; }
    .leads-filter-form input, .leads-filter-form select { font-size: 12px; padding: 6px 8px; }
    .leads-filter-form .leads-filter-btn-wrap { display: flex; flex-direction: column; gap: 4px; }
}
</style>
@endpush

@section('header-actions')
    <a href="{{ route('leads.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        Add Lead
    </a>
    @if($user && ($user->isAdmin() || $user->isCrm()))
    @php
        $currentView = $view ?? request('view', 'cards');
        $viewQuery = request()->except(['view', 'per_page']);
    @endphp
    <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden bg-white shadow-sm">
        <a href="{{ route('leads.index', array_merge($viewQuery, ['view' => 'cards'])) }}" class="px-4 py-2 text-sm font-medium {{ $currentView !== 'list' ? 'bg-[#063A1C] text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-th-large mr-1.5"></i> Cards
        </a>
        <a href="{{ route('leads.index', array_merge($viewQuery, ['view' => 'list', 'per_page' => 500])) }}" class="px-4 py-2 text-sm font-medium {{ $currentView === 'list' ? 'bg-[#063A1C] text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-list mr-1.5"></i> List
        </a>
    </div>
    @endif
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg">
            <strong>ℹ️ Info:</strong> {{ session('info') }}
        </div>
    @endif

    <!-- Search and Filter (mobile: one row 25% x 4) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('leads.index') }}" class="leads-filter-form flex gap-4 items-end flex-wrap">
            @if($user && ($user->isAdmin() || $user->isCrm()) && request('view') === 'list')
            <input type="hidden" name="view" value="list">
            <input type="hidden" name="per_page" value="500">
            @endif
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="Search..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
            </div>
            <div class="w-48">
                <label for="lead_type_filter" class="block text-sm font-medium text-gray-700 mb-2">Filter by Type</label>
                <select name="lead_type_filter" 
                        id="lead_type_filter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
                    <option value="">All</option>
                    <option value="prospect" {{ request('lead_type_filter') == 'prospect' ? 'selected' : '' }}>Prospect</option>
                    <option value="visit" {{ request('lead_type_filter') == 'visit' ? 'selected' : '' }}>Visit</option>
                    <option value="revisit" {{ request('lead_type_filter') == 'revisit' ? 'selected' : '' }}>Revisit</option>
                    <option value="meeting" {{ request('lead_type_filter') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                    <option value="closer" {{ request('lead_type_filter') == 'closer' ? 'selected' : '' }}>Closer</option>
                </select>
            </div>
            <div class="w-48">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Filter by User</label>
                <select name="user_id" 
                        id="user_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
                    <option value="">All Users</option>
                    @foreach($filterUsers ?? [] as $filterUser)
                        <option value="{{ $filterUser->id }}" {{ request('user_id') == $filterUser->id ? 'selected' : '' }}>
                            {{ $filterUser->name }}{{ $filterUser->role ? ' (' . $filterUser->role->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="leads-filter-btn-wrap">
                <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium text-sm">
                    Search
                </button>
                @if(request('search') || request('lead_type_filter') || request('user_id'))
                    <a href="{{ route('leads.index') }}" class="block text-center mt-1 text-xs text-gray-600 hover:text-gray-800">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @if($user && ($user->isAdmin() || $user->isCrm()) && request('user_id'))
        @php
            $transferAllFromUser = ($filterUsers ?? collect())->firstWhere('id', request('user_id'));
        @endphp
        @if($transferAllFromUser)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex flex-wrap items-center gap-4">
            <span class="text-sm font-medium text-amber-800">Transfer all leads from <strong>{{ $transferAllFromUser->name }}</strong> to:</span>
            <select id="transferAllToUserId" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @foreach($ownerTransferUsers ?? [] as $u)
                    @if((int)$u->id !== (int)request('user_id'))
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role->name ?? 'User' }})</option>
                    @endif
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" id="transferAllCreateTask" checked>
                Create calling task
            </label>
            <button type="button" onclick="submitTransferAllFromUser()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium text-sm">
                Transfer all
            </button>
        </div>
        @endif
    @endif

    <!-- Leads Cards -->
    <style>
        .leads-grid {
            display: grid;
            /* Auto-fit cards based on available content width (fixes nav text mode clipping) */
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            max-width: 100%;
            min-width: 0;
        }
        @media (max-width: 640px) {
            .leads-grid {
                grid-template-columns: 1fr;
            }
        }
        .lead-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 280px;
            min-width: 0;
            overflow: hidden;
        }
        .lead-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        .lead-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            min-width: 0;
        }
        .lead-card-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 50%, #166534 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.25rem;
            margin-right: 1rem;
            box-shadow: 0 2px 4px rgba(21, 128, 61, 0.3);
        }
        .lead-card-body {
            flex: 1;
            margin-bottom: 1rem;
            min-width: 0;
            overflow: hidden;
        }
        .lead-card-footer {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            width: 100%;
            min-width: 0;
        }
        body.nav-text .lead-card-footer {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        body.nav-text .lead-card-footer form {
            grid-column: 1 / -1;
        }
        @media (max-width: 1200px) {
            .lead-card-footer {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .lead-card-footer form {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 420px) {
            .lead-card-footer {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .lead-card-footer form {
                grid-column: auto;
            }
        }
        @media (max-width: 767px) {
            .lead-card {
                min-height: auto;
                padding: 1rem;
            }
            .lead-card-footer {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.35rem;
                padding-top: 0.75rem;
            }
            .lead-card-footer form {
                grid-column: auto;
            }
            .lead-card-footer a,
            .lead-card-footer button {
                padding: 6px 4px !important;
                font-size: 11px !important;
                border-radius: 6px !important;
            }
            .lead-card-footer a i,
            .lead-card-footer button i {
                margin-right: 3px !important;
                font-size: 10px !important;
            }
        }
        .lead-card-footer a,
        .lead-card-footer button {
            min-width: 0;
            white-space: nowrap;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        .lead-card-footer a span,
        .lead-card-footer button span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Short Details Modal Styles */
        #leadDetailsModal {
            backdrop-filter: blur(4px);
        }
        #leadDetailsModal .rounded-xl {
            max-height: 90vh;
            overflow-y: auto;
        }
        #leadDetailsModal .fa-star {
            font-size: 1.25rem;
        }
        .bulk-transfer-bar {
            display: none;
        }
        .bulk-transfer-bar.visible {
            display: flex;
        }
    </style>

    @if($user && ($user->isAdmin() || $user->isCrm()) && ($view ?? 'cards') !== 'list')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap items-center gap-4">
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
            <input type="checkbox" id="selectAllLeadsOnPage" onchange="toggleSelectAllLeads(this)" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            Select all on this page
        </label>
        <div id="bulkTransferBar" class="bulk-transfer-bar flex flex-wrap items-center gap-3">
            <span id="bulkTransferCount" class="text-sm text-gray-700">Transfer selected (<span class="font-semibold">0</span>) to</span>
            <select id="bulkTransferToUserId" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                @foreach($ownerTransferUsers ?? [] as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role->name ?? 'User' }})</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" id="bulkTransferCreateTask" checked class="rounded border-gray-300 text-green-600">
                Create calling task
            </label>
            <button type="button" id="bulkTransferBtn" onclick="submitBulkTransfer()" disabled class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg font-medium text-sm hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                Transfer
            </button>
        </div>
    </div>
    @endif
    
    @if($leads->count() > 0)
        @if(($view ?? 'cards') === 'list' && $user && ($user->isAdmin() || $user->isCrm()))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Number</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Owner</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leads as $lead)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('leads.show', $lead->id) }}" class="text-[#063A1C] font-medium hover:underline">{{ $lead->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $lead->phone }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($lead->activeAssignments->count() > 0)
                                    {{ $lead->activeAssignments->first()->assignedTo->name }}
                                @else
                                    <span class="text-gray-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('leads.show', $lead->id) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="leads-grid mb-6">
            @foreach($leads as $lead)
                @php
                    $statusColors = [
                        'new' => 'bg-blue-100 text-blue-800',
                        'contacted' => 'bg-yellow-100 text-yellow-800',
                        'connected' => 'bg-blue-100 text-blue-800',
                        'verified_prospect' => 'bg-purple-100 text-purple-800',
                        'meeting_scheduled' => 'bg-indigo-100 text-indigo-800',
                        'meeting_completed' => 'bg-cyan-100 text-cyan-800',
                        'visit_scheduled' => 'bg-violet-100 text-violet-800',
                        'visit_done' => 'bg-pink-100 text-pink-800',
                        'revisited_scheduled' => 'bg-fuchsia-100 text-fuchsia-800',
                        'revisited_completed' => 'bg-rose-100 text-rose-800',
                        'closed' => 'bg-green-100 text-green-800',
                        'dead' => 'bg-red-100 text-red-800',
                        'on_hold' => 'bg-gray-100 text-gray-800',
                    ];
                    
                    $statusLabels = [
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'connected' => 'Connected',
                        'verified_prospect' => 'Verified Prospect',
                        'meeting_scheduled' => 'Meeting Scheduled',
                        'meeting_completed' => 'Meeting Completed',
                        'visit_scheduled' => 'Visit Scheduled',
                        'visit_done' => 'Visit Done',
                        'revisited_scheduled' => 'Revisit Scheduled',
                        'revisited_completed' => 'Revisit Completed',
                        'closed' => 'Closed',
                        'dead' => 'Dead',
                        'on_hold' => 'On Hold',
                    ];
                    
                    $color = $statusColors[$lead->status] ?? 'bg-gray-100 text-gray-800';
                    $label = $statusLabels[$lead->status] ?? ucfirst(str_replace('_', ' ', $lead->status));
                    $phoneNumber = preg_replace('/[^0-9]/', '', $lead->phone);
                    $whatsappUrl = 'https://wa.me/' . $phoneNumber;
                @endphp
                <div class="lead-card" data-lead-id="{{ $lead->id }}">
                    <div class="lead-card-header">
                        @if($user && ($user->isAdmin() || $user->isCrm()))
                        <label class="flex items-center mr-2 cursor-pointer shrink-0" title="Select for bulk transfer">
                            <input type="checkbox" class="lead-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500" value="{{ $lead->id }}" data-lead-id="{{ $lead->id }}" onchange="updateBulkTransferBar()">
                        </label>
                        @endif
                        <div class="lead-card-avatar">
                            {{ strtoupper(substr($lead->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $lead->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ $lead->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="lead-card-body">
                        <div class="space-y-2">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="truncate">{{ $lead->phone }}</span>
                            </div>
                            
                            @if($lead->email)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate">{{ $lead->email }}</span>
                            </div>
                            @endif
                            
                            @if($lead->city || $lead->state)
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="truncate">{{ $lead->city }}{{ $lead->state ? ', ' . $lead->state : '' }}</span>
                            </div>
                            @endif
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="truncate">
                                    @if($lead->activeAssignments->count() > 0)
                                        {{ $lead->activeAssignments->first()->assignedTo->name }}
                                    @else
                                        <span class="text-gray-400">Unassigned</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="pt-2">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                    {{ $label }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lead-card-footer" style="display: flex !important; visibility: visible !important;">
                        <a href="{{ route('leads.show', $lead->id) }}" 
                           style="display: flex !important; visibility: visible !important;"
                           class="flex-1 flex items-center justify-center px-3 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 font-medium text-xs shadow-md">
                            <i class="fas fa-eye mr-1.5"></i>
                            <span>View Detail</span>
                        </a>
                        <button onclick="viewShortDetails({{ $lead->id }})" 
                                style="display: flex !important; visibility: visible !important;"
                                class="flex-1 flex items-center justify-center px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 font-medium text-xs shadow-md">
                            <i class="fas fa-info-circle mr-1.5"></i>
                            <span>Short Detail</span>
                        </button>
                        @if($user && ($user->isAdmin() || $user->isCrm()))
                        <form method="POST" action="{{ route('leads.destroy', $lead->id) }}" class="flex-1 flex" onsubmit="return confirm('Delete this lead? It will be moved to trash and can be recovered.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="display: flex !important; visibility: visible !important;" class="w-full flex items-center justify-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 font-medium text-xs shadow-md">
                                <i class="fas fa-trash-alt mr-1.5"></i>
                                <span>Delete</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Pagination -->
        @if($leads->hasPages())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3 sm:px-6">
                {{ $leads->withQueryString()->links() }}
            </div>
        @endif
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No leads found</h3>
            <p class="text-gray-500 mb-4">Get started by creating your first lead</p>
            <a href="{{ route('leads.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                Add Lead
            </a>
        </div>
    @endif

    <!-- Lead Details Modal -->
    <div id="leadDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 md:w-2/3 lg:w-1/2 xl:w-2/5 shadow-2xl rounded-xl bg-white overflow-hidden">
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Short Details</h3>
                    <button onclick="closeLeadDetailsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div id="leadDetailsContent" class="p-6">
                <!-- Lead details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        const API_BASE_URL = '{{ url("/api") }}';
        const API_TOKEN = '{{ auth()->check() ? (session("api_token") ?? auth()->user()->createToken("web-token")->plainTextToken) : "" }}';
        const CAN_TRANSFER_OWNER = @json($user && ($user->isAdmin() || $user->isCrm()));
        const OWNER_TRANSFER_USERS = @json($ownerTransferUsersJson ?? []);
        const FILTER_USER_ID = @json(request('user_id'));

        function getSelectedLeadIds() {
            return Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => parseInt(cb.value, 10));
        }

        function updateBulkTransferBar() {
            if (!CAN_TRANSFER_OWNER) return;
            const ids = getSelectedLeadIds();
            const n = ids.length;
            const bar = document.getElementById('bulkTransferBar');
            const countEl = document.querySelector('#bulkTransferCount span');
            const btn = document.getElementById('bulkTransferBtn');
            if (countEl) countEl.textContent = n;
            if (bar) {
                if (n > 0) bar.classList.add('visible');
                else bar.classList.remove('visible');
            }
            if (btn) btn.disabled = n === 0;
        }

        function toggleSelectAllLeads(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.lead-checkbox');
            checkboxes.forEach(cb => { cb.checked = !!selectAllCheckbox.checked; });
            updateBulkTransferBar();
        }

        async function submitBulkTransfer() {
            if (!CAN_TRANSFER_OWNER) return;
            const leadIds = getSelectedLeadIds();
            if (leadIds.length === 0) {
                alert('Please select at least one lead.');
                return;
            }
            const toUserId = document.getElementById('bulkTransferToUserId');
            if (!toUserId || !toUserId.value) {
                alert('Please select a user to transfer to.');
                return;
            }
            const createTask = document.getElementById('bulkTransferCreateTask');
            const payload = {
                lead_ids: leadIds,
                assigned_to: parseInt(toUserId.value, 10),
                create_calling_task: !!(createTask && createTask.checked),
                transfer_existing_tasks: true,
            };
            try {
                const response = await fetch(`${API_BASE_URL}/leads/bulk-assign`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${API_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || 'Bulk transfer failed');
                alert(result.message || 'Leads transferred successfully.');
                document.getElementById('selectAllLeadsOnPage').checked = false;
                document.querySelectorAll('.lead-checkbox').forEach(cb => { cb.checked = false; });
                updateBulkTransferBar();
                location.reload();
            } catch (error) {
                console.error('Bulk transfer error:', error);
                alert(error.message || 'Unable to transfer leads.');
            }
        }

        async function submitTransferAllFromUser() {
            if (!CAN_TRANSFER_OWNER || !FILTER_USER_ID) return;
            const toSelect = document.getElementById('transferAllToUserId');
            if (!toSelect || !toSelect.value) {
                alert('Please select a user to transfer to.');
                return;
            }
            const toName = toSelect.options[toSelect.selectedIndex].text;
            const createTask = document.getElementById('transferAllCreateTask');
            if (!confirm('Transfer all leads assigned to the current user to ' + toName + '? This will affect all such leads.')) return;
            const payload = {
                from_user_id: parseInt(FILTER_USER_ID, 10),
                assigned_to: parseInt(toSelect.value, 10),
                create_calling_task: !!(createTask && createTask.checked),
                transfer_existing_tasks: true,
            };
            try {
                const response = await fetch(`${API_BASE_URL}/leads/transfer-all-from-user`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${API_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || 'Transfer all failed');
                alert(result.message || 'Leads transferred successfully.');
                location.reload();
            } catch (error) {
                console.error('Transfer all error:', error);
                alert(error.message || 'Unable to transfer leads.');
            }
        }

        // Star rating rendering function
        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function renderStarRating(score) {
            if (!score || score < 1) return '<span class="text-gray-400 text-2xl">-</span>';
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= score) {
                    stars += '<i class="fas fa-star text-yellow-400 text-2xl"></i>';
                } else {
                    stars += '<i class="far fa-star text-gray-300 text-2xl"></i>';
                }
            }
            return stars;
        }

        async function viewShortDetails(leadId) {
            const modal = document.getElementById('leadDetailsModal');
            const content = document.getElementById('leadDetailsContent');
            
            // Show loading state
            content.innerHTML = '<div class="text-center py-12"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div><p class="mt-4 text-gray-600">Loading lead details...</p></div>';
            modal.classList.remove('hidden');
            
            try {
                // Fetch lead details using web route (session-based authentication)
                const response = await fetch(`/leads/${leadId}/short-details`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin' // Include cookies for session authentication
                });
                
                if (!response.ok) {
                    // If API fails, try to get data from the lead object in the page
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Failed to load lead details');
                }
                
                const data = await response.json();
                const lead = data.data || data;
                const leadScore = data.lead_score || null;
                
                // Render lead details
                const statusLabels = {
                    'new': 'New',
                    'contacted': 'Contacted',
                    'connected': 'Connected',
                    'verified_prospect': 'Verified Prospect',
                    'meeting_scheduled': 'Meeting Scheduled',
                    'meeting_completed': 'Meeting Completed',
                    'visit_scheduled': 'Visit Scheduled',
                    'visit_done': 'Visit Done',
                    'revisited_scheduled': 'Revisit Scheduled',
                    'revisited_completed': 'Revisit Completed',
                    'closed': 'Closed',
                    'dead': 'Dead',
                    'on_hold': 'On Hold',
                };
                
                const statusLabel = statusLabels[lead.status] || lead.status;
                const createdDate = new Date(lead.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                
                // Format location
                const locationParts = [];
                if (lead.city) locationParts.push(lead.city);
                if (lead.state) locationParts.push(lead.state);
                if (lead.pincode) locationParts.push(lead.pincode);
                const location = locationParts.length > 0 ? locationParts.join(', ') : 'N/A';
                
                // Format budget (for display in other sections if needed)
                let budgetText = '-';
                if (lead.budget_min && lead.budget_max) {
                    budgetText = `₹${parseFloat(lead.budget_min).toLocaleString('en-IN')} - ₹${parseFloat(lead.budget_max).toLocaleString('en-IN')}`;
                } else if (lead.budget_min) {
                    budgetText = `₹${parseFloat(lead.budget_min).toLocaleString('en-IN')}+`;
                } else if (lead.budget_max) {
                    budgetText = `Up to ₹${parseFloat(lead.budget_max).toLocaleString('en-IN')}`;
                } else if (lead.budget) {
                    budgetText = `₹${parseFloat(lead.budget).toLocaleString('en-IN')}`;
                }
                
                // Get assigned user with priority logic based on prospect verification status
                let assignedUser = 'Unassigned';
                let assignedUserRole = '';
                let assignedOwnerId = null;
                
                // Priority 1: Check if lead has prospects
                if (lead.prospects && lead.prospects.length > 0) {
                    // Check verification status of prospects
                    const verifiedProspects = lead.prospects.filter(p => 
                        p.verification_status === 'verified' || p.verification_status === 'approved'
                    );
                    
                    // If prospects are NOT verified → show telecaller
                    if (verifiedProspects.length === 0) {
                        // Get telecaller from first prospect
                        const prospect = lead.prospects[0];
                        if (prospect.telecaller && prospect.telecaller.name) {
                            assignedUser = prospect.telecaller.name;
                            assignedUserRole = prospect.telecaller.role ? prospect.telecaller.role.name : 'Sales Executive';
                        } else if (lead.active_assignments && lead.active_assignments.length > 0) {
                            // Fallback to active assignment if telecaller
                            const assignment = lead.active_assignments[0];
                            const assignedTo = assignment.assigned_to;
                            if (assignedTo && assignedTo.role && assignedTo.role.slug === 'telecaller') {
                                assignedUser = assignedTo.name || 'Unassigned';
                                assignedUserRole = assignedTo.role.name || '';
                                assignedOwnerId = assignedTo.id || null;
                            }
                        }
                    } else {
                        // If prospects ARE verified → show owner (Senior Manager or Sales Executive)
                        const prospect = verifiedProspects[0];
                        
                        // Priority: assigned_manager > manager_id > verified_by
                        if (prospect.assigned_manager && prospect.assigned_manager.name) {
                            assignedUser = prospect.assigned_manager.name;
                            assignedUserRole = prospect.assigned_manager.role ? prospect.assigned_manager.role.name : '';
                        } else if (prospect.manager && prospect.manager.name) {
                            assignedUser = prospect.manager.name;
                            assignedUserRole = prospect.manager.role ? prospect.manager.role.name : '';
                        } else if (prospect.verified_by && prospect.verified_by.name) {
                            assignedUser = prospect.verified_by.name;
                            assignedUserRole = prospect.verified_by.role ? prospect.verified_by.role.name : '';
                        }
                    }
                } else {
                    // No prospects - check active assignments
                    if (lead.active_assignments && lead.active_assignments.length > 0) {
                        const assignment = lead.active_assignments[0];
                        const assignedTo = assignment.assigned_to;
                        if (assignedTo && assignedTo.name) {
                            assignedUser = assignedTo.name;
                            assignedUserRole = assignedTo.role ? assignedTo.role.name : '';
                            assignedOwnerId = assignedTo.id || null;
                        }
                    }
                }
                
                // Format display with role
                const assignedUserDisplay = assignedUserRole 
                    ? `${assignedUser} (${assignedUserRole})`
                    : assignedUser;
                
                // Get initial for avatar
                const initial = lead.name ? lead.name.charAt(0).toUpperCase() : 'L';
                
                // Status badge color
                const statusColors = {
                    'new': 'bg-blue-100 text-blue-800',
                    'contacted': 'bg-yellow-100 text-yellow-800',
                    'connected': 'bg-blue-100 text-blue-800',
                    'verified_prospect': 'bg-purple-100 text-purple-800',
                    'meeting_scheduled': 'bg-indigo-100 text-indigo-800',
                    'meeting_completed': 'bg-cyan-100 text-cyan-800',
                    'visit_scheduled': 'bg-violet-100 text-violet-800',
                    'visit_done': 'bg-pink-100 text-pink-800',
                    'revisited_scheduled': 'bg-fuchsia-100 text-fuchsia-800',
                    'revisited_completed': 'bg-rose-100 text-rose-800',
                    'closed': 'bg-green-100 text-green-800',
                    'dead': 'bg-red-100 text-red-800',
                    'on_hold': 'bg-gray-100 text-gray-800',
                };
                const statusColor = statusColors[lead.status] || 'bg-gray-100 text-gray-800';
                
                content.innerHTML = `
                    <!-- Header Section with Avatar -->
                    <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-200">
                        <div class="flex-shrink-0">
                            <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-[#063A1C] to-[#205A44] flex items-center justify-center shadow-lg border-4 border-white">
                                <span class="text-white font-bold text-3xl">${initial}</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">${lead.name || 'N/A'}</h2>
                            <span class="inline-flex px-3 py-1.5 text-sm font-semibold rounded-full ${statusColor}">
                                ${statusLabel}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Contact Information Section -->
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-phone w-5 text-green-600 mr-3"></i>
                            <span class="text-green-600 font-medium">${lead.phone || 'N/A'}</span>
                        </div>
                        ${lead.email ? `
                        <div class="flex items-center text-sm">
                            <i class="fas fa-envelope w-5 text-green-600 mr-3"></i>
                            <span class="text-green-600 font-medium">${lead.email}</span>
                        </div>
                        ` : ''}
                        <div class="flex items-center text-sm">
                            <i class="fas fa-map-marker-alt w-5 text-blue-600 mr-3"></i>
                            <span class="text-blue-600 font-medium">${location}</span>
                        </div>
                    </div>
                    
                    <!-- Metrics Section - Lead Score Only -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 mb-2 flex items-center justify-center gap-2">
                                ${leadScore ? renderStarRating(leadScore) : '<span class="text-gray-400">-</span>'}
                            </div>
                            <div class="text-sm text-gray-600 font-medium">Lead Score</div>
                        </div>
                    </div>
                    
                    <!-- Lead Owner Section -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Lead Owner</label>
                                <p class="text-lg font-semibold text-gray-900">${assignedUserDisplay}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-tie text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Details -->
                    <div class="space-y-4 mb-6">
                        ${lead.preferred_location ? `
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Preferred Location</label>
                            <p class="text-sm text-gray-900">${lead.preferred_location}</p>
                        </div>
                        ` : ''}
                        ${(() => {
                            // Collect all interested projects from all prospects
                            const allProjects = new Set();
                            if (lead.prospects && lead.prospects.length > 0) {
                                lead.prospects.forEach(prospect => {
                                    // Handle both snake_case and camelCase
                                    const projects = prospect.interested_projects || prospect.interestedProjects || [];
                                    if (projects && projects.length > 0) {
                                        projects.forEach(project => {
                                            if (project && project.name) {
                                                allProjects.add(project.name);
                                            }
                                        });
                                    }
                                });
                            }
                            const projectNames = Array.from(allProjects);
                            if (projectNames.length > 0) {
                                return `
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Interested Projects</label>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        ${projectNames.map(projectName => `
                                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-gradient-to-r from-green-50 to-emerald-50 text-[#063A1C] border border-green-200">
                                                ${projectName}
                                            </span>
                                        `).join('')}
                                    </div>
                                </div>
                                `;
                            }
                            return '';
                        })()}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Assigned To</label>
                            <p class="text-sm text-gray-900">${assignedUserDisplay}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Created</label>
                            <p class="text-sm text-gray-900">${createdDate}</p>
                        </div>
                        ${lead.source ? `
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Source</label>
                            <p class="text-sm text-gray-900">${lead.source.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
                        </div>
                        ` : ''}
                    </div>
                    
                    ${lead.requirements ? `
                    <div class="mb-6 pt-4 border-t border-gray-200">
                        <label class="block text-xs font-medium text-gray-500 mb-2">Requirements</label>
                        <p class="text-sm text-gray-900">${lead.requirements}</p>
                    </div>
                    ` : ''}
                    ${lead.notes ? `
                    <div class="mb-6 pt-4 border-t border-gray-200">
                        <label class="block text-xs font-medium text-gray-500 mb-2">Notes</label>
                        <p class="text-sm text-gray-900">${lead.notes}</p>
                    </div>
                    ` : ''}

                    ${CAN_TRANSFER_OWNER ? `
                    <div class="mb-6 pt-4 border-t border-gray-200">
                        <label class="block text-xs font-medium text-gray-500 mb-2">Change Lead Owner</label>
                        <div class="space-y-3">
                            <select id="ownerTransferAssignedTo-${leadId}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select user</option>
                                ${OWNER_TRANSFER_USERS.map(user => `<option value="${user.id}">${escapeHtml(user.name)} (${escapeHtml(user.role)})</option>`).join('')}
                            </select>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" id="ownerTransferCreateTask-${leadId}" checked>
                                <span>Create calling task for new owner</span>
                            </label>
                            <textarea id="ownerTransferNotes-${leadId}" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Reason for owner change (optional)"></textarea>
                            <button type="button" onclick="transferLeadOwnerFromShortDetails(${leadId})" class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium text-sm">
                                Transfer Lead
                            </button>
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- Action Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <a href="/leads/${leadId}" class="inline-flex items-center justify-center w-full px-6 py-3 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 font-semibold shadow-md hover:shadow-lg">
                            <i class="fas fa-external-link-alt mr-2"></i> View Full Details
                        </a>
                    </div>
                `;

                if (CAN_TRANSFER_OWNER && assignedOwnerId) {
                    const selectEl = document.getElementById(`ownerTransferAssignedTo-${leadId}`);
                    if (selectEl) {
                        selectEl.value = String(assignedOwnerId);
                    }
                }
            } catch (error) {
                console.error('Error loading lead details:', error);
                content.innerHTML = `
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-red-600 font-medium mb-2">Failed to load lead details</p>
                        <p class="text-gray-600 text-sm mb-4">${error.message || 'Please try again'}</p>
                        <button onclick="viewShortDetails(${leadId})" class="mt-4 px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                            <i class="fas fa-redo mr-2"></i> Retry
                        </button>
                        <a href="/leads/${leadId}" class="mt-2 block px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium">
                            <i class="fas fa-external-link-alt mr-2"></i> View Full Details
                        </a>
                    </div>
                `;
            }
        }

        function closeLeadDetailsModal() {
            document.getElementById('leadDetailsModal').classList.add('hidden');
        }

        async function transferLeadOwnerFromShortDetails(leadId) {
            if (!CAN_TRANSFER_OWNER) return;

            const assignedToEl = document.getElementById(`ownerTransferAssignedTo-${leadId}`);
            const createTaskEl = document.getElementById(`ownerTransferCreateTask-${leadId}`);
            const notesEl = document.getElementById(`ownerTransferNotes-${leadId}`);

            if (!assignedToEl || !assignedToEl.value) {
                alert('Please select new owner');
                return;
            }

            const payload = {
                assigned_to: parseInt(assignedToEl.value, 10),
                create_calling_task: !!(createTaskEl && createTaskEl.checked),
                transfer_existing_tasks: true,
                notes: notesEl && notesEl.value ? notesEl.value.trim() : null,
            };

            try {
                const response = await fetch(`${API_BASE_URL}/leads/${leadId}/assign`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${API_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const contentType = response.headers.get('content-type') || '';
                const result = contentType.includes('application/json') ? await response.json() : {};
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to transfer lead owner');
                }

                alert('Lead owner changed successfully.');
                closeLeadDetailsModal();
                location.reload();
            } catch (error) {
                console.error('Owner transfer error:', error);
                alert(error.message || 'Unable to transfer lead owner');
            }
        }

        // Close modal when clicking outside
        document.getElementById('leadDetailsModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLeadDetailsModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLeadDetailsModal();
            }
        });
    </script>
@endsection

