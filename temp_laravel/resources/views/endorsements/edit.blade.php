@extends('layouts.app')

@section('title', 'Edit Endorsement - PolicyZen')

@section('page-title')
<div class="flex items-center">
    <a href="{{ route('endorsements.show', $endorsement) }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Edit Endorsement</span>
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
        <form method="POST" action="{{ route('endorsements.update', $endorsement) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Policy *</label>
                    <select name="policy_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <option value="">Select Policy</option>
                        @foreach($policies as $policy)
                        <option value="{{ $policy->id }}" {{ old('policy_id', $endorsement->policy_id) == $policy->id ? 'selected' : '' }}>
                            {{ $policy->policy_number }} - {{ $policy->insurance_type }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endorsement Number *</label>
                    <input type="text" name="endorsement_number" value="{{ old('endorsement_number', $endorsement->endorsement_number) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">{{ old('description', $endorsement->description) }}</textarea>
                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Effective Date *</label>
                                    <input type="date" name="effective_date" value="{{ old('effective_date', $endorsement->effective_date ? $endorsement->effective_date->format('Y-m-d') : '') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Associated Entities</label>
                                    <p class="text-xs text-gray-500 mb-3">Select entities associated with this endorsement. Use search and filters for easier management.</p>
                                    
                                    <!-- Search and Filter Controls -->
                                    <div class="mb-4 space-y-3">
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <div class="flex-1 relative">
                                                <input type="text" id="edit-entity-search" placeholder="Search entities..." 
                                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                            </div>
                                            <select id="edit-entity-type-filter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
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
                                                <button type="button" onclick="editSelectAll()" class="text-sm text-[#f06e11] hover:text-blue-800 font-medium">
                                                    <i class="fas fa-check-square mr-1"></i>Select All
                                                </button>
                                                <button type="button" onclick="editDeselectAll()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                                    <i class="fas fa-square mr-1"></i>Deselect All
                                                </button>
                                            </div>
                                            <div class="text-sm font-medium text-gray-700">
                                                <span id="edit-selected-count">0</span> selected
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto">
                                        @php
                                        $groupedEntities = $entities->groupBy('type');
                                        $selectedIds = $endorsement->entities->pluck('id')->toArray();
                                        @endphp
                                        <div id="edit-entity-list" class="space-y-4">
                                            @foreach($groupedEntities as $type => $typeEntities)
                                            <div class="edit-entity-group" data-entity-type="{{ $type }}">
                                                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                                                    <h4 class="font-medium text-gray-900 flex items-center">
                                                        @if($type === 'EMPLOYEE')
                                                        <i class="fas fa-user mr-2 text-[#f06e11]"></i>
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
                                                    <button type="button" onclick="editSelectType('{{ $type }}')" class="text-xs text-[#f06e11] hover:text-blue-800">
                                                        Select All
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 edit-entity-items">
                                                    @foreach($typeEntities as $entity)
                                                    <label class="edit-entity-item flex items-center space-x-2 p-2 rounded hover:bg-white cursor-pointer" data-entity-name="{{ strtolower($entity->description) }}">
                                                        <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" 
                                                               {{ in_array($entity->id, old('entity_ids', $selectedIds)) ? 'checked' : '' }}
                                                               class="edit-entity-checkbox rounded border-gray-300 text-[#f06e11] focus:ring-[#2b8bd0]"
                                                               onchange="updateEditSelectedCount()">
                                                        <span class="text-sm text-gray-700">{{ $entity->description }}</span>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div id="edit-no-results" class="hidden text-center py-8">
                                            <i class="fas fa-search text-4xl text-gray-300 mb-2"></i>
                                            <p class="text-gray-500">No entities found matching your search</p>
                                        </div>
                                    </div>
                                </div>
            </div>

            <!-- Document Upload Section -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Additional Documents</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Files</label>
                        <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                        <select name="document_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                            <option value="ENDORSEMENT_DOCUMENT">Endorsement Document</option>
                            <option value="POLICY_DOCUMENT">Policy Document</option>
                            <option value="FINANCIAL_DOCUMENT">Financial Document</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('endorsements.show', $endorsement) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                    <i class="fas fa-save mr-2"></i>Update Endorsement
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Edit view search functionality
    document.getElementById('edit-entity-search')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const entityItems = document.querySelectorAll('.edit-entity-item');
        let visibleCount = 0;
        
        entityItems.forEach(item => {
            const entityName = item.getAttribute('data-entity-name');
            const parentGroup = item.closest('.edit-entity-group');
            
            if (entityName.includes(searchTerm)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });
        
        document.querySelectorAll('.edit-entity-group').forEach(group => {
            const visibleItems = group.querySelectorAll('.edit-entity-item:not(.hidden)');
            group.style.display = visibleItems.length > 0 ? 'block' : 'none';
        });
        
        document.getElementById('edit-no-results').classList.toggle('hidden', visibleCount > 0);
    });
    
    // Edit view filter by type
    document.getElementById('edit-entity-type-filter')?.addEventListener('change', function(e) {
        const selectedType = e.target.value;
        const groups = document.querySelectorAll('.edit-entity-group');
        
        groups.forEach(group => {
            if (selectedType === 'ALL' || group.getAttribute('data-entity-type') === selectedType) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    });
    
    function editSelectAll() {
        document.querySelectorAll('.edit-entity-checkbox:not(:disabled)').forEach(checkbox => {
            if (!checkbox.closest('.edit-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateEditSelectedCount();
    }
    
    function editDeselectAll() {
        document.querySelectorAll('.edit-entity-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateEditSelectedCount();
    }
    
    function editSelectType(type) {
        document.querySelectorAll(`.edit-entity-group[data-entity-type="${type}"] .edit-entity-checkbox:not(:disabled)`).forEach(checkbox => {
            if (!checkbox.closest('.edit-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateEditSelectedCount();
    }
    
    function updateEditSelectedCount() {
        const checked = document.querySelectorAll('.edit-entity-checkbox:checked').length;
        document.getElementById('edit-selected-count').textContent = checked;
    }
    
    // Initialize count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateEditSelectedCount();
    });
</script>
@endpush
@endsection

