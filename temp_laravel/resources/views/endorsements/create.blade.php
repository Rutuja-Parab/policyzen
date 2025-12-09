@extends('layouts.app')

@section('title', 'Create Endorsement - PolicyZen')

@section('page-title')
<div class="flex items-center">
    <a href="{{ route('endorsements.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Create Endorsement</span>
</div>
@endsection

@section('content')
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
        <form method="POST" action="{{ route('endorsements.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Policy *</label>
                    <select name="policy_id" id="policy-select" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select a policy</option>
                        @foreach($policies as $policy)
                        <option value="{{ $policy->id }}">{{ $policy->policy_number }} - {{ $policy->provider }}</option>
                        @endforeach
                    </select>
                    <div id="policy-info" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="policy-entity-count">0</span> entities will be auto-selected from this policy
                        </p>
                    </div>
                    
                    <!-- Debug/Test Section -->
                    <div class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <button type="button" onclick="testAutoSelection()" class="text-xs text-gray-600 hover:text-gray-800">
                            <i class="fas fa-bug mr-1"></i>Test Auto-Selection
                        </button>
                        <span id="debug-status" class="ml-2 text-xs text-gray-500"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endorsement Number *</label>
                    <input type="text" name="endorsement_number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="END001">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" required rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Describe the endorsement changes..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Effective Date *</label>
                    <input type="date" name="effective_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Associated Entities (Optional)</label>
                    <p class="text-xs text-gray-500 mb-3">Select entities associated with this endorsement. Use search and filters to manage large lists.</p>
                    
                    <!-- Search and Filter Controls -->
                    <div class="mb-4 space-y-3">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <input type="text" id="endorsement-create-search" placeholder="Search entities by name..." 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select id="endorsement-create-type-filter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                                <button type="button" onclick="endorsementCreateSelectAll()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    <i class="fas fa-check-square mr-1"></i>Select All
                                </button>
                                <button type="button" onclick="endorsementCreateDeselectAll()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                    <i class="fas fa-square mr-1"></i>Deselect All
                                </button>
                                <button type="button" onclick="endorsementCreateSelectByType()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    <i class="fas fa-filter mr-1"></i>Select by Type
                                </button>
                            </div>
                            <div class="text-sm font-medium text-gray-700">
                                <span id="endorsement-create-selected-count">0</span> selected
                            </div>
                        </div>
                    </div>

                    <!-- Entity Selection Area -->
                    <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto">
                        <div id="endorsement-create-entity-list" class="space-y-4">
                            @php
                            $groupedEntities = $entities->groupBy('type');
                            @endphp
                            
                            @foreach($groupedEntities as $type => $typeEntities)
                            <div class="endorsement-create-entity-group" data-entity-type="{{ $type }}">
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
                                    <button type="button" onclick="endorsementCreateSelectType('{{ $type }}')" class="text-xs text-blue-600 hover:text-blue-800">
                                        Select All
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 endorsement-create-entity-items">
                                    @foreach($typeEntities as $entity)
                                    <label class="endorsement-create-entity-item flex items-center space-x-2 p-2 rounded hover:bg-white cursor-pointer" data-entity-name="{{ strtolower($entity->description) }}">
                                        <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" 
                                               class="endorsement-create-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                               onchange="updateEndorsementCreateSelectedCount()">
                                        <span class="text-sm text-gray-700">{{ $entity->description }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div id="endorsement-create-no-results" class="hidden text-center py-8">
                            <i class="fas fa-search text-4xl text-gray-300 mb-2"></i>
                            <p class="text-gray-500">No entities found matching your search</p>
                        </div>
                    </div>
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
                            <option value="ENDORSEMENT_DOCUMENT">Endorsement Document</option>
                            <option value="POLICY_DOCUMENT">Policy Document</option>
                            <option value="FINANCIAL_DOCUMENT">Financial Document</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('endorsements.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Endorsement
                    @if($entities->count() > 0)
                    <span class="ml-2 text-sm opacity-75">(<span id="submit-entity-count">0</span> entities)</span>
                    @endif
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Track originally selected entities from policy
    let policyEntities = new Set();
    
    // Handle policy selection change
    document.getElementById('policy-select')?.addEventListener('change', function(e) {
        const policyId = e.target.value;
        const policyInfo = document.getElementById('policy-info');
        const policyEntityCount = document.getElementById('policy-entity-count');
        
        console.log('🔍 Policy selection changed:', policyId);
        
        if (policyId) {
            // Clear previous selections
            endorsementCreateDeselectAll();
            policyEntities.clear();
            
            // Show loading state
            policyEntityCount.textContent = 'Loading...';
            policyInfo.classList.remove('hidden');
            
            // Try API first, fallback to mock data
            console.log('🌐 Making API call to:', `/policies/${policyId}/entities`);
            
            fetch(`/policies/${policyId}/entities`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    console.log('📡 API Response status:', response.status);
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            console.error('❌ API Error:', errorData);
                            throw new Error(`HTTP error! status: ${response.status} - ${JSON.stringify(errorData)}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('✅ API Response data:', data);
                    
                    if (data.error) {
                        console.error('❌ API returned error:', data.error);
                        throw new Error(data.error);
                    }
                    
                    console.log(`📊 API returned ${data.entities?.length || 0} entities for policy ${data.policy_number}`);
                    
                    if (data.debug_info) {
                        console.log('🔍 Debug info:', data.debug_info);
                        console.log(`🎯 Auto-selection: ${data.debug_info.active_count} active entities out of ${data.debug_info.total_attached} total`);
                    }
                    
                    if (data.entities?.length > 0) {
                        console.log('📋 Active entities to auto-select:');
                        data.entities.forEach((entity, index) => {
                            console.log(`  ${index + 1}. ${entity.description} (ID: ${entity.id})`);
                        });
                    }
                    
                    processPolicyEntities(data.entities, policyEntityCount, policyInfo);
                })
                .catch(error => {
                    console.warn('⚠️ API failed, using fallback method:', error.message);
                    policyEntityCount.textContent = 'API Error';
                    
                    // Fallback: Try to get entities from a different approach
                    getEntitiesFromPolicyFallback(policyId, policyEntityCount, policyInfo);
                });
        } else {
            // Clear selections when no policy selected
            endorsementCreateDeselectAll();
            policyEntities.clear();
            policyInfo.classList.add('hidden');
        }
    });

    // Process policy entities (main function)
    function processPolicyEntities(entities, policyEntityCount, policyInfo) {
        let autoSelectedCount = 0;
        
        // Auto-select entities from the policy
        entities.forEach(entity => {
            const checkbox = document.querySelector(`input[name="entity_ids[]"][value="${entity.id}"]`);
            console.log('🔍 Looking for entity:', entity.id, 'Found checkbox:', !!checkbox);
            
            if (checkbox) {
                checkbox.checked = true;
                policyEntities.add(entity.id);
                autoSelectedCount++;
                console.log('✅ Auto-selected entity:', entity.description);
            } else {
                console.log('❌ Entity checkbox not found for:', entity.id);
            }
        });
        
        // Show policy info
        if (autoSelectedCount > 0) {
            policyEntityCount.textContent = autoSelectedCount;
            policyInfo.classList.remove('hidden');
            console.log(`🎯 Successfully auto-selected ${autoSelectedCount} entities`);
        } else {
            policyInfo.classList.add('hidden');
            console.log('ℹ️ No entities found to auto-select');
        }
        
        updateEndorsementCreateSelectedCount();
    }

    // Fallback method - get entities from a different source
    function getEntitiesFromPolicyFallback(policyId, policyEntityCount, policyInfo) {
        // This could be enhanced to get data from a different source
        // For now, we'll just show a message
        policyEntityCount.textContent = 'API unavailable';
        policyInfo.classList.add('hidden');
        
        // Try to manually trigger selection for testing
        setTimeout(() => {
            console.log('🧪 Testing with ALL entities...');
            const testCheckboxes = document.querySelectorAll('.endorsement-create-checkbox');
            let testCount = 0;
            
            testCheckboxes.forEach((checkbox, index) => {
                checkbox.checked = true;
                policyEntities.add(parseInt(checkbox.value));
                testCount++;
                console.log(`🧪 Test selected entity ${index + 1}:`, checkbox.value);
            });
            
            if (testCount > 0) {
                policyEntityCount.textContent = `Test: ${testCount} entities`;
                policyInfo.classList.remove('hidden');
                updateEndorsementCreateSelectedCount();
                console.log(`🧪 Test selection: ${testCount} entities`);
            }
        }, 500);
    }
    
    // Track entity selection changes and handle termination logic
    document.addEventListener('change', function(e) {
        if (e.target.name === 'entity_ids[]') {
            const entityId = parseInt(e.target.value);
            const isChecked = e.target.checked;
            
            // If an entity was previously auto-selected from policy and is now deselected
            if (policyEntities.has(entityId) && !isChecked) {
                // Mark for termination - you can add visual indication here
                const label = e.target.closest('label');
                label.classList.add('border-red-300', 'bg-red-50');
                label.title = 'This entity will be terminated from the policy';
            } else {
                // Remove termination marking
                const label = e.target.closest('label');
                label.classList.remove('border-red-300', 'bg-red-50');
                label.title = '';
            }
            
            updateEndorsementCreateSelectedCount();
        }
    });

    // Search functionality
    document.getElementById('endorsement-create-search')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const entityItems = document.querySelectorAll('.endorsement-create-entity-item');
        let visibleCount = 0;
        
        entityItems.forEach(item => {
            const entityName = item.getAttribute('data-entity-name');
            const parentGroup = item.closest('.endorsement-create-entity-group');
            
            if (entityName.includes(searchTerm)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });
        
        // Hide/show groups with no visible items
        document.querySelectorAll('.endorsement-create-entity-group').forEach(group => {
            const visibleItems = group.querySelectorAll('.endorsement-create-entity-item:not(.hidden)');
            group.style.display = visibleItems.length > 0 ? 'block' : 'none';
        });
        
        // Show/hide no results message
        document.getElementById('endorsement-create-no-results').classList.toggle('hidden', visibleCount > 0);
    });
    
    // Filter by type
    document.getElementById('endorsement-create-type-filter')?.addEventListener('change', function(e) {
        const selectedType = e.target.value;
        const groups = document.querySelectorAll('.endorsement-create-entity-group');
        
        groups.forEach(group => {
            if (selectedType === 'ALL' || group.getAttribute('data-entity-type') === selectedType) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    });
    
    // Select all entities
    function endorsementCreateSelectAll() {
        document.querySelectorAll('.endorsement-create-checkbox:not(:disabled)').forEach(checkbox => {
            if (!checkbox.closest('.endorsement-create-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateEndorsementCreateSelectedCount();
    }
    
    // Deselect all entities
    function endorsementCreateDeselectAll() {
        document.querySelectorAll('.endorsement-create-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateEndorsementCreateSelectedCount();
    }
    
    // Select by type
    function endorsementCreateSelectByType() {
        const selectedType = document.getElementById('endorsement-create-type-filter').value;
        if (selectedType === 'ALL') {
            endorsementCreateSelectAll();
        } else {
            document.querySelectorAll(`.endorsement-create-entity-group[data-entity-type="${selectedType}"] .endorsement-create-checkbox:not(:disabled)`).forEach(checkbox => {
                if (!checkbox.closest('.endorsement-create-entity-item').classList.contains('hidden')) {
                    checkbox.checked = true;
                }
            });
            updateEndorsementCreateSelectedCount();
        }
    }
    
    // Select all in a specific type group
    function endorsementCreateSelectType(type) {
        document.querySelectorAll(`.endorsement-create-entity-group[data-entity-type="${type}"] .endorsement-create-checkbox:not(:disabled)`).forEach(checkbox => {
            if (!checkbox.closest('.endorsement-create-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateEndorsementCreateSelectedCount();
    }
    
    // Update selected count
    function updateEndorsementCreateSelectedCount() {
        const checked = document.querySelectorAll('.endorsement-create-checkbox:checked').length;
        document.getElementById('endorsement-create-selected-count').textContent = checked;
        document.getElementById('submit-entity-count').textContent = checked;
    }
    
    // Test auto-selection function
    function testAutoSelection() {
        const debugStatus = document.getElementById('debug-status');
        debugStatus.textContent = 'Testing ALL entities...';
        
        console.log('🧪 Starting auto-selection test - selecting ALL entities');
        
        // Clear current selections
        endorsementCreateDeselectAll();
        policyEntities.clear();
        
        // Get all entity checkboxes
        const allCheckboxes = document.querySelectorAll('.endorsement-create-checkbox');
        console.log('📋 Found total checkboxes:', allCheckboxes.length);
        
        if (allCheckboxes.length === 0) {
            debugStatus.textContent = 'No entities found!';
            console.error('❌ No entity checkboxes found on page');
            return;
        }
        
        // Select ALL entities as test
        let selectedCount = 0;
        allCheckboxes.forEach((checkbox, index) => {
            checkbox.checked = true;
            policyEntities.add(parseInt(checkbox.value));
            selectedCount++;
            console.log(`✅ Test selected entity ${index + 1}:`, checkbox.value, '-', checkbox.nextElementSibling.textContent);
        });
        
        updateEndorsementCreateSelectedCount();
        debugStatus.textContent = `Test: ${selectedCount} entities selected`;
        
        console.log(`🧪 Test completed: ${selectedCount} entities auto-selected`);
    }

    // Initialize count on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Page loaded, initializing endorsement creation form');
        
        // Log available checkboxes
        const checkboxes = document.querySelectorAll('.endorsement-create-checkbox');
        console.log(`📋 Found ${checkboxes.length} entity checkboxes on page load`);
        
        checkboxes.forEach((checkbox, index) => {
            console.log(`Checkbox ${index + 1}:`, {
                value: checkbox.value,
                name: checkbox.name,
                type: checkbox.type,
                checked: checkbox.checked
            });
        });
        
        updateEndorsementCreateSelectedCount();
    });
</script>
@endpush
@endsection
