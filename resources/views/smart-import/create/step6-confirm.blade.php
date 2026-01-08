<div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 6: Confirm & Create</h3>
    <p class="text-gray-600 mb-6">Review your automation settings and create</p>

    <form id="confirm-form">
        @csrf
        
        <div class="space-y-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Automation Name *</label>
                <input type="text" name="name" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" placeholder="e.g., Monthly Lead Import">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                <textarea name="description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" placeholder="Brief description of this automation"></textarea>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h4 class="font-semibold text-gray-900 mb-2">Summary</h4>
            <ul class="space-y-1 text-sm text-gray-600">
                <li>File: <span id="summary-file">-</span></li>
                <li>Total Rows: <span id="summary-rows">-</span></li>
                <li>Assignment Mode: <span id="summary-mode">-</span></li>
            </ul>
        </div>
    </form>

    <div class="mt-6 flex justify-between">
        <button onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            ← Previous
        </button>
        <button onclick="createAutomation()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            Create Automation
        </button>
    </div>
</div>

<script>
async function createAutomation() {
    const form = document.getElementById('confirm-form');
    const formData = new FormData(form);

    try {
        const response = await axios.post('{{ route("smart-import.store") }}', formData);

        if (response.data.success) {
            alert('Automation created successfully!');
            window.location.href = '{{ route("smart-import.index") }}';
        } else {
            alert('Error: ' + (response.data.message || 'Failed to create automation'));
        }
    } catch (error) {
        console.error(error);
        alert('Error: ' + (error.response?.data?.message || 'Failed to create automation'));
    }
}
</script>

