<div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 2: Map Columns</h3>
    <p class="text-gray-600 mb-6">Map your file columns to CRM fields</p>

    <div id="mapping-container" class="space-y-4">
        <p class="text-gray-500">Column mapping will be displayed here after file upload.</p>
    </div>

    <div class="mt-6 flex justify-between">
        <button onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            ← Previous
        </button>
        <button onclick="saveMapping()" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
            Continue to Distribution →
        </button>
    </div>
</div>

<script>
function saveMapping() {
    // TODO: Implement mapping save
    nextStep();
}
</script>

