@extends('layouts.app')

@section('title', 'Create Policy - PolicyZen')

@section('page-title')
    <div class="flex items-center">
        <a href="{{ route('policies.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span>Create New Policy</span>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <form method="POST" action="{{ route('policies.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Policy Number *</label>
                        <input type="text" name="policy_number" required pattern="^[A-Z0-9\-]{5,20}$"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent"
                            placeholder="POL-2025-001">
                        <p class="text-xs text-gray-500 mt-1">5-20 characters, uppercase letters, numbers and hyphens only
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Insurance Type *</label>
                        <select name="insurance_type" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                            <option value="HEALTH">Health Insurance</option>
                            <option value="ACCIDENT">Accident Insurance</option>
                            <option value="PROPERTY">Property Insurance</option>
                            <option value="VEHICLE">Vehicle Insurance</option>
                            <option value="MARINE">Marine Insurance</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider *</label>
                        <input type="text" name="provider" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                        <input type="date" name="start_date" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                        <input type="date" name="end_date" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Starting Coverage Pool (₹) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">₹</span>
                            <input type="text" name="starting_coverage_pool" required pattern="^[0-9]+(?:\.[0-9]{1,2})?$"
                                class="w-full border border-gray-300 rounded-lg px-8 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent"
                                placeholder="100000.00" maxlength="15" oninput="formatCurrency(this)">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Enter amount in Indian Rupees (e.g., 100000.00 for ₹1,00,000)
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Available Coverage Pool (₹) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">₹</span>
                            <input type="text" name="available_coverage_pool" required
                                pattern="^[0-9]+(?:\.[0-9]{1,2})?$"
                                class="w-full border border-gray-300 rounded-lg px-8 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent"
                                placeholder="100000.00" maxlength="15" oninput="formatCurrency(this)">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Enter amount in Indian Rupees (e.g., 100000.00 for ₹1,00,000)
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Utilized Coverage Pool (₹) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">₹</span>
                            <input type="text" name="utilized_coverage_pool" required pattern="^[0-9]+(?:\.[0-9]{1,2})?$"
                                class="w-full border border-gray-300 rounded-lg px-8 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent"
                                placeholder="0.00" maxlength="15" oninput="formatCurrency(this)">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Enter amount in Indian Rupees (e.g., 0.00 for no utilization)
                        </p>
                    </div>
                </div>

                <!-- Document Upload Section -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Documents</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Policy Document *</label>
                            <input type="file" name="documents[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max
                                10MB)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supporting Documents
                                (Optional)</label>
                            <input type="file" name="supporting_documents[]" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Upload additional supporting documents if needed. Max 10MB
                                each.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <a href="{{ route('policies.index') }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Cancel
                    </a>
                    <button type="submit" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                        <i class="fas fa-save mr-2"></i>Create Policy
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Currency formatting function
            function formatCurrency(input) {
                let value = input.value.replace(/[^0-9.]/g, '');
                let parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                if (parts[1] && parts[1].length > 2) {
                    value = parts[0] + '.' + parts[1].substring(0, 2);
                }
                input.value = value;
            }

            // Phone number formatting function
            function formatPhone(input) {
                let value = input.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                input.value = value;
            }

            // Real-time validation functions
            function validateEmail(input) {
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                const isValid = emailRegex.test(input.value);
                toggleFieldValidation(input, isValid, 'Please enter a valid email address');
                return isValid;
            }

            function validatePhone(input) {
                const phoneRegex = /^\+91[0-9]{10}$/;
                const value = input.value;
                let isValid = false;
                let message = 'Please enter 10 digits';

                if (value.startsWith('+91') && phoneRegex.test(value)) {
                    isValid = true;
                } else if (value.length === 10 && /^[0-9]{10}$/.test(value)) {
                    input.value = '+91' + value;
                    isValid = true;
                } else if (value.length < 10) {
                    message = 'Phone number must be 10 digits';
                } else if (!value.startsWith('+91')) {
                    message = 'Phone number must start with +91';
                }

                toggleFieldValidation(input, isValid, message);
                return isValid;
            }

            function validatePolicyNumber(input) {
                const policyRegex = /^[A-Z0-9\-]{5,20}$/;
                const isValid = policyRegex.test(input.value);
                toggleFieldValidation(input, isValid, 'Policy number must be 5-20 characters (A-Z, 0-9, - only)');
                return isValid;
            }

            function validateCurrency(input) {
                const currencyRegex = /^[0-9]+(?:\.[0-9]{1,2})?$/;
                const isValid = currencyRegex.test(input.value) && parseFloat(input.value) >= 0;
                toggleFieldValidation(input, isValid, 'Please enter a valid amount (0 or greater)');
                return isValid;
            }

            function toggleFieldValidation(input, isValid, message) {
                const field = input.closest('div');
                let errorDiv = field.querySelector('.field-error');

                if (!isValid && input.value) {
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'field-error text-red-500 text-xs mt-1';
                        field.appendChild(errorDiv);
                    }
                    errorDiv.textContent = message;
                    input.classList.add('border-red-500');
                    input.classList.remove('border-green-500');
                } else if (isValid && input.value) {
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                    input.classList.remove('border-red-500');
                    input.classList.add('border-green-500');
                } else {
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                    input.classList.remove('border-red-500', 'border-green-500');
                }
            }

            // Form validation before submission
            function validateForm() {
                let isValid = true;

                // Validate required fields
                const requiredFields = document.querySelectorAll('input[required], select[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('border-red-500');
                        isValid = false;
                    }
                });

                // Validate policy number
                const policyField = document.querySelector('input[name="policy_number"]');
                if (policyField) {
                    if (!validatePolicyNumber(policyField)) isValid = false;
                }

                // Validate currency fields
                const startingCoveragePoolField = document.querySelector('input[name="starting_coverage_pool"]');
                if (startingCoveragePoolField) {
                    if (!validateCurrency(startingCoveragePoolField)) isValid = false;
                }

                const availableCoveragePoolField = document.querySelector('input[name="available_coverage_pool"]');
                if (availableCoveragePoolField) {
                    if (!validateCurrency(availableCoveragePoolField)) isValid = false;
                }

                const utilizedCoveragePoolField = document.querySelector('input[name="utilized_coverage_pool"]');
                if (utilizedCoveragePoolField) {
                    if (!validateCurrency(utilizedCoveragePoolField)) isValid = false;
                }

                return isValid;
            }

            // Add event listeners for real-time validation
            document.addEventListener('DOMContentLoaded', function() {
                // Policy number validation
                const policyFields = document.querySelectorAll('input[name="policy_number"]');
                policyFields.forEach(field => {
                    field.addEventListener('input', function() {
                        this.value = this.value.toUpperCase();
                        if (this.classList.contains('border-red-500')) {
                            validatePolicyNumber(this);
                        }
                    });
                    field.addEventListener('blur', function() {
                        validatePolicyNumber(this);
                    });
                });

                // Currency validation
                const currencyFields = document.querySelectorAll('input[pattern*="[0-9]"]');
                currencyFields.forEach(field => {
                    field.addEventListener('input', function() {
                        if (this.name === 'starting_coverage_pool' || this.name ===
                            'available_coverage_pool' || this.name === 'utilized_coverage_pool') {
                            formatCurrency(this);
                        }
                        if (this.classList.contains('border-red-500')) {
                            validateCurrency(this);
                        }
                    });
                    field.addEventListener('blur', function() {
                        if (this.name === 'starting_coverage_pool' || this.name ===
                            'available_coverage_pool' || this.name === 'utilized_coverage_pool') {
                            validateCurrency(this);
                        }
                    });
                });

                // Form submission validation
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        if (!validateForm()) {
                            e.preventDefault();
                            alert('Please correct the highlighted errors before submitting');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
