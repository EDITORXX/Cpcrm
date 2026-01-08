@extends('layouts.app')

@section('title', 'Smart Import Assignments - Base CRM')
@section('page-title', 'Smart Import Assignments')
@section('page-subtitle', 'Manage and override lead assignments')

@section('header-actions')
    <a href="{{ route('smart-import.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 text-sm font-medium">
        ← Back to Automations
    </a>
@endsection

@section('content')
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('smart-import.assignments') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Execution ID</label>
                <input type="number" name="execution_id" value="{{ request('execution_id') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                <select name="assigned_to" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="is_queued" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900">
                    <option value="">All</option>
                    <option value="0" {{ request('is_queued') === '0' ? 'selected' : '' }}>Assigned</option>
                    <option value="1" {{ request('is_queued') === '1' ? 'selected' : '' }}>Queued</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Assignments Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Assignments</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lead</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SLA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Override</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assignments as $assignment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $assignment->lead->name }}</div>
                                <div class="text-sm text-gray-500">{{ $assignment->lead->phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $assignment->assignedTo->name }}</div>
                                @if($assignment->override_user_id)
                                    <div class="text-xs text-orange-600">Overridden</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($assignment->is_queued)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Queued: {{ $assignment->queued_reason }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Assigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($assignment->sla_started_at)
                                    @if($assignment->sla_breached)
                                        <span class="text-red-600">Breached</span>
                                    @elseif($assignment->sla_met_at)
                                        <span class="text-green-600">Met</span>
                                    @else
                                        <span class="text-yellow-600">Pending</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">No SLA</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($assignment->override_user_id)
                                    <div class="text-xs">{{ $assignment->overrideUser->name }}</div>
                                    <div class="text-xs text-gray-400">{{ Str::limit($assignment->override_reason, 30) }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openOverrideModal({{ $assignment->id }}, {{ $assignment->assigned_to }}, '{{ $assignment->lead->name }}')" class="text-indigo-600 hover:text-indigo-900">
                                    Override
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No assignments found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $assignments->links() }}
        </div>
    </div>

    <!-- Override Modal -->
    <div id="override-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Override Assignment</h3>
            </div>
            <form id="override-form" class="p-6">
                @csrf
                <input type="hidden" id="override-assignment-id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lead</label>
                    <input type="text" id="override-lead-name" class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 text-gray-900" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current User</label>
                    <input type="text" id="override-current-user" class="block w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 text-gray-900" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reassign To *</label>
                    <select id="override-new-user" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" required>
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
                    <textarea id="override-reason" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" placeholder="Explain why you are overriding this assignment..." required></textarea>
                    <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeOverrideModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                        Override Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openOverrideModal(assignmentId, currentUserId, leadName) {
            document.getElementById('override-assignment-id').value = assignmentId;
            document.getElementById('override-lead-name').value = leadName;
            
            // Get current user name
            const currentUserSelect = document.querySelector(`option[value="${currentUserId}"]`);
            document.getElementById('override-current-user').value = currentUserSelect ? currentUserSelect.textContent : 'Unknown';
            
            document.getElementById('override-modal').classList.remove('hidden');
        }

        function closeOverrideModal() {
            document.getElementById('override-modal').classList.add('hidden');
            document.getElementById('override-form').reset();
        }

        document.getElementById('override-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const assignmentId = document.getElementById('override-assignment-id').value;
            const formData = new FormData(this);
            formData.append('new_user_id', document.getElementById('override-new-user').value);
            formData.append('reason', document.getElementById('override-reason').value);

            try {
                const response = await axios.post(`/smart-import/assignments/${assignmentId}/override`, formData);

                if (response.data.success) {
                    alert('Assignment overridden successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to override assignment'));
                }
            } catch (error) {
                console.error(error);
                alert('Error: ' + (error.response?.data?.message || 'Failed to override assignment'));
            }
        });
    </script>
    @endpush
@endsection

