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
@else
    @extends('layouts.app')
@endif

@section('title', 'Users - Base CRM')
@section('page-title', 'Users')

@section('header-actions')
    @if(auth()->user()->isAdmin())
    <button onclick="document.getElementById('hierarchyModal').style.display='flex'"
        class="px-4 py-2 border border-[#205A44] text-[#205A44] rounded-lg hover:bg-[#f0fdf4] transition-colors duration-200 text-sm font-medium flex items-center gap-2">
        <i class="fas fa-sitemap"></i> View Hierarchy
    </button>
    @endif
    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
    <a href="{{ route('users.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        Create User
    </a>
    @endif
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search and Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('users.index') }}" class="flex gap-4 items-end">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="Search by name, email, or phone..."
                       class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 placeholder-gray-400">
            </div>
            <div class="w-48">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Filter by Role</label>
                <select name="role" 
                        id="role"
                        class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand text-gray-900">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                    Search
                </button>
                @if(request('search') || request('role'))
                    <a href="{{ route('users.index') }}" class="ml-2 px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Card View -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($users as $user)
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg hover:border-brand transition-all duration-300 transform hover:-translate-y-1">
                <div class="p-6">
                    <!-- User Avatar and Name -->
                    <div class="flex items-center mb-5 pb-5 border-b border-gray-100">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-[#063A1C] to-[#205A44] flex items-center justify-center shadow-md">
                                <span class="text-white font-bold text-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 truncate mb-1">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-600 truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    <!-- User Details -->
                    <div class="space-y-3 mb-5">
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="text-gray-700 font-medium">{{ $user->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gradient-to-r from-green-50 to-emerald-50 text-[#063A1C] border border-green-200">
                                {{ $user->getDisplayRoleName() }}
                            </span>
                        </div>
                        @if($user->manager)
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-gray-700"><span class="font-medium">Manager:</span> {{ $user->manager->name }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-5">
                        @if($user->is_active)
                            <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200 shadow-sm">
                                <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Active
                            </span>
                        @else
                            <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Inactive
                            </span>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('users.show', $user) }}" 
                           class="flex-1 px-4 py-2.5 text-center text-sm font-semibold text-white bg-gradient-to-r from-[#063A1C] to-[#205A44] rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                        <a href="{{ route('users.edit', $user) }}" 
                           class="flex-1 px-4 py-2.5 text-center text-sm font-semibold text-[#063A1C] bg-green-50 rounded-lg hover:bg-green-100 border border-green-200 transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                        <form action="{{ route('users.destroy', $user) }}" 
                              method="POST" 
                              class="flex-1"
                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-red-500 to-red-600 rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="mt-6 bg-white px-4 py-3 border border-gray-100 rounded-xl shadow-sm sm:px-6">
            {{ $users->links() }}
        </div>
    @endif
@endsection

@push('scripts')
{{-- Hierarchy Modal --}}
@if(auth()->user()->isAdmin())
<div id="hierarchyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:1150px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.3);">

        {{-- Header --}}
        <div style="padding:18px 28px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:linear-gradient(135deg,#063A1C,#205A44);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-sitemap" style="color:#fff;font-size:16px;"></i>
                </div>
                <div>
                    <div style="font-size:17px;font-weight:700;color:#111827;">User Hierarchy</div>
                    <div style="font-size:12px;color:#6b7280;" id="hierUserCount"></div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                {{-- Toggle --}}
                <div style="background:#f3f4f6;border-radius:10px;padding:4px;display:flex;gap:4px;">
                    <button id="btnDemo" onclick="switchMode('demo')"
                        style="padding:6px 16px;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;background:#063A1C;color:#fff;transition:all .2s;">
                        <i class="fas fa-eye" style="margin-right:4px;"></i> Demo
                    </button>
                    <button id="btnActual" onclick="switchMode('actual')"
                        style="padding:6px 16px;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;background:transparent;color:#6b7280;transition:all .2s;">
                        <i class="fas fa-users" style="margin-right:4px;"></i> Actual
                    </button>
                </div>
                <button onclick="document.getElementById('hierarchyModal').style.display='none'"
                    style="width:32px;height:32px;border:none;background:#f3f4f6;border-radius:8px;cursor:pointer;font-size:18px;color:#6b7280;display:flex;align-items:center;justify-content:center;">×</button>
            </div>
        </div>

        {{-- Demo notice banner --}}
        <div id="demoBanner" style="background:#fefce8;border-bottom:1px solid #fde68a;padding:8px 28px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:8px;flex-shrink:0;">
            <i class="fas fa-info-circle"></i>
            <strong>Demo Mode:</strong> Yeh ek sample hierarchy hai jo dikhata hai ki CRM mein user structure kaisa hona chahiye. Actual data dekhne ke liye <strong>Actual</strong> tab click karein.
        </div>

        {{-- Legend --}}
        <div style="padding:10px 28px;border-bottom:1px solid #f3f4f6;display:flex;gap:14px;flex-wrap:wrap;flex-shrink:0;align-items:center;">
            <span style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Roles:</span>
            @foreach([['#7c3aed','Admin'],['#1d4ed8','CRM'],['#0369a1','HR / Finance'],['#065f46','Sales Manager'],['#15803d','Senior Manager'],['#ca8a04','Asst. SM'],['#b45309','Sales Executive']] as $l)
            <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:#374151;">
                <span style="width:10px;height:10px;border-radius:3px;background:{{ $l[0] }};display:inline-block;"></span> {{ $l[1] }}
            </div>
            @endforeach
        </div>

        {{-- Org Chart --}}
        <div id="orgChartContainer" style="overflow:auto;padding:28px;flex:1;"></div>
    </div>
</div>

<script>
const hierUsers = @json($hierarchyUsers);
let currentMode = 'demo';

// ── Demo data ──────────────────────────────────────────────
const demoUsers = [
    { id:1,  name:'Rahul Sharma',   role:'Admin',                   role_slug:'admin',                   manager_id:null, avatar:'R', children:[] },
    { id:2,  name:'Priya Singh',    role:'CRM Manager',             role_slug:'crm',                     manager_id:1,    avatar:'P', children:[] },
    { id:3,  name:'Anita Verma',    role:'HR Manager',              role_slug:'hr_manager',              manager_id:1,    avatar:'A', children:[] },
    { id:4,  name:'Suresh Gupta',   role:'Finance Manager',         role_slug:'finance_manager',         manager_id:1,    avatar:'S', children:[] },
    { id:5,  name:'Mohan Yadav',    role:'Sales Manager',           role_slug:'sales_manager',           manager_id:1,    avatar:'M', children:[] },
    { id:6,  name:'Deepak Kumar',   role:'Senior Manager',          role_slug:'senior_manager',          manager_id:5,    avatar:'D', children:[] },
    { id:7,  name:'Neha Joshi',     role:'Senior Manager',          role_slug:'senior_manager',          manager_id:5,    avatar:'N', children:[] },
    { id:8,  name:'Amit Patel',     role:'Asst. Sales Manager',     role_slug:'assistant_sales_manager', manager_id:6,    avatar:'A', children:[] },
    { id:9,  name:'Kavita Rao',     role:'Asst. Sales Manager',     role_slug:'assistant_sales_manager', manager_id:7,    avatar:'K', children:[] },
    { id:10, name:'Ravi Mehta',     role:'Sales Executive',         role_slug:'sales_executive',         manager_id:8,    avatar:'R', children:[] },
    { id:11, name:'Sunita Das',     role:'Sales Executive',         role_slug:'sales_executive',         manager_id:8,    avatar:'S', children:[] },
    { id:12, name:'Vijay Tiwari',   role:'Sales Executive',         role_slug:'sales_executive',         manager_id:9,    avatar:'V', children:[] },
    { id:13, name:'Pooja Mishra',   role:'Sales Executive',         role_slug:'sales_executive',         manager_id:9,    avatar:'P', children:[] },
];

// ── Colors ─────────────────────────────────────────────────
const roleColors = {
    'admin':                   { bg:'#ede9fe', border:'#7c3aed', text:'#5b21b6' },
    'crm':                     { bg:'#dbeafe', border:'#1d4ed8', text:'#1e40af' },
    'hr_manager':              { bg:'#e0f2fe', border:'#0369a1', text:'#075985' },
    'finance_manager':         { bg:'#e0f2fe', border:'#0369a1', text:'#075985' },
    'sales_manager':           { bg:'#d1fae5', border:'#065f46', text:'#064e3b' },
    'senior_manager':          { bg:'#d1fae5', border:'#15803d', text:'#14532d' },
    'assistant_sales_manager': { bg:'#fef9c3', border:'#ca8a04', text:'#92400e' },
    'sales_executive':         { bg:'#ffedd5', border:'#b45309', text:'#92400e' },
};
function getColor(slug) {
    return roleColors[slug] || { bg:'#f3f4f6', border:'#6b7280', text:'#374151' };
}

// ── Build tree from flat list ───────────────────────────────
function buildTree(users) {
    const map = {};
    users.forEach(u => { map[u.id] = { ...u, children: [] }; });
    const roots = [];
    users.forEach(u => {
        if (u.manager_id && map[u.manager_id]) {
            map[u.manager_id].children.push(map[u.id]);
        } else {
            roots.push(map[u.id]);
        }
    });
    return roots;
}

// ── Render single node ──────────────────────────────────────
function renderNode(node) {
    const c = getColor(node.role_slug);
    const hasChildren = node.children && node.children.length > 0;

    const card = `
        <div style="
            background:${c.bg};border:2px solid ${c.border};border-radius:12px;
            padding:10px 14px;text-align:center;min-width:118px;max-width:148px;
            cursor:default;transition:transform .15s,box-shadow .15s;
            box-shadow:0 2px 8px rgba(0,0,0,0.06);
        " onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,0.12)'"
           onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'">
            <div style="width:36px;height:36px;border-radius:50%;background:${c.border};color:#fff;
                font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 7px;">
                ${node.avatar}
            </div>
            <div style="font-size:11px;font-weight:700;color:#111827;line-height:1.3;margin-bottom:4px;word-break:break-word;">${node.name}</div>
            <div style="font-size:10px;font-weight:600;color:${c.text};background:white;border-radius:5px;padding:2px 6px;display:inline-block;">${node.role}</div>
            ${hasChildren ? `<div style="font-size:10px;color:${c.text};margin-top:3px;">${node.children.length} direct report${node.children.length>1?'s':''}</div>` : ''}
        </div>`;

    if (!hasChildren) {
        return `<div style="display:flex;flex-direction:column;align-items:center;">${card}</div>`;
    }

    const childrenHtml = node.children.map(ch => `
        <div style="display:flex;flex-direction:column;align-items:center;">
            <div style="width:2px;height:20px;background:#d1d5db;"></div>
            ${renderNode(ch)}
        </div>`).join('');

    // horizontal connector line across children
    const connector = node.children.length > 1
        ? `<div style="display:flex;align-items:flex-start;justify-content:center;width:100%;">
               <div style="border-top:2px solid #d1d5db;margin-top:0;flex:1;min-width:0;"></div>
           </div>` : '';

    return `
    <div style="display:flex;flex-direction:column;align-items:center;">
        ${card}
        <div style="width:2px;height:20px;background:#d1d5db;"></div>
        <div style="display:flex;gap:16px;justify-content:center;align-items:flex-start;position:relative;">
            ${childrenHtml}
        </div>
    </div>`;
}

// ── Switch toggle ───────────────────────────────────────────
function switchMode(mode) {
    currentMode = mode;
    const btnDemo   = document.getElementById('btnDemo');
    const btnActual = document.getElementById('btnActual');
    const banner    = document.getElementById('demoBanner');

    if (mode === 'demo') {
        btnDemo.style.background   = '#063A1C';
        btnDemo.style.color        = '#fff';
        btnActual.style.background = 'transparent';
        btnActual.style.color      = '#6b7280';
        banner.style.display       = 'flex';
    } else {
        btnActual.style.background = '#063A1C';
        btnActual.style.color      = '#fff';
        btnDemo.style.background   = 'transparent';
        btnDemo.style.color        = '#6b7280';
        banner.style.display       = 'none';
    }
    renderHierarchy();
}

// ── Main render ─────────────────────────────────────────────
function renderHierarchy() {
    const data = currentMode === 'demo' ? demoUsers : hierUsers;
    const container = document.getElementById('orgChartContainer');
    const countEl   = document.getElementById('hierUserCount');

    if (currentMode === 'demo') {
        countEl.textContent = 'Sample hierarchy — ' + data.length + ' demo users';
    } else {
        countEl.textContent = data.length + ' active users';
    }

    const tree = buildTree(JSON.parse(JSON.stringify(data))); // deep clone

    if (tree.length === 0) {
        container.innerHTML = `<div style="text-align:center;padding:60px;color:#6b7280;">
            <i class="fas fa-users" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
            <div style="font-size:15px;font-weight:600;">No users found</div>
            <div style="font-size:13px;margin-top:4px;">Add users to see the hierarchy here.</div>
        </div>`;
        return;
    }

    container.innerHTML = `
        <div style="display:flex;gap:32px;justify-content:center;align-items:flex-start;min-width:max-content;margin:0 auto;padding-bottom:16px;">
            ${tree.map(root => renderNode(root)).join('')}
        </div>`;
}

// ── Events ──────────────────────────────────────────────────
document.getElementById('hierarchyModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
document.querySelector('[onclick*="hierarchyModal"]').addEventListener('click', function() {
    currentMode = 'demo';
    // reset toggle visuals
    document.getElementById('btnDemo').style.background   = '#063A1C';
    document.getElementById('btnDemo').style.color        = '#fff';
    document.getElementById('btnActual').style.background = 'transparent';
    document.getElementById('btnActual').style.color      = '#6b7280';
    document.getElementById('demoBanner').style.display   = 'flex';
    setTimeout(renderHierarchy, 50);
});
</script>
@endif
@endpush

