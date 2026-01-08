<div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 4: Conditions Builder</h3>
    <p class="text-gray-600 mb-6">Set up conditional assignment rules (optional)</p>

    <div class="space-y-4">
        <p class="text-gray-500">Condition builder will be displayed here.</p>
    </div>

    <div class="mt-6 flex justify-between">
        <button onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            ← Previous
        </button>
        <button onclick="saveConditions()" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
            Continue to Preview →
        </button>
    </div>
</div>

<script>
function saveConditions() {
    // TODO: Implement conditions save
    nextStep();
}
</script>

