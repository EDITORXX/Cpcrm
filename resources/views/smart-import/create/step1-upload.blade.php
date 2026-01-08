<div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 1: Upload File</h3>
    <p class="text-gray-600 mb-6">Upload your CSV or Excel file containing lead data</p>

    <form id="upload-form" enctype="multipart/form-data">
        @csrf
        
        <!-- File Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-400 transition-colors">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <span>Upload a file</span>
                            <input id="file-upload" name="file" type="file" accept=".csv,.xlsx,.xls" class="sr-only" required>
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">CSV, XLSX, XLS up to 10MB</p>
                </div>
            </div>
            <div id="file-name" class="mt-2 text-sm text-gray-600 hidden"></div>
        </div>

        <!-- Duplicate Handling -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Duplicate Handling</label>
            <select name="duplicate_handling" id="duplicate_handling" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" required>
                <option value="skip">Skip duplicates</option>
                <option value="merge">Merge with existing</option>
                <option value="update">Update existing</option>
            </select>
            <p class="mt-1 text-xs text-gray-500">How to handle leads that already exist in the system</p>
        </div>

        <!-- Upload Button -->
        <div class="flex justify-end space-x-4">
            <button type="button" onclick="window.location.href='{{ route('smart-import.index') }}'" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                Upload & Continue
            </button>
        </div>
    </form>

    <!-- Preview Section (hidden initially) -->
    <div id="preview-section" class="mt-8 hidden">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">File Preview</h4>
        <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
            <table id="preview-table" class="min-w-full divide-y divide-gray-200">
                <thead id="preview-thead" class="bg-gray-100"></thead>
                <tbody id="preview-tbody" class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>

        <!-- File Info -->
        <div id="file-info" class="mt-4 grid grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-sm text-gray-600">Total Rows</p>
                <p id="total-rows" class="text-2xl font-bold text-gray-900">-</p>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-sm text-gray-600">Duplicates Found</p>
                <p id="duplicates-count" class="text-2xl font-bold text-yellow-600">-</p>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-sm text-gray-600">File Type</p>
                <p id="file-type" class="text-2xl font-bold text-gray-900">-</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="nextStep()" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                Continue to Column Mapping →
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('upload-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';

        try {
            const response = await axios.post('{{ route("smart-import.upload") }}', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            if (response.data.success) {
                // Show preview
                document.getElementById('preview-section').classList.remove('hidden');
                
                // Display file info
                document.getElementById('total-rows').textContent = response.data.total_rows;
                document.getElementById('duplicates-count').textContent = response.data.duplicates_count;
                document.getElementById('file-type').textContent = response.data.file_type.toUpperCase();
                document.getElementById('file-name').textContent = response.data.file_name;
                document.getElementById('file-name').classList.remove('hidden');

                // Display preview table
                const headerRow = response.data.header_row;
                const preview = response.data.preview;
                
                // Build header
                const thead = document.getElementById('preview-thead');
                thead.innerHTML = '<tr>' + headerRow.map(col => `<th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">${col || ''}</th>`).join('') + '</tr>';

                // Build body (first 10 rows)
                const tbody = document.getElementById('preview-tbody');
                tbody.innerHTML = preview.slice(1, 11).map(row => 
                    '<tr>' + row.map(cell => `<td class="px-4 py-2 text-sm text-gray-900">${cell || ''}</td>`).join('') + '</tr>'
                ).join('');

                // Store data in global variable for next steps
                window.uploadData = response.data;
            } else {
                alert('Error: ' + (response.data.message || 'Upload failed'));
            }
        } catch (error) {
            console.error(error);
            alert('Error: ' + (error.response?.data?.message || 'Upload failed'));
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Upload & Continue';
        }
    });
</script>
@endpush

