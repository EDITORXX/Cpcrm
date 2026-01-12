@extends('sales-manager.layout')

@section('title', 'Prospects - Sales Manager')
@section('page-title', 'Prospects')

@push('styles')
<style>
    #prospectsGrid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
    
    @media (max-width: 1024px) {
        #prospectsGrid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        #prospectsGrid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        /* Search and filter controls */
        .flex.items-center.justify-between {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .flex.gap-2 {
            width: 100%;
            flex-direction: column;
            gap: 8px;
        }
        
        .flex.gap-2 input,
        .flex.gap-2 select {
            width: 100%;
            padding: 10px;
        }
        
        /* Prospect cards responsive */
        .bg-white.rounded-lg.shadow {
            padding: 16px !important;
        }
        
        /* Pagination responsive */
        #pagination {
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }
    }
    
    .prospect-card {
        transition: all 0.3s ease;
    }
    
    .prospect-card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-6" style="flex-wrap: wrap; gap: 12px;">
        <h2 class="text-xl font-bold text-gray-900">Team Prospects</h2>
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <input 
                type="text" 
                id="searchInput"
                placeholder="Search prospects..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                onkeyup="handleSearch()"
            >
            <select 
                id="statusFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                onchange="loadProspects()"
            >
                <option value="all">All Status</option>
                <option value="pending_verification">Pending Verification</option>
                <option value="verified">Verified</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-gray-400 text-4xl mb-4"></i>
        <p class="text-gray-500">Loading prospects...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-12" style="display: none;">
        <i class="fas fa-star text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Prospects Found</h3>
        <p class="text-gray-500">No prospects match your current filters.</p>
    </div>

    <!-- Prospects Cards -->
    <div id="prospectsCards" style="display: none;">
        <div id="prospectsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Prospects will be loaded here -->
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="mt-6 flex items-center justify-between">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    const API_TOKEN = '{{ $api_token }}';
    let searchTimeout = null;

    // Get auth headers with Bearer token
    function getAuthHeaders() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${API_TOKEN}`,
        };
    }

    // Store all prospects data
    let allProspects = [];

    // Load prospects
    async function loadProspects(page = 1) {
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const prospectsCards = document.getElementById('prospectsCards');
        const prospectsGrid = document.getElementById('prospectsGrid');
        
        loadingState.style.display = 'block';
        emptyState.style.display = 'none';
        prospectsCards.style.display = 'none';

        try {
            const status = document.getElementById('statusFilter').value;
            const search = document.getElementById('searchInput').value;
            
            const params = new URLSearchParams({
                page: page,
                per_page: 15,
            });
            
            if (status !== 'all') {
                params.append('verification_status', status);
            }
            
            if (search) {
                params.append('search', search);
            }

            const response = await fetch(`${API_BASE_URL}/prospects?${params}`, {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load prospects');
            }

            const data = await response.json();

            if (data.data && data.data.length > 0) {
                allProspects = data.data;
                prospectsGrid.innerHTML = '';
                data.data.forEach(prospect => {
                    const card = createProspectCard(prospect);
                    prospectsGrid.appendChild(card);
                });
                
                renderPagination(data);
                prospectsCards.style.display = 'block';
                emptyState.style.display = 'none';
            } else {
                prospectsCards.style.display = 'none';
                emptyState.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading prospects:', error);
            alert('Failed to load prospects. Please try again.');
        } finally {
            loadingState.style.display = 'none';
        }
    }

    // Create prospect card
    function createProspectCard(prospect) {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200';
        card.id = `prospect-card-${prospect.id}`;
        
        const statusBadge = getStatusBadge(prospect.verification_status);
        const createdBy = prospect.telecaller ? prospect.telecaller.name : (prospect.created_by ? prospect.created_by.name : 'N/A');
        const createdAt = new Date(prospect.created_at).toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });

        card.innerHTML = `
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">${prospect.customer_name || 'N/A'}</h3>
                        <p class="text-sm text-gray-500">${createdBy}</p>
                    </div>
                    ${statusBadge}
                </div>
                
                <div class="space-y-2 mb-4">
                    ${prospect.lead_score ? `
                    <div class="flex items-center text-sm text-gray-600 mb-2">
                        <i class="fas fa-star w-5 text-gray-400" style="color: #fbbf24;"></i>
                        <span class="ml-1">Lead Score: ${renderStarRating(prospect.lead_score)}</span>
                    </div>
                    ` : ''}
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-phone w-5 text-gray-400"></i>
                        <span>${prospect.phone || 'N/A'}</span>
                    </div>
                    ${prospect.budget ? `
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-rupee-sign w-5 text-gray-400"></i>
                        <span>₹${parseFloat(prospect.budget).toLocaleString('en-IN')}</span>
                    </div>
                    ` : ''}
                    ${prospect.preferred_location ? `
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                        <span>${prospect.preferred_location}</span>
                    </div>
                    ` : ''}
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-calendar w-5 text-gray-400"></i>
                        <span>${createdAt}</span>
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button 
                        onclick="makeCall('${prospect.phone || ''}')" 
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                    >
                        <i class="fas fa-phone mr-2"></i>
                        Call
                    </button>
                    <button 
                        onclick="openWhatsApp('${prospect.phone || ''}')" 
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 text-sm font-medium shadow-md"
                    >
                        <i class="fab fa-whatsapp mr-2"></i>
                        WhatsApp
                    </button>
                    <a 
                        href="/sales-manager/prospects/${prospect.id}" 
                        class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium text-center"
                    >
                        <i class="fas fa-eye mr-2"></i>
                        View Details
                    </a>
                </div>
            </div>
            
            <!-- Expandable Details Section -->
            <div 
                id="details-${prospect.id}" 
                class="hidden border-t border-gray-200 bg-gray-50 p-5"
                style="transition: all 0.3s ease;"
            >
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        ${prospect.lead_score ? `
                        <div class="col-span-2">
                            <span class="text-gray-500">Lead Score:</span>
                            <span class="font-medium text-gray-900 ml-2">${renderStarRating(prospect.lead_score)} <span class="text-gray-500 text-xs">(${prospect.lead_score}/5)</span></span>
                        </div>
                        ` : ''}
                        <div>
                            <span class="text-gray-500">Phone:</span>
                            <span class="font-medium text-gray-900 ml-2">${prospect.phone || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Budget:</span>
                            <span class="font-medium text-gray-900 ml-2">${prospect.budget ? '₹' + parseFloat(prospect.budget).toLocaleString('en-IN') : 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Location:</span>
                            <span class="font-medium text-gray-900 ml-2">${prospect.preferred_location || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Size:</span>
                            <span class="font-medium text-gray-900 ml-2">${prospect.size || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Purpose:</span>
                            <span class="font-medium text-gray-900 ml-2">${prospect.purpose === 'end_user' ? 'End User' : (prospect.purpose === 'investment' ? 'Investment' : 'N/A')}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Possession:</span>
                            <span class="font-medium text-gray-900 ml-2">${prospect.possession || 'N/A'}</span>
                        </div>
                    </div>
                    ${prospect.remark ? `
                    <div class="mt-3 p-3 bg-white rounded border border-gray-200">
                        <span class="text-xs font-medium text-gray-500 uppercase">Remark:</span>
                        <p class="text-sm text-gray-700 mt-1">${prospect.remark}</p>
                    </div>
                    ` : ''}
                    ${prospect.manager_remark ? `
                    <div class="mt-3 p-3 bg-green-50 rounded border border-green-200">
                        <span class="text-xs font-medium text-green-700 uppercase">Manager Remark:</span>
                        <p class="text-sm text-green-800 mt-1">${prospect.manager_remark}</p>
                    </div>
                    ` : ''}
                    ${prospect.lead_status ? `
                    <div class="mt-3 p-3 bg-blue-50 rounded border border-blue-200">
                        <span class="text-xs font-medium text-blue-700 uppercase">Lead Status:</span>
                        <span class="ml-2 inline-block px-3 py-1 rounded-full text-xs font-semibold ${getLeadStatusBadgeClass(prospect.lead_status)}">${getLeadStatusLabel(prospect.lead_status)}</span>
                    </div>
                    ` : ''}
                    ${prospect.rejection_reason ? `
                    <div class="mt-3 p-3 bg-red-50 rounded border border-red-200">
                        <span class="text-xs font-medium text-red-700 uppercase">Rejection Reason:</span>
                        <p class="text-sm text-red-800 mt-1">${prospect.rejection_reason}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        return card;
    }

    function getLeadStatusLabel(status) {
        const labels = {
            'hot': 'Hot',
            'warm': 'Warm',
            'cold': 'Cold',
            'junk': 'Junk'
        };
        return labels[status] || status;
    }
    
    function getLeadStatusBadgeClass(status) {
        const classes = {
            'hot': 'bg-red-100 text-red-800 border-red-200',
            'warm': 'bg-orange-100 text-orange-800 border-orange-200',
            'cold': 'bg-blue-100 text-blue-800 border-blue-200',
            'junk': 'bg-gray-100 text-gray-800 border-gray-200'
        };
        return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
    }
    
    // Render star rating
    function renderStarRating(rating) {
        if (!rating || rating < 1 || rating > 5) {
            return '<span class="text-gray-400">No rating</span>';
        }
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<span style="color: #fbbf24; font-size: 16px;">★</span>';
            } else {
                stars += '<span style="color: #d1d5db; font-size: 16px;">☆</span>';
            }
        }
        return stars;
    }
    
    // Get status badge
    function getStatusBadge(status) {
        const badges = {
            'pending_verification': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
            'pending': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
            'verified': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Verified</span>',
            'rejected': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>',
        };
        return badges[status] || '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">' + status + '</span>';
    }

    // Render pagination
    function renderPagination(data) {
        const pagination = document.getElementById('pagination');
        if (data.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '<div class="flex items-center gap-2">';
        
        // Previous button
        if (data.current_page > 1) {
            html += `<button onclick="loadProspects(${data.current_page - 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>`;
        }
        
        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `<button onclick="loadProspects(${i})" class="px-3 py-2 border border-gray-300 rounded-lg ${i === data.current_page ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' : 'hover:bg-gray-50'}">${i}</button>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += `<span class="px-3 py-2">...</span>`;
            }
        }
        
        // Next button
        if (data.current_page < data.last_page) {
            html += `<button onclick="loadProspects(${data.current_page + 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>`;
        }
        
        html += '</div>';
        html += `<div class="text-sm text-gray-500">Showing ${data.from} to ${data.to} of ${data.total} prospects</div>`;
        
        pagination.innerHTML = html;
    }

    // Toggle details section
    function toggleDetails(prospectId) {
        const detailsDiv = document.getElementById(`details-${prospectId}`);
        const chevron = document.getElementById(`chevron-${prospectId}`);
        const btn = document.getElementById(`viewDetailsBtn-${prospectId}`);
        
        if (detailsDiv.classList.contains('hidden')) {
            detailsDiv.classList.remove('hidden');
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
            btn.innerHTML = `<i class="fas fa-chevron-up mr-2" id="chevron-${prospectId}"></i> Hide Details`;
        } else {
            detailsDiv.classList.add('hidden');
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
            btn.innerHTML = `<i class="fas fa-chevron-down mr-2" id="chevron-${prospectId}"></i> View Details`;
        }
    }

    // Open WhatsApp
    function openWhatsApp(phone) {
        if (!phone || phone === 'N/A') {
            alert('Phone number not available');
            return;
        }
        const cleanedPhone = phone.replace(/[^\d+]/g, '');
        if (!cleanedPhone) {
            alert('Invalid phone number');
            return;
        }
        window.open(`https://wa.me/${cleanedPhone}`, '_blank');
    }

    // Make call
    function makeCall(phone) {
        if (!phone || phone === 'N/A') {
            alert('Phone number not available');
            return;
        }
        const cleanedPhone = phone.replace(/[^\d+]/g, '');
        if (!cleanedPhone) {
            alert('Invalid phone number');
            return;
        }
        window.location.href = `tel:${cleanedPhone}`;
    }

    // Handle search with debounce
    function handleSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadProspects(1);
        }, 500);
    }

    // Load prospects on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadProspects();
    });
</script>
@endpush

