<div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 5: Preview & Test</h3>
    <p class="text-gray-600 mb-6">Review how leads will be distributed before executing</p>

    <div class="space-y-4">
        <p class="text-gray-500">Preview results will be displayed here.</p>
    </div>

    <div class="mt-6 flex justify-between">
        <button onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            ← Previous
        </button>
        <button onclick="runPreview()" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
            Run Test Mode
        </button>
        <button onclick="savePreview()" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
            Continue to Confirm →
        </button>
    </div>
</div>

<script>
function runPreview() {
    // TODO: Implement preview/test mode
    alert('Preview mode will be implemented');
}

function savePreview() {
    nextStep();
}
</script>

