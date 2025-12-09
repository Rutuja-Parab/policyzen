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
                @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach($errors->all() as $error)
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
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Covered Entities *</label>
                                <p class="text-xs text-gray-500 mb-3">Select entities to cover under this group policy. Use search and filters to manage large lists.</p>
                                
                                <!-- Search and Filter Controls -->
                                <div class="mb-4 space-y-3">
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <div class="flex-1 relative">
                                            <input type="text" id="entity-search" placeholder="Search entities by name..." 
                                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                        </div>
                                        <select id="entity-type-filter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="ALL">All Types</option>
                                            <option value="EMPLOYEE">Employees</option>
                                            <option value="STUDENT">Students</option>
                                            <option value="VEHICLE">Vehicles</option>
                                            <option value="SHIP">Vessels</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Bulk Actions -->
                                    <div class="flex items-center justify-between bg-blue-50 p-3 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <button type="button" onclick="selectAllEntities()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fas fa-check-square mr-1"></i>Select All
                                            </button>
                                            <button type="button" onclick="deselectAllEntities()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                                <i class="fas fa-square mr-1"></i>Deselect All
                                            </button>
                                            <button type="button" onclick="selectByType()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fas fa-filter mr-1"></i>Select by Type
                                            </button>
                                        </div>
                                        <div class="text-sm font-medium text-gray-700">
                                            <span id="selected-count">0</span> selected
                                        </div>
                                    </div>
                                </div>

                                <!-- Entity Selection Area -->
                                <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto">
                                    <div id="entity-list" class="space-y-4">
                                        @php
                                        $groupedEntities = $entities->groupBy('type');
                                        @endphp
                                        
                                        @foreach($groupedEntities as $type => $typeEntities)
                                        <div class="entity-group" data-entity-type="{{ $type }}">
                                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                                                <h4 class="font-medium text-gray-900 flex items-center">
                                                    @if($type === 'EMPLOYEE')
                                                    <i class="fas fa-user mr-2 text-blue-600"></i>
                                                    @elseif($type === 'STUDENT')
                                                    <i class="fas fa-graduation-cap mr-2 text-green-600"></i>
                                                    @elseif($type === 'COMPANY')
                                                    <i class="fas fa-building mr-2 text-purple-600"></i>
                                                    @elseif($type === 'COURSE')
                                                    <i class="fas fa-book mr-2 text-orange-600"></i>
                                                    @elseif($type === 'VEHICLE')
                                                    <i class="fas fa-car mr-2 text-indigo-600"></i>
                                                    @elseif($type === 'SHIP')
                                                    <i class="fas fa-ship mr-2 text-teal-600"></i>
                                                    @endif
                                                    {{ $type }} <span class="ml-2 text-sm text-gray-500">({{ $typeEntities->count() }})</span>
                                                </h4>
                                                <button type="button" onclick="selectType('{{ $type }}')" class="text-xs text-blue-600 hover:text-blue-800">
                                                    Select All
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 entity-items">
                                                @foreach($typeEntities as $entity)
                                                <label class="entity-item flex items-center space-x-2 p-2 rounded hover:bg-white cursor-pointer" data-entity-name="{{ strtolower($entity->description) }}">
                                                    <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" 
                                                           class="entity-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                           onchange="updateSelectedCount()">
                                                    <span class="text-sm text-gray-700">{{ $entity->description }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    
                                    <div id="no-results" class="hidden text-center py-8">
                                        <i class="fas fa-search text-4xl text-gray-300 mb-2"></i>
                                        <p class="text-gray-500">No entities found matching your search</p>
                                    </div>
                                </div>
                            </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Policy Number *</label>
                                    <input type="text" name="policy_number" required 
                                           pattern="^[A-Z0-9\-]{5,20}$"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="POL-2025-001">
                                    <p class="text-xs text-gray-500 mt-1">5-20 characters, uppercase letters, numbers and hyphens only</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Insurance Type *</label>
                                    <select name="insurance_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="HEALTH">Health Insurance</option>
                                        <option value="ACCIDENT">Accident Insurance</option>
                                        <option value="PROPERTY">Property Insurance</option>
                                        <option value="VEHICLE">Vehicle Insurance</option>
                                        <option value="MARINE">Marine Insurance</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Provider *</label>
                                    <input type="text" name="provider" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                                    <input type="date" name="start_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                                    <input type="date" name="end_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sum Insured (₹) *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">₹</span>
                                        <input type="text" name="sum_insured" required 
                                               pattern="^[0-9]+(?:\.[0-9]{1,2})?$"
                                               class="w-full border border-gray-300 rounded-lg px-8 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="100000.00"
                                               maxlength="15"
                                               oninput="formatCurrency(this)">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Enter amount in Indian Rupees (e.g., 100000.00 for ₹1,00,000)</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Premium Amount (₹) *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">₹</span>
                                        <input type="text" name="premium_amount" required 
                                               pattern="^[0-9]+(?:\.[0-9]{1,2})?$"
                                               class="w-full border border-gray-300 rounded-lg px-8 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="5000.00"
                                               maxlength="10"
                                               oninput="formatCurrency(this)">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Enter amount in Indian Rupees (e.g., 5000.00 for ₹5,000)</p>
                                </div>
                            </div>

                            <!-- Document Upload Section -->
                            <div class="mt-8 border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Documents</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Files</label>
                                        <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each)</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                                        <select name="document_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="POLICY_DOCUMENT">Policy Document</option>
                                            <option value="FINANCIAL_DOCUMENT">Financial Document</option>
                                            <option value="OTHER">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-4">
                                <a href="{{ route('policies.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-save mr-2"></i>Create Policy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

