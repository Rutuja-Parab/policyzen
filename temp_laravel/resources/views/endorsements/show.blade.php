@extends('layouts.app')

@section('title', 'Endorsement Details - PolicyZen')

@section('page-title')
<div class="flex items-center">
    <a href="{{ route('endorsements.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Endorsement Details</span>
</div>
@endsection

@section('header-actions')
<a href="{{ route('endorsements.edit', $endorsement) }}" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
    <i class="fas fa-edit mr-2"></i>Edit Endorsement
</a>
@endsection

@section('content')
<!-- Associated Entities Management -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Associated Entities</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $endorsement->entities->count() }} entity(ies) currently associated</p>
            </div>
            <button onclick="document.getElementById('manage-entities-modal').classList.remove('hidden')"
                    class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                <i class="fas fa-edit mr-2"></i>Manage Entities
            </button>
        </div>
    </div>

    <div class="p-6">
        @if($endorsement->entities->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($endorsement->entities as $entity)
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3 flex-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                            @if($entity->type === 'EMPLOYEE') bg-blue-100 text-[#f06e11]
                            @elseif($entity->type === 'STUDENT') bg-green-100 text-green-600
                            @elseif($entity->type === 'COMPANY') bg-purple-100 text-purple-600
                            @elseif($entity->type === 'COURSE') bg-orange-100 text-orange-600
                            @elseif($entity->type === 'VEHICLE') bg-indigo-100 text-indigo-600
                            @elseif($entity->type === 'SHIP') bg-teal-100 text-teal-600
                            @else bg-gray-100 text-gray-600 @endif">
                            @if($entity->type === 'EMPLOYEE')
                            <i class="fas fa-user"></i>
                            @elseif($entity->type === 'STUDENT')
                            <i class="fas fa-graduation-cap"></i>
                            @elseif($entity->type === 'COMPANY')
                            <i class="fas fa-building"></i>
                            @elseif($entity->type === 'COURSE')
                            <i class="fas fa-book"></i>
                            @elseif($entity->type === 'VEHICLE')
                            <i class="fas fa-car"></i>
                            @elseif($entity->type === 'SHIP')
                            <i class="fas fa-ship"></i>
                            @else
                            <i class="fas fa-question"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $entity->description }}</h3>
                            <p class="text-xs text-gray-500">{{ $entity->type }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('endorsements.remove-entity', $endorsement) }}" class="inline ml-2">
                        @csrf
                        <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm"
                                onclick="return confirm('Remove this entity from the endorsement?')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-users text-gray-300 text-3xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No entities associated</h3>
            <p class="text-gray-500 mb-4">Add entities to this endorsement.</p>
            <button onclick="document.getElementById('manage-entities-modal').classList.remove('hidden')"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i>Add Entities
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Documents Section -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Documents</h2>
            <button onclick="document.getElementById('upload-endorsement-document-modal').classList.remove('hidden')"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-upload mr-2"></i>Upload Document
            </button>
        </div>
    </div>

    <div class="p-6">
        @if($endorsement->documents->count() > 0)
        <div class="space-y-4">
            @foreach($endorsement->documents as $document)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-100 text-[#f06e11]">
                        <i class="fas fa-file"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">{{ $document->file_name }}</h3>
                        <p class="text-sm text-gray-600">{{ $document->document_type }} • {{ $document->file_type }} • {{ number_format($document->file_size ?? 0, 0) }} KB</p>
                        <p class="text-xs text-gray-500">Uploaded {{ $document->uploaded_at ? $document->uploaded_at->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="text-[#f06e11] hover:text-blue-800">
                        <i class="fas fa-download"></i>
                    </a>
                    <form method="POST" action="{{ route('documents.destroy', $document) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this document?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-file text-gray-300 text-3xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No documents uploaded</h3>
            <p class="text-gray-500 mb-4">Upload endorsement documents, financial records, or other related files.</p>
            <button onclick="document.getElementById('upload-endorsement-document-modal').classList.remove('hidden')"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-upload mr-2"></i>Upload First Document
            </button>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Endorsement Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Endorsement Information</h2>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Endorsement Number</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $endorsement->endorsement_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Policy</label>
                        <p class="text-gray-900">
                            <a href="{{ route('policies.show', $endorsement->policy) }}" class="text-[#f06e11] hover:underline">
                                {{ $endorsement->policy->policy_number }}
                            </a>
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <p class="text-gray-900">{{ $endorsement->description }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Effective Date</label>
                        <p class="text-gray-900">{{ $endorsement->effective_date ? $endorsement->effective_date->format('M d, Y') : 'N/A' }}</p>
                    </div>

                    @if($endorsement->creator)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Created By</label>
                        <p class="text-gray-900">{{ $endorsement->creator->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div>
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>

            <div class="p-6 space-y-4">
                <a href="{{ route('endorsements.edit', $endorsement) }}" class="w-full bg-[#f06e11] text-white px-4 py-3 rounded-lg hover:bg-[#f28e1f] flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>Edit Endorsement
                </a>

                <form method="POST" action="{{ route('endorsements.destroy', $endorsement) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this endorsement? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Delete Endorsement
                    </button>
                </form>
            </div>
        </div>

        <!-- Endorsement Stats -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Details</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Created</span>
                    <span class="font-medium">{{ $endorsement->created_at ? $endorsement->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Updated</span>
                    <span class="font-medium">{{ $endorsement->updated_at ? $endorsement->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manage Entities Modal -->
<div id="manage-entities-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-5 mx-auto p-5 border w-full max-w-6xl shadow-lg rounded-md bg-white my-8">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Manage Endorsement Entities</h3>
                    <p class="text-sm text-gray-500 mt-1">Remove existing entities or add new ones to this endorsement</p>
                </div>
                <button onclick="document.getElementById('manage-entities-modal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Current Entities (Remove) -->
                <div class="border border-gray-300 rounded-lg">
                    <div class="bg-red-50 border-b border-gray-300 p-4">
                        <h4 class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-minus-circle text-red-600 mr-2"></i>
                            Remove Entities
                            <span class="ml-2 text-sm text-gray-500">({{ $endorsement->entities->count() }} current)</span>
                        </h4>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto">
                        @if($endorsement->entities->count() > 0)
                        <form method="POST" action="{{ route('endorsements.remove-entity', $endorsement) }}" id="remove-entities-form">
                            @csrf
                            <div class="space-y-2" id="current-entities-list">
                                @foreach($endorsement->entities as $entity)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                    <div class="flex items-center space-x-3 flex-1">
                                        <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" 
                                               class="remove-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500"
                                               onchange="updateRemoveCount()">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                            @if($entity->type === 'EMPLOYEE') bg-blue-100 text-[#f06e11]
                                            @elseif($entity->type === 'STUDENT') bg-green-100 text-green-600
                                            @elseif($entity->type === 'COMPANY') bg-purple-100 text-purple-600
                                            @elseif($entity->type === 'COURSE') bg-orange-100 text-orange-600
                                            @elseif($entity->type === 'VEHICLE') bg-indigo-100 text-indigo-600
                                            @elseif($entity->type === 'SHIP') bg-teal-100 text-teal-600
                                            @else bg-gray-100 text-gray-600 @endif">
                                            @if($entity->type === 'EMPLOYEE')
                                            <i class="fas fa-user text-xs"></i>
                                            @elseif($entity->type === 'STUDENT')
                                            <i class="fas fa-graduation-cap text-xs"></i>
                                            @elseif($entity->type === 'COMPANY')
                                            <i class="fas fa-building text-xs"></i>
                                            @elseif($entity->type === 'COURSE')
                                            <i class="fas fa-book text-xs"></i>
                                            @elseif($entity->type === 'VEHICLE')
                                            <i class="fas fa-car text-xs"></i>
                                            @elseif($entity->type === 'SHIP')
                                            <i class="fas fa-ship text-xs"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $entity->description }}</p>
                                            <p class="text-xs text-gray-500">{{ $entity->type }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm text-gray-600">
                                        <span id="remove-count">0</span> selected for removal
                                    </span>
                                    <div class="space-x-2">
                                        <button type="button" onclick="selectAllRemove()" class="text-xs text-red-600 hover:text-red-800">Select All</button>
                                        <button type="button" onclick="deselectAllRemove()" class="text-xs text-gray-600 hover:text-gray-800">Deselect All</button>
                                    </div>
                                </div>
                                <button type="submit" id="remove-btn" disabled
                                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                        onclick="return confirmRemoveEntities()">
                                    <i class="fas fa-minus mr-2"></i>Remove Selected (<span id="remove-btn-count">0</span>)
                                </button>
                            </div>
                        </form>
                        @else
                        <p class="text-sm text-gray-500 text-center py-8">No entities to remove</p>
                        @endif
                    </div>
                </div>

                <!-- Add New Entities -->
                <div class="border border-gray-300 rounded-lg">
                    <div class="bg-green-50 border-b border-gray-300 p-4">
                        <h4 class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                            Add New Entities
                        </h4>
                    </div>
                    <div class="p-4">
                        <!-- Search and Filter -->
                        <div class="mb-4 space-y-2">
                            <div class="relative">
                                <input type="text" id="endorsement-entity-search" placeholder="Search entities..." 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select id="endorsement-entity-type-filter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="ALL">All Types</option>
                                <option value="EMPLOYEE">Employees</option>
                                <option value="STUDENT">Students</option>
                                <option value="COMPANY">Companies</option>
                                <option value="COURSE">Courses</option>
                                <option value="VEHICLE">Vehicles</option>
                                <option value="SHIP">Vessels</option>
                            </select>
                        </div>
                        
                        <form method="POST" action="{{ route('endorsements.add-entities', $endorsement) }}" id="add-entities-form">
                            @csrf
                            <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-gray-50 mb-4">
                                @php
                                $existingIds = $endorsement->entities->pluck('id')->toArray();
                                $availableEntities = $allEntities->reject(function($entity) use ($existingIds) {
                                    return in_array($entity->id, $existingIds);
                                });
                                $groupedEntities = $availableEntities->groupBy('type');
                                @endphp
                                
                                @if($availableEntities->count() > 0)
                                <div id="endorsement-entity-list" class="space-y-3">
                                    @foreach($groupedEntities as $type => $entities)
                                    <div class="endorsement-entity-group" data-entity-type="{{ $type }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="text-xs font-medium text-gray-700 flex items-center">
                                                @if($type === 'EMPLOYEE')
                                                <i class="fas fa-user mr-1 text-[#f06e11] text-xs"></i>
                                                @elseif($type === 'STUDENT')
                                                <i class="fas fa-graduation-cap mr-1 text-green-600 text-xs"></i>
                                                @elseif($type === 'COMPANY')
                                                <i class="fas fa-building mr-1 text-purple-600 text-xs"></i>
                                                @elseif($type === 'COURSE')
                                                <i class="fas fa-book mr-1 text-orange-600 text-xs"></i>
                                                @elseif($type === 'VEHICLE')
                                                <i class="fas fa-car mr-1 text-indigo-600 text-xs"></i>
                                                @elseif($type === 'SHIP')
                                                <i class="fas fa-ship mr-1 text-teal-600 text-xs"></i>
                                                @endif
                                                {{ $type }} ({{ $entities->count() }})
                                            </h5>
                                            <button type="button" onclick="selectEndorsementType('{{ $type }}')" class="text-xs text-green-600 hover:text-green-800">Select All</button>
                                        </div>
                                        <div class="space-y-1 endorsement-entity-items">
                                            @foreach($entities as $entity)
                                            <label class="endorsement-entity-item flex items-center space-x-2 p-2 rounded hover:bg-white cursor-pointer" data-entity-name="{{ strtolower($entity->description) }}">
                                                <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" 
                                                       class="add-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                       onchange="updateAddCount()">
                                                <span class="text-xs text-gray-700">{{ $entity->description }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div id="endorsement-no-results" class="hidden text-center py-4">
                                    <p class="text-xs text-gray-500">No entities found</p>
                                </div>
                                @else
                                <p class="text-sm text-gray-500 text-center py-4">All entities are already associated</p>
                                @endif
                            </div>
                            
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-gray-600">
                                    <span id="add-count">0</span> selected to add
                                </span>
                                <div class="space-x-2">
                                    <button type="button" onclick="selectAllAdd()" class="text-xs text-green-600 hover:text-green-800">Select All</button>
                                    <button type="button" onclick="deselectAllAdd()" class="text-xs text-gray-600 hover:text-gray-800">Deselect All</button>
                                </div>
                            </div>
                            <button type="submit" id="add-btn" disabled
                                    class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                <i class="fas fa-plus mr-2"></i>Add Selected (<span id="add-btn-count">0</span>)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button onclick="document.getElementById('manage-entities-modal').classList.add('hidden')"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="upload-endorsement-document-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Upload Document</h3>
                <button onclick="document.getElementById('upload-endorsement-document-modal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('endorsements.upload-documents', $endorsement) }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                        <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                        <select name="document_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                            <option value="ENDORSEMENT_DOCUMENT">Endorsement Document</option>
                            <option value="POLICY_DOCUMENT">Policy Document</option>
                            <option value="FINANCIAL_DOCUMENT">Financial Document</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <input type="hidden" name="uploaded_by" value="{{ auth()->id() }}">
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="document.getElementById('upload-endorsement-document-modal').classList.add('hidden')"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-upload mr-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Endorsement entity search
    document.getElementById('endorsement-entity-search')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const entityItems = document.querySelectorAll('.endorsement-entity-item');
        let visibleCount = 0;
        
        entityItems.forEach(item => {
            const entityName = item.getAttribute('data-entity-name');
            const parentGroup = item.closest('.endorsement-entity-group');
            
            if (entityName.includes(searchTerm)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });
        
        document.querySelectorAll('.endorsement-entity-group').forEach(group => {
            const visibleItems = group.querySelectorAll('.endorsement-entity-item:not(.hidden)');
            group.style.display = visibleItems.length > 0 ? 'block' : 'none';
        });
        
        document.getElementById('endorsement-no-results').classList.toggle('hidden', visibleCount > 0);
    });
    
    // Endorsement filter by type
    document.getElementById('endorsement-entity-type-filter')?.addEventListener('change', function(e) {
        const selectedType = e.target.value;
        const groups = document.querySelectorAll('.endorsement-entity-group');
        
        groups.forEach(group => {
            if (selectedType === 'ALL' || group.getAttribute('data-entity-type') === selectedType) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    });
    
    // Remove functions
    function selectAllRemove() {
        document.querySelectorAll('.remove-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateRemoveCount();
    }
    
    function deselectAllRemove() {
        document.querySelectorAll('.remove-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateRemoveCount();
    }
    
    function updateRemoveCount() {
        const checked = document.querySelectorAll('.remove-checkbox:checked').length;
        document.getElementById('remove-count').textContent = checked;
        document.getElementById('remove-btn-count').textContent = checked;
        document.getElementById('remove-btn').disabled = checked === 0;
        
        // Update form action for bulk remove
        if (checked > 0) {
            const form = document.getElementById('remove-entities-form');
            const checkboxes = Array.from(document.querySelectorAll('.remove-checkbox:checked'));
            if (checkboxes.length === 1) {
                // Single remove - use existing route
                form.action = "{{ route('endorsements.remove-entity', $endorsement) }}";
                form.innerHTML = '@csrf<input type="hidden" name="entity_id" value="' + checkboxes[0].value + '">';
            } else {
                // Multiple remove - need to handle differently
                // For now, we'll process one by one or create a bulk remove endpoint
            }
        }
    }
    
    // Add functions
    function selectAllAdd() {
        document.querySelectorAll('.add-checkbox:not(:disabled)').forEach(checkbox => {
            if (!checkbox.closest('.endorsement-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateAddCount();
    }
    
    function deselectAllAdd() {
        document.querySelectorAll('.add-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateAddCount();
    }
    
    function selectEndorsementType(type) {
        document.querySelectorAll(`.endorsement-entity-group[data-entity-type="${type}"] .add-checkbox:not(:disabled)`).forEach(checkbox => {
            if (!checkbox.closest('.endorsement-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateAddCount();
    }
    
    function updateAddCount() {
        const checked = document.querySelectorAll('.add-checkbox:checked').length;
        document.getElementById('add-count').textContent = checked;
        document.getElementById('add-btn-count').textContent = checked;
        document.getElementById('add-btn').disabled = checked === 0;
    }
    
    function confirmRemoveEntities() {
        const checked = document.querySelectorAll('.remove-checkbox:checked');
        if (checked.length === 0) {
            return false;
        }
        return confirm(`Are you sure you want to remove ${checked.length} entity(ies) from this endorsement?`);
    }
</script>
@endpush
@endsection

