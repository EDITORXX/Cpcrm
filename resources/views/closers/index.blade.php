@extends('layouts.app')

@section('title', 'Closers - Base CRM')
@section('page-title', 'Closers')
@section('page-subtitle', 'Site Visits Converted to Closers')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Status:</label>
                    <div class="flex gap-2">
                        <a href="{{ route('closers.index', array_merge(request()->except('status'), ['status' => ''])) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ !request()->has('status') ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            All ({{ $counts['all'] ?? 0 }})
                        </a>
                        <a href="{{ route('closers.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->get('status') === 'pending' ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Pending ({{ $counts['pending'] ?? 0 }})
                        </a>
                        <a href="{{ route('closers.index', array_merge(request()->except('status'), ['status' => 'verified'])) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->get('status') === 'verified' ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Verified ({{ $counts['verified'] ?? 0 }})
                        </a>
                        <a href="{{ route('closers.index', array_merge(request()->except('status'), ['status' => 'rejected'])) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->get('status') === 'rejected' ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Rejected ({{ $counts['rejected'] ?? 0 }})
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <form method="GET" action="{{ route('closers.index') }}" class="flex gap-2">
                        <input type="text" 
                               name="search" 
                               value="{{ request()->get('search') }}" 
                               placeholder="Search by name, phone, or project..."
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @if(request()->has('status'))
                            <input type="hidden" name="status" value="{{ request()->get('status') }}">
                        @endif
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request()->has('search'))
                            <a href="{{ route('closers.index', request()->except('search')) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Closers Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @if($closers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visit Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Converted At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($closers as $closer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $closer->customer_name ?? $closer->lead->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $closer->phone ?? $closer->lead->phone ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $closer->project ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $closer->date_of_visit ? \Carbon\Carbon::parse($closer->date_of_visit)->format('M d, Y') : ($closer->scheduled_at ? $closer->scheduled_at->format('M d, Y') : 'N/A') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $closer->converted_to_closer_at ? $closer->converted_to_closer_at->format('M d, Y') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($closer->closer_status === 'pending')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($closer->closer_status === 'verified')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Verified
                                            </span>
                                        @elseif($closer->closer_status === 'rejected')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $closer->closerVerifiedBy->name ?? 'N/A' }}
                                        </div>
                                        @if($closer->closer_verified_at)
                                            <div class="text-xs text-gray-500">
                                                {{ $closer->closer_verified_at->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('leads.show', $closer->lead_id ?? '#') }}" 
                                           class="text-indigo-600 hover:text-indigo-900">
                                            View Lead
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $closers->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500 text-lg">No closers found</p>
                    @if(request()->has('search') || request()->has('status'))
                        <a href="{{ route('closers.index') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900">
                            Clear filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
