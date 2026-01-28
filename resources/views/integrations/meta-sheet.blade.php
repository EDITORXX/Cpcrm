@extends('layouts.app')

@section('title', 'Meta Sheet Configuration - Base CRM')
@section('page-title', 'Meta Sheet Configuration')

@section('header-actions')
    <a href="{{ route('integrations.meta-sheet.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-plus mr-2"></i>
        Add New Meta Sheet
    </a>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Configured Meta/Facebook Sheets</h2>
        
        @if($configs->isEmpty())
            <div class="text-center py-12">
                <i class="fab fa-facebook text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500 mb-4">No Meta/Facebook sheet integrations configured yet.</p>
                <a href="{{ route('integrations.meta-sheet.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium inline-block">
                    <i class="fas fa-plus mr-2"></i>
                    Add Your First Meta Sheet
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sheet Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mappings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($configs as $config)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fab fa-facebook text-blue-600 mr-2"></i>
                                        <span class="text-sm font-medium text-gray-900">{{ $config->sheet_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        @if($config->is_draft)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-save mr-1"></i> Draft
                                            </span>
                                        @elseif($config->is_active)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($config->columnMappings && $config->columnMappings->count() > 0)
                                        {{ $config->columnMappings->count() }} fields mapped
                                    @else
                                        <span class="text-gray-400">Not mapped yet</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($config->is_draft)
                                            <a href="{{ route('integrations.meta-sheet.step' . $config->resume_step, $config->id) }}" 
                                               class="text-blue-600 hover:text-blue-900" 
                                               title="Resume Setup">
                                                <i class="fas fa-play mr-1"></i> Resume
                                            </a>
                                        @else
                                            <button onclick="testIntegration({{ $config->id }})" class="text-indigo-600 hover:text-indigo-900" title="Test Integration">
                                                <i class="fas fa-vial"></i> Test
                                            </button>
                                            <button onclick="toggleIntegration({{ $config->id }})" class="text-yellow-600 hover:text-yellow-900" title="Toggle Status">
                                                <i class="fas fa-toggle-{{ $config->is_active ? 'on' : 'off' }}"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function testIntegration(id) {
    if (!confirm('This will send a test lead to CRM. Continue?')) {
        return;
    }
    
    fetch(`/integrations/meta-sheet/test/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test successful! Lead ID: ' + (data.lead_id || 'N/A'));
        } else {
            alert('Test failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Test failed: ' + error.message);
    });
}

function toggleIntegration(id) {
    fetch(`/integrations/meta-sheet/toggle/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to toggle integration');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to toggle integration');
    });
}
</script>
@endpush
@endsection