@push('scripts')
<script>
// Search functionality
document.getElementById('entity-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const entityItems = document.querySelectorAll('.entity-item');
    let visibleCount = 0;
    
    entityItems.forEach(item => {
        const entityName = item.getAttribute('data-entity-name');
        const parentGroup = item.closest('.entity-group');
        
        if (entityName.includes(searchTerm)) {
            item.classList.remove('hidden');
            visibleCount++;
        } else {
            item.classList.add('hidden');
        }
    });
    
    // Hide/show groups with no visible items
    document.querySelectorAll('.entity-group').forEach(group => {
        const visibleItems = group.querySelectorAll('.entity-item:not(.hidden)');
        group.style.display = visibleItems.length > 0 ? 'block' : 'none';
    });
    
    // Show/hide no results message
    document.getElementById('no-results').classList.toggle('hidden', visibleCount > 0);
});

// Filter by type
document.getElementById('entity-type-filter').addEventListener('change', function(e) {
    const selectedType = e.target.value;
    const groups = document.querySelectorAll('.entity-group');
    
    groups.forEach(group => {
        if (selectedType === 'ALL' || group.getAttribute('data-entity-type') === selectedType) {
            group.style.display = 'block';
        } else {
            group.style.display = 'none';
        }
    });
});

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
    const isValid = currencyRegex.test(input.value) && parseFloat(input.value) > 0;
    toggleFieldValidation(input, isValid, 'Please enter a valid amount greater than 0');
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
    
    // Validate email
    const emailField = document.querySelector('input[name="email"]');
    if (emailField && emailField.value) {
        if (!validateEmail(emailField)) isValid = false;
    }
    
    // Validate phone
    const phoneField = document.querySelector('input[name="phone"]');
    if (phoneField && phoneField.value) {
        if (!validatePhone(phoneField)) isValid = false;
    }
    
    // Validate policy number
    const policyField = document.querySelector('input[name="policy_number"]');
    if (policyField) {
        if (!validatePolicyNumber(policyField)) isValid = false;
    }
    
    // Validate currency fields
    const sumInsuredField = document.querySelector('input[name="sum_insured"]');
    if (sumInsuredField) {
        if (!validateCurrency(sumInsuredField)) isValid = false;
    }
    
    const premiumField = document.querySelector('input[name="premium_amount"]');
    if (premiumField) {
        if (!validateCurrency(premiumField)) isValid = false;
    }
    
    // Validate entity selection
    const entityCheckboxes = document.querySelectorAll('.entity-checkbox:checked');
    if (entityCheckboxes.length === 0) {
        alert('Please select at least one entity to cover under this policy');
        isValid = false;
    }
    
    return isValid;
}

// Add event listeners for real-time validation
document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value) validateEmail(this);
        });
        field.addEventListener('input', function() {
            if (this.classList.contains('border-red-500')) {
                validateEmail(this);
            }
        });
    });
    
    // Phone validation
    const phoneFields = document.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        field.addEventListener('input', function() {
            formatPhone(this);
            if (this.classList.contains('border-red-500') || this.value.startsWith('+91')) {
                validatePhone(this);
            }
        });
        field.addEventListener('blur', function() {
            if (this.value && !this.value.startsWith('+91')) {
                this.value = '+91' + this.value;
            }
            validatePhone(this);
        });
    });
    
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
            if (this.name === 'sum_insured' || this.name === 'premium_amount') {
                formatCurrency(this);
            }
            if (this.classList.contains('border-red-500')) {
                validateCurrency(this);
            }
        });
        field.addEventListener('blur', function() {
            if (this.name === 'sum_insured' || this.name === 'premium_amount') {
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
    
    updateSelectedCount();
});

// Entity selection functions
function selectAllEntities() {
    document.querySelectorAll('.entity-checkbox:not(:disabled)').forEach(checkbox => {
        if (!checkbox.closest('.entity-item').classList.contains('hidden')) {
            checkbox.checked = true;
        }
    });
    updateSelectedCount();
}

function deselectAllEntities() {
    document.querySelectorAll('.entity-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

function selectByType() {
    const selectedType = document.getElementById('entity-type-filter').value;
    if (selectedType === 'ALL') {
        selectAllEntities();
    } else {
        document.querySelectorAll(`.entity-group[data-entity-type="${selectedType}"] .entity-checkbox:not(:disabled)`).forEach(checkbox => {
            if (!checkbox.closest('.entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateSelectedCount();
    }
}

function selectType(type) {
    document.querySelectorAll(`.entity-group[data-entity-type="${type}"] .entity-checkbox:not(:disabled)`).forEach(checkbox => {
        if (!checkbox.closest('.entity-item').classList.contains('hidden')) {
            checkbox.checked = true;
        }
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.entity-checkbox:checked').length;
    document.getElementById('selected-count').textContent = checked;
}
</script>
@endpush
@endsection