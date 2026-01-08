@extends('layouts.app')

@section('title', 'Create Smart Import Automation - Base CRM')
@section('page-title', 'Create Smart Import Automation')
@section('page-subtitle', 'Step-by-step wizard to configure intelligent lead import and distribution')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div id="step-1-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white font-semibold">1</div>
                        <span class="ml-2 text-sm font-medium text-gray-900">Upload</span>
                    </div>
                    <div class="w-16 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div id="step-2-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">2</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Map Columns</span>
                    </div>
                    <div class="w-16 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div id="step-3-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Distribution</span>
                    </div>
                    <div class="w-16 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div id="step-4-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">4</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Conditions</span>
                    </div>
                    <div class="w-16 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div id="step-5-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">5</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Preview</span>
                    </div>
                    <div class="w-16 h-0.5 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div id="step-6-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">6</div>
                        <span class="ml-2 text-sm font-medium text-gray-600">Confirm</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <!-- Step 1: Upload -->
            <div id="step-1" class="step-content">
                @include('smart-import.create.step1-upload')
            </div>

            <!-- Step 2: Column Mapping -->
            <div id="step-2" class="step-content hidden">
                @include('smart-import.create.step2-mapping')
            </div>

            <!-- Step 3: Distribution Rules -->
            <div id="step-3" class="step-content hidden">
                @include('smart-import.create.step3-distribution')
            </div>

            <!-- Step 4: Conditions -->
            <div id="step-4" class="step-content hidden">
                @include('smart-import.create.step4-conditions')
            </div>

            <!-- Step 5: Preview -->
            <div id="step-5" class="step-content hidden">
                @include('smart-import.create.step5-preview')
            </div>

            <!-- Step 6: Confirm -->
            <div id="step-6" class="step-content hidden">
                @include('smart-import.create.step6-confirm')
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentStep = 1;
        const totalSteps = 6;

        function updateStepIndicator(step) {
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step-${i}-indicator`);
                const stepContent = document.getElementById(`step-${i}`);
                
                if (i < step) {
                    indicator.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold';
                } else if (i === step) {
                    indicator.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white font-semibold';
                    if (stepContent) stepContent.classList.remove('hidden');
                } else {
                    indicator.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold';
                    if (stepContent) stepContent.classList.add('hidden');
                }
            }
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepIndicator(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepIndicator(currentStep);
            }
        }

        // Initialize
        updateStepIndicator(1);
    </script>
    @endpush
@endsection

