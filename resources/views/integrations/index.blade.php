@extends('layouts.app')

@section('title', 'Integrations - Base CRM')
@section('page-title', 'Integrations')

@section('header-actions')
    <button onclick="showComingSoonNotification('Configuration')" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-cog mr-2"></i>
        Configuration
    </button>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Integrations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Email Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.email') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-envelope text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Email</h3>
                <p class="text-sm text-gray-500 mb-4">Email integration for sending and receiving emails</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Calendar Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.calendar') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Calendar</h3>
                <p class="text-sm text-gray-500 mb-4">Calendar integration for scheduling and events</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- WhatsApp API Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.whatsapp') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fab fa-whatsapp text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">WhatsApp API</h3>
                <p class="text-sm text-gray-500 mb-4">WhatsApp Business API integration via Engage API</p>
                @php
                    $whatsappSettings = \App\Models\WhatsAppApiSettings::getSettings();
                @endphp
                <div class="flex items-center mb-4">
                    @if($whatsappSettings->is_active && $whatsappSettings->is_verified)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active & Verified</span>
                    @elseif($whatsappSettings->is_active)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Active (Not Verified)</span>
                    @elseif($whatsappSettings->api_token)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Configured</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Not Configured</span>
                    @endif
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.whatsapp') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Facebook Meta Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.facebook') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fab fa-facebook text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Facebook Meta</h3>
                <p class="text-sm text-gray-500 mb-4">Facebook and Meta platform integration</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Magic Bricks Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.magic-bricks') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Magic Bricks</h3>
                <p class="text-sm text-gray-500 mb-4">Magic Bricks real estate platform integration</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Housing Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.housing') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-home text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Housing</h3>
                <p class="text-sm text-gray-500 mb-4">Housing.com real estate platform integration</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- 99acres Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.99acres') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">99acres</h3>
                <p class="text-sm text-gray-500 mb-4">99acres real estate platform integration</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    function showComingSoonNotification(integrationName) {
        alert(integrationName + ' Integration\n\nComing Soon!\n\nThis integration is currently under development and will be available soon.');
    }
</script>
@endpush
@endsection
