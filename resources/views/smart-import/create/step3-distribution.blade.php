<div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 3: Distribution Rules</h3>
    <p class="text-gray-600 mb-6">Configure how leads will be distributed to users</p>

    <div class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Mode</label>
            <select id="assignment_mode" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900">
                <option value="percentage">Percentage Based</option>
                <option value="fixed_count">Fixed Count Based</option>
                <option value="round_robin">Round Robin</option>
            </select>
        </div>

        <div id="distribution-config">
            <p class="text-gray-500">Distribution configuration will be displayed here.</p>
        </div>
    </div>

    <div class="mt-6 flex justify-between">
        <button onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            ← Previous
        </button>
        <button onclick="saveDistribution()" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
            Continue to Conditions →
        </button>
    </div>
</div>

<script>
function saveDistribution() {
    // TODO: Implement distribution save
    nextStep();
}
</script>

