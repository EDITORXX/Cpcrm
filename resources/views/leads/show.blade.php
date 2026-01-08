@php
    $user = auth()->user();
    if ($user && !$user->relationLoaded('role')) {
        $user->load('role');
    }
@endphp
@if($user && ($user->isAdmin() || $user->isCrm()))
    @extends('layouts.app')
@elseif($user && $user->isSalesHead() && !$user->isAdmin() && !$user->isCrm())
    @extends('sales-head.layout')
@elseif($user && $user->isSalesManager())
    @extends('sales-manager.layout')
@else
    @extends('layouts.app')
@endif

@section('title', $lead->name . ' - Lead Details')
@section('page-title', 'Lead Details')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $lead->name }}</h1>
                <div class="flex items-center gap-4 mt-2">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ 
                        $lead->status === 'dead' ? 'bg-red-100 text-red-800' : 
                        ($lead->status === 'closed' ? 'bg-green-100 text-green-800' : 
                        'bg-blue-100 text-blue-800') 
                    }}">
                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                    </span>
                    <span class="text-sm text-gray-500">
                        <i class="fas fa-calendar mr-1"></i>
                        Created {{ $lead->created_at->format('M d, Y') }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('leads.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
        
        <!-- Quick Actions Section -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <!-- Call Button -->
                <button onclick="openCallModal()" class="flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 shadow-sm font-medium">
                    <i class="fas fa-phone"></i>
                    <span>Call</span>
                </button>
                
                <!-- WhatsApp Button -->
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}?text=Hello%20{{ urlencode($lead->name) }}" 
                   target="_blank"
                   class="flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 shadow-sm font-medium">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                
                <!-- Follow-up Button -->
                <button onclick="openFollowupModal()" class="flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 shadow-sm font-medium">
                    <i class="fas fa-calendar-check"></i>
                    <span>Follow-up</span>
                </button>
                
                <!-- Site Visit Button -->
                <button onclick="openSiteVisitModal()" class="flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 shadow-sm font-medium">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Site Visit</span>
                </button>
                
                <!-- Meeting Button -->
                <button onclick="openMeetingModal()" class="flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 shadow-sm font-medium">
                    <i class="fas fa-handshake"></i>
                    <span>Meeting</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Lead Information Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Lead Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Name</label>
                        <p class="text-gray-900 font-medium">{{ $lead->name }}</p>
                    </div>
                    
                    @if($lead->email)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900">
                            <a href="mailto:{{ $lead->email }}" class="text-blue-600 hover:underline">
                                {{ $lead->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-900">
                            <a href="tel:{{ $lead->phone }}" class="text-blue-600 hover:underline">
                                {{ $lead->phone }}
                            </a>
                        </p>
                    </div>
                    
                    @if($lead->address)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Address</label>
                        <p class="text-gray-900">{{ $lead->address }}</p>
                        @if($lead->city || $lead->state || $lead->pincode)
                            <p class="text-sm text-gray-600">
                                {{ trim(implode(', ', array_filter([$lead->city, $lead->state, $lead->pincode]))) }}
                            </p>
                        @endif
                    </div>
                    @endif
                    
                    @if($lead->source)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Source</label>
                        <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $lead->source)) }}</p>
                    </div>
                    @endif
                    
                    @if($lead->budget)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Budget</label>
                        <p class="text-gray-900">{{ $lead->budget }}</p>
                    </div>
                    @endif
                    
                    @if($lead->property_type)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Property Type</label>
                        <p class="text-gray-900">{{ ucfirst($lead->property_type) }}</p>
                    </div>
                    @endif
                    
                    @php
                        $allInterestedProjects = collect();
                        foreach ($lead->prospects as $prospect) {
                            if ($prospect->interestedProjects) {
                                $allInterestedProjects = $allInterestedProjects->merge($prospect->interestedProjects);
                            }
                        }
                        $uniqueProjects = $allInterestedProjects->unique('id');
                    @endphp
                    
                    @if($uniqueProjects->count() > 0)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Interested Projects</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach($uniqueProjects as $project)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white">
                                    {{ $project->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Created By</label>
                        <p class="text-gray-900">{{ $lead->creator->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $lead->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    
                    @if($lead->activeAssignments->count() > 0)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Assigned To</label>
                        <div class="space-y-1">
                            @foreach($lead->activeAssignments as $assignment)
                                <p class="text-gray-900">{{ $assignment->assignedTo->name ?? 'N/A' }}</p>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($lead->notes)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Notes</label>
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $lead->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Stats</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Calls</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->callLogs->count() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Site Visits</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->siteVisits->count() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Follow-ups</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->followUps->count() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Meetings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->meetings->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Activity Timeline</h2>
                
                @if($timeline->count() > 0)
                    <div class="relative">
                        <!-- Timeline Line -->
                        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        
                        <div class="space-y-6">
                            @php
                                $currentDate = null;
                            @endphp
                            
                            @foreach($timeline as $activity)
                                @php
                                    $activityDate = $activity['timestamp']->format('Y-m-d');
                                    $showDateHeader = $currentDate !== $activityDate;
                                    if ($showDateHeader) {
                                        $currentDate = $activityDate;
                                    }
                                @endphp
                                
                                @if($showDateHeader)
                                    <div class="relative">
                                        <div class="flex items-center mb-4">
                                            <div class="flex-1 border-t border-gray-200"></div>
                                            <span class="px-4 text-sm font-semibold text-gray-500">
                                                @if($activity['timestamp']->isToday())
                                                    Today
                                                @elseif($activity['timestamp']->isYesterday())
                                                    Yesterday
                                                @elseif($activity['timestamp']->isCurrentWeek())
                                                    {{ $activity['timestamp']->format('l, M d') }}
                                                @else
                                                    {{ $activity['timestamp']->format('M d, Y') }}
                                                @endif
                                            </span>
                                            <div class="flex-1 border-t border-gray-200"></div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="relative pl-16 pb-6">
                                    <!-- Timeline Dot -->
                                    <div class="absolute left-0 top-1">
                                        <div class="w-12 h-12 rounded-full border-4 border-white shadow-md flex items-center justify-center" style="background-color: {{ $activity['color'] }}20;">
                                            <i class="fas {{ $activity['icon'] }} text-sm" style="color: {{ $activity['color'] }};"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Activity Card -->
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1">{{ $activity['title'] }}</h3>
                                                <p class="text-sm text-gray-600 mb-2">{{ $activity['description'] }}</p>
                                                
                                                @if(isset($activity['metadata']) && !empty($activity['metadata']))
                                                    <div class="mt-2 space-y-1">
                                                        @if(isset($activity['metadata']['status']))
                                                            <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                                                Status: {{ ucfirst($activity['metadata']['status']) }}
                                                            </span>
                                                        @endif
                                                        @if(isset($activity['metadata']['duration']))
                                                            <span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">
                                                                Duration: {{ $activity['metadata']['duration'] }}
                                                            </span>
                                                        @endif
                                                        @if(isset($activity['metadata']['lead_score']))
                                                            <span class="inline-block px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">
                                                                Lead Score: {{ $activity['metadata']['lead_score'] }}/5
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="text-right ml-4">
                                                <p class="text-xs text-gray-500">
                                                    {{ $activity['timestamp']->format('h:i A') }}
                                                </p>
                                                @if($activity['user'])
                                                    <p class="text-xs text-gray-400 mt-1">
                                                        by {{ $activity['user']->name }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-history text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">No activities found for this lead.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals for Quick Actions -->
<!-- Call Modal -->
<div id="callModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Log Call</h3>
                <button onclick="closeCallModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="callForm" onsubmit="submitCall(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Call Type *</label>
                        <select name="call_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="outgoing">Outgoing</option>
                            <option value="incoming">Incoming</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input type="text" name="phone_number" value="{{ $lead->phone }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                            <input type="datetime-local" name="start_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (seconds) *</label>
                            <input type="number" name="duration" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Call Outcome</label>
                        <select name="call_outcome" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select outcome</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="callback">Callback</option>
                            <option value="no_answer">No Answer</option>
                            <option value="busy">Busy</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeCallModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Log Call</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Follow-up Modal -->
<div id="followupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Schedule Follow-up</h3>
                <button onclick="closeFollowupModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="followupForm" onsubmit="submitFollowup(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="site_visit">Site Visit</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time *</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes *</label>
                        <textarea name="notes" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeFollowupModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Site Visit Modal -->
<div id="siteVisitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Schedule Site Visit</h3>
                <button onclick="closeSiteVisitModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="siteVisitForm" onsubmit="submitSiteVisit(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property Name</label>
                        <input type="text" name="property_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property Address</label>
                        <textarea name="property_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time *</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Visit Notes</label>
                        <textarea name="visit_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeSiteVisitModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Meeting Modal -->
<div id="meetingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Schedule Meeting</h3>
                <button onclick="closeMeetingModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="meetingForm" onsubmit="submitMeeting(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" value="{{ $lead->name }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                        <input type="text" name="phone" value="{{ $lead->phone }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                        <input type="text" name="project" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Budget Range *</label>
                            <select name="budget_range" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Under 50 Lac">Under 50 Lac</option>
                                <option value="50 Lac – 1 Cr">50 Lac – 1 Cr</option>
                                <option value="1 Cr – 2 Cr">1 Cr – 2 Cr</option>
                                <option value="2 Cr – 3 Cr">2 Cr – 3 Cr</option>
                                <option value="Above 3 Cr">Above 3 Cr</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Property Type *</label>
                            <select name="property_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Plot/Villa">Plot/Villa</option>
                                <option value="Flat">Flat</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Just Exploring">Just Exploring</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode *</label>
                            <select name="payment_mode" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Self Fund">Self Fund</option>
                                <option value="Loan">Loan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tentative Period *</label>
                            <select name="tentative_period" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Within 1 Month">Within 1 Month</option>
                                <option value="Within 3 Months">Within 3 Months</option>
                                <option value="Within 6 Months">Within 6 Months</option>
                                <option value="More than 6 Months">More than 6 Months</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lead Type *</label>
                        <select name="lead_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="New Visit">New Visit</option>
                            <option value="Revisited">Revisited</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Prospect">Prospect</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Visit *</label>
                        <input type="date" name="date_of_visit" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time *</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Notes</label>
                        <textarea name="meeting_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeMeetingModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline-item {
        position: relative;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
</style>
@endpush

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api") }}';
    const API_TOKEN = '{{ auth()->check() ? (session("api_token") ?? auth()->user()->createToken("web-token")->plainTextToken) : "" }}';
    const LEAD_ID = {{ $lead->id }};

    // Modal open/close functions
    function openCallModal() {
        document.getElementById('callModal').classList.remove('hidden');
        // Set default start time to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.querySelector('#callForm input[name="start_time"]').value = now.toISOString().slice(0, 16);
    }

    function closeCallModal() {
        document.getElementById('callModal').classList.add('hidden');
        document.getElementById('callForm').reset();
    }

    function openFollowupModal() {
        document.getElementById('followupModal').classList.remove('hidden');
        // Set default scheduled time to tomorrow same time
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
        document.querySelector('#followupForm input[name="scheduled_at"]').value = tomorrow.toISOString().slice(0, 16);
    }

    function closeFollowupModal() {
        document.getElementById('followupModal').classList.add('hidden');
        document.getElementById('followupForm').reset();
    }

    function openSiteVisitModal() {
        document.getElementById('siteVisitModal').classList.remove('hidden');
        // Set default scheduled time to tomorrow same time
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
        document.querySelector('#siteVisitForm input[name="scheduled_at"]').value = tomorrow.toISOString().slice(0, 16);
    }

    function closeSiteVisitModal() {
        document.getElementById('siteVisitModal').classList.add('hidden');
        document.getElementById('siteVisitForm').reset();
    }

    function openMeetingModal() {
        document.getElementById('meetingModal').classList.remove('hidden');
        // Set default scheduled time to tomorrow same time
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
        document.querySelector('#meetingForm input[name="scheduled_at"]').value = tomorrow.toISOString().slice(0, 16);
        // Set date of visit to today
        const today = new Date();
        today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
        document.querySelector('#meetingForm input[name="date_of_visit"]').value = today.toISOString().slice(0, 10);
    }

    function closeMeetingModal() {
        document.getElementById('meetingModal').classList.add('hidden');
        document.getElementById('meetingForm').reset();
    }

    // Form submission functions
    async function submitCall(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            lead_id: LEAD_ID,
            phone_number: formData.get('phone_number'),
            call_type: formData.get('call_type'),
            start_time: new Date(formData.get('start_time')).toISOString(),
            duration: parseInt(formData.get('duration')),
            call_outcome: formData.get('call_outcome') || null,
            notes: formData.get('notes') || null,
        };

        try {
            const response = await fetch(`${API_BASE_URL}/call-logs`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Call logged successfully!');
                closeCallModal();
                location.reload(); // Reload to show in timeline
            } else {
                alert(result.message || 'Failed to log call');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while logging the call');
        }
    }

    async function submitFollowup(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            lead_id: LEAD_ID,
            type: formData.get('type'),
            notes: formData.get('notes'),
            scheduled_at: new Date(formData.get('scheduled_at')).toISOString(),
        };

        try {
            const response = await fetch(`${API_BASE_URL}/follow-ups`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok) {
                alert('Follow-up scheduled successfully!');
                closeFollowupModal();
                location.reload(); // Reload to show in timeline
            } else {
                alert(result.message || 'Failed to schedule follow-up');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while scheduling the follow-up');
        }
    }

    async function submitSiteVisit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            lead_id: LEAD_ID,
            property_name: formData.get('property_name') || null,
            property_address: formData.get('property_address') || null,
            scheduled_at: new Date(formData.get('scheduled_at')).toISOString(),
            visit_notes: formData.get('visit_notes') || null,
        };

        try {
            const response = await fetch(`${API_BASE_URL}/site-visits`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Site visit scheduled successfully!');
                closeSiteVisitModal();
                location.reload(); // Reload to show in timeline
            } else {
                alert(result.message || 'Failed to schedule site visit');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while scheduling the site visit');
        }
    }

    async function submitMeeting(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            lead_id: LEAD_ID,
            customer_name: formData.get('customer_name'),
            phone: formData.get('phone'),
            project: formData.get('project') || null,
            budget_range: formData.get('budget_range'),
            property_type: formData.get('property_type'),
            payment_mode: formData.get('payment_mode'),
            tentative_period: formData.get('tentative_period'),
            lead_type: formData.get('lead_type'),
            date_of_visit: formData.get('date_of_visit'),
            scheduled_at: new Date(formData.get('scheduled_at')).toISOString(),
            meeting_notes: formData.get('meeting_notes') || null,
        };

        try {
            const response = await fetch(`${API_BASE_URL}/meetings`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Meeting scheduled successfully!');
                closeMeetingModal();
                location.reload(); // Reload to show in timeline
            } else {
                alert(result.message || 'Failed to schedule meeting');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while scheduling the meeting');
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const callModal = document.getElementById('callModal');
        const followupModal = document.getElementById('followupModal');
        const siteVisitModal = document.getElementById('siteVisitModal');
        const meetingModal = document.getElementById('meetingModal');

        if (event.target === callModal) {
            closeCallModal();
        }
        if (event.target === followupModal) {
            closeFollowupModal();
        }
        if (event.target === siteVisitModal) {
            closeSiteVisitModal();
        }
        if (event.target === meetingModal) {
            closeMeetingModal();
        }
    }
</script>
@endpush
