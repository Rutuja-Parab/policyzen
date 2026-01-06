@extends('layouts.app')

@section('title', 'Policy Details - PolicyZen')

@section('page-title')
<div class="flex items-center">
    <a href="{{ route('policies.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Policy Details</span>
</div>
@endsection

@section('header-actions')
<a href="{{ route('policies.edit', $policy) }}" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
    <i class="fas fa-edit mr-2"></i>Edit Policy
</a>
@endsection

@section('content')
                <!-- Covered Entities Management -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Covered Entities</h2>
                            <button onclick="document.getElementById('add-entity-modal').classList.remove('hidden')"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i>Add Entity
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($activeEntities->count() > 0 || $terminatedEntities->count() > 0)
                        <div class="space-y-6">
                            <!-- Active Entities Section -->
                            @if($activeEntities->count() > 0)
                            <div>
                                <div class="flex items-center mb-4">
                                    <h3 class="text-md font-medium text-green-700 mr-3">
                                        <i class="fas fa-check-circle mr-2"></i>Active Entities ({{ $activeEntities->count() }})
                                    </h3>
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Currently Covered</span>
                                </div>
                                <div class="space-y-3">
                                    @foreach($activeEntities as $entity)
                                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center
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
                                            <div>
                                                <h4 class="font-medium text-gray-900">{{ $entity->description }}</h4>
                                                <p class="text-sm text-gray-600">{{ $entity->type }}</p>
                                                <p class="text-xs text-green-600">
                                                    <i class="fas fa-calendar-plus mr-1"></i>
                                                    Added: {{ $entity->pivot->effective_date ? \Carbon\Carbon::parse($entity->pivot->effective_date)->format('M d, Y') : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('policies.remove-entity', $policy) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm px-3 py-1 rounded border border-red-200 hover:border-red-300"
                                                    onclick="return confirm('Are you sure you want to terminate this entity from the policy?')">
                                                <i class="fas fa-times mr-1"></i>Terminate
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Terminated Entities Section -->
                            @if($terminatedEntities->count() > 0)
                            <div>
                                <div class="flex items-center mb-4">
                                    <h3 class="text-md font-medium text-gray-600 mr-3">
                                        <i class="fas fa-times-circle mr-2"></i>Terminated Entities ({{ $terminatedEntities->count() }})
                                    </h3>
                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">No Longer Covered</span>
                                </div>
                                <div class="space-y-3">
                                    @foreach($terminatedEntities as $entity)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 opacity-75">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-200 text-gray-500">
                                                <i class="fas fa-user-slash"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-700">{{ $entity->description }}</h4>
                                                <p class="text-sm text-gray-500">{{ $entity->type }}</p>
                                                <div class="text-xs text-gray-500 space-y-1">
                                                    <p>
                                                        <i class="fas fa-calendar-plus mr-1"></i>
                                                        Added: {{ $entity->pivot->effective_date ? \Carbon\Carbon::parse($entity->pivot->effective_date)->format('M d, Y') : 'N/A' }}
                                                    </p>
                                                    <p class="text-red-600">
                                                        <i class="fas fa-calendar-minus mr-1"></i>
                                                        <strong>Terminated: {{ $entity->pivot->termination_date ? \Carbon\Carbon::parse($entity->pivot->termination_date)->format('M d, Y') : 'N/A' }}</strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">Terminated</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="text-center py-8">
                            <i class="fas fa-users text-gray-300 text-3xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No entities covered</h3>
                            <p class="text-gray-500 mb-4">Add employees or students to this group policy.</p>
                            <button onclick="document.getElementById('add-entity-modal').classList.remove('hidden')"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i>Add First Entity
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Policy Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900">Policy Information</h2>
                                    <span class="px-3 py-1 text-sm rounded-full
                                        @if($policy->status === 'ACTIVE') bg-green-100 text-green-800
                                        @elseif($policy->status === 'EXPIRED') bg-red-100 text-red-800
                                        @elseif($policy->status === 'UNDER_REVIEW') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $policy->status }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Policy Number</label>
                                        <p class="text-lg font-semibold text-gray-900">{{ $policy->policy_number }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Insurance Type</label>
                                        <p class="text-gray-900">{{ $policy->insurance_type }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider</label>
                                        <p class="text-gray-900">{{ $policy->provider }}</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Covered Entities</label>
                                        <p class="text-gray-900">{{ $policy->entities->count() }} entities covered</p>
                                        @if($policy->entities->count() > 0)
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($policy->entities as $entity)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($entity->type === 'EMPLOYEE') bg-blue-100 text-blue-800
                                                @elseif($entity->type === 'STUDENT') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $entity->description }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                        <p class="text-gray-900">{{ $policy->start_date ? $policy->start_date->format('M d, Y') : 'N/A' }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                        <p class="text-gray-900">{{ $policy->end_date ? $policy->end_date->format('M d, Y') : 'N/A' }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sum Insured</label>
                                        <p class="text-2xl font-bold text-green-600">${{ number_format($policy->sum_insured, 2) }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Premium Amount</label>
                                        <p class="text-2xl font-bold text-[#f06e11]">${{ number_format($policy->premium_amount, 2) }}</p>
                                    </div>
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
                                <a href="{{ route('policies.edit', $policy) }}" class="w-full bg-[#f06e11] text-white px-4 py-3 rounded-lg hover:bg-[#f28e1f] flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i>Edit Policy
                                </a>

                                @if($policy->status === 'ACTIVE')
                                <form method="POST" action="{{ route('policies.update', $policy) }}" class="w-full">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="CANCELLED">
                                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center" onclick="return confirm('Are you sure you want to cancel this policy?')">
                                        <i class="fas fa-times mr-2"></i>Cancel Policy
                                    </button>
                                </form>
                                @endif

                                <form method="POST" action="{{ route('policies.destroy', $policy) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this policy? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-gray-600 text-white px-4 py-3 rounded-lg hover:bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-trash mr-2"></i>Delete Policy
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Policy Stats -->
                        <div class="bg-white rounded-lg shadow mt-6">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Policy Stats</h3>
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Created</span>
                                    <span class="font-medium">{{ $policy->created_at ? $policy->created_at->format('M d, Y') : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Last Updated</span>
                                    <span class="font-medium">{{ $policy->updated_at ? $policy->updated_at->format('M d, Y') : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Endorsements</span>
                                    <span class="font-medium">{{ $policy->endorsements->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="bg-white rounded-lg shadow mt-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Documents</h2>
                            <button onclick="document.getElementById('upload-document-modal').classList.remove('hidden')"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                <i class="fas fa-upload mr-2"></i>Upload Document
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($allDocuments->count() > 0)
                        <div class="space-y-4">
                            @foreach($allDocuments as $document)
                            @php
                                $isEndorsementDoc = $document->documentable_type === 'App\Models\PolicyEndorsement';
                                $relatedEndorsement = $isEndorsementDoc ? \App\Models\PolicyEndorsement::find($document->documentable_id) : null;
                            @endphp
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border 
                                @if($isEndorsementDoc) border-yellow-200 bg-yellow-50 
                                @elseif(in_array($document->document_type, ['INVOICE', 'CREDIT_NOTE', 'RECEIPT'])) border-green-200 bg-green-50
                                @else border-blue-200 bg-blue-50 @endif">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                                        @if($isEndorsementDoc) bg-yellow-100 text-yellow-600 
                                        @elseif(in_array($document->document_type, ['INVOICE', 'CREDIT_NOTE', 'RECEIPT'])) bg-green-100 text-green-600
                                        @else bg-blue-100 text-[#f06e11] @endif">
                                        @if($isEndorsementDoc)
                                        <i class="fas fa-edit"></i>
                                        @elseif($document->document_type === 'INVOICE')
                                        <i class="fas fa-file-invoice"></i>
                                        @elseif($document->document_type === 'CREDIT_NOTE')
                                        <i class="fas fa-file-invoice-dollar"></i>
                                        @elseif($document->document_type === 'RECEIPT')
                                        <i class="fas fa-receipt"></i>
                                        @else
                                        <i class="fas fa-file-contract"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h3 class="font-medium text-gray-900">{{ $document->file_name }}</h3>
                                            @if($isEndorsementDoc)
                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                                <i class="fas fa-tag mr-1"></i>Endorsement Document
                                            </span>
                                            @elseif(in_array($document->document_type, ['INVOICE', 'CREDIT_NOTE', 'RECEIPT']))
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                @if($document->document_type === 'INVOICE')
                                                <i class="fas fa-file-invoice mr-1"></i>Invoice
                                                @elseif($document->document_type === 'CREDIT_NOTE')
                                                <i class="fas fa-file-invoice-dollar mr-1"></i>Credit Note
                                                @elseif($document->document_type === 'RECEIPT')
                                                <i class="fas fa-receipt mr-1"></i>Receipt
                                                @endif
                                            </span>
                                            @else
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                <i class="fas fa-file-contract mr-1"></i>Policy Document
                                            </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Document Details -->
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <p>{{ $document->document_type }} • {{ $document->file_type }} • {{ number_format($document->file_size ?? 0, 0) }} KB</p>
                                            
                                            <!-- Invoice/Credit Note specific information -->
                                            @if(in_array($document->document_type, ['INVOICE', 'CREDIT_NOTE', 'RECEIPT']))
                                            <div class="bg-green-50 p-3 rounded border border-green-200 mt-2">
                                                <div class="grid grid-cols-2 gap-3 text-xs">
                                                    @if($document->invoice_number)
                                                    <div>
                                                        <span class="font-medium text-green-700">Number:</span>
                                                        <span class="text-green-600">{{ $document->invoice_number }}</span>
                                                    </div>
                                                    @endif
                                                    @if($document->total_amount)
                                                    <div>
                                                        <span class="font-medium text-green-700">Total Amount:</span>
                                                        <span class="text-green-600 font-semibold">₹{{ number_format($document->total_amount, 2) }}</span>
                                                    </div>
                                                    @endif
                                                    @if($document->amount)
                                                    <div>
                                                        <span class="font-medium text-green-700">Amount:</span>
                                                        <span class="text-green-600">₹{{ number_format($document->amount, 2) }}</span>
                                                    </div>
                                                    @endif
                                                    @if($document->tax_amount)
                                                    <div>
                                                        <span class="font-medium text-green-700">Tax:</span>
                                                        <span class="text-green-600">₹{{ number_format($document->tax_amount, 2) }}</span>
                                                    </div>
                                                    @endif
                                                    @if($document->status)
                                                    <div>
                                                        <span class="font-medium text-green-700">Status:</span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                            @if($document->status === 'PAID') bg-green-100 text-green-800
                                                            @elseif($document->status === 'OVERDUE') bg-red-100 text-red-800
                                                            @elseif($document->status === 'PARTIALLY_PAID') bg-yellow-100 text-yellow-800
                                                            @elseif($document->status === 'CANCELLED') bg-gray-100 text-gray-800
                                                            @else bg-blue-100 text-blue-800 @endif">
                                                            {{ ucwords(str_replace('_', ' ', $document->status)) }}
                                                        </span>
                                                    </div>
                                                    @endif
                                                    @if($document->issue_date)
                                                    <div>
                                                        <span class="font-medium text-green-700">Issue Date:</span>
                                                        <span class="text-green-600">{{ $document->issue_date->format('M d, Y') }}</span>
                                                    </div>
                                                    @endif
                                                    @if($document->due_date)
                                                    <div>
                                                        <span class="font-medium text-green-700">Due Date:</span>
                                                        <span class="text-green-600">{{ $document->due_date->format('M d, Y') }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                                @if($document->notes)
                                                <div class="mt-2 pt-2 border-t border-green-200">
                                                    <span class="font-medium text-green-700 text-xs">Notes:</span>
                                                    <p class="text-green-600 text-xs mt-1">{{ $document->notes }}</p>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                            
                                            <!-- Upload Information -->
                                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                <span>
                                                    <i class="fas fa-upload mr-1"></i>
                                                    Uploaded {{ $document->uploaded_at ? $document->uploaded_at->format('M d, Y H:i') : 'N/A' }}
                                                </span>
                                                <span>
                                                    <i class="fas fa-user mr-1"></i>
                                                    By {{ $document->uploader->name ?? 'Unknown' }}
                                                </span>
                                                @if($relatedEndorsement)
                                                <span>
                                                    <i class="fas fa-link mr-1"></i>
                                                    Endorsement: {{ $relatedEndorsement->endorsement_number }}
                                                </span>
                                                @endif
                                            </div>
                                            
                                            <!-- Audit Trail -->
                                            <div class="bg-white bg-opacity-60 rounded p-2 mt-2">
                                                <p class="text-xs font-medium text-gray-700 mb-1">
                                                    <i class="fas fa-history mr-1"></i>Audit Trail
                                                </p>
                                                <div class="text-xs text-gray-600 space-y-1">
                                                    <p>📄 Document ID: {{ $document->id }}</p>
                                                    <p>🔗 Related to: 
                                                        @if($isEndorsementDoc)
                                                            Endorsement #{{ $relatedEndorsement->endorsement_number ?? 'N/A' }}
                                                        @else
                                                            Policy #{{ $policy->policy_number }}
                                                        @endif
                                                    </p>
                                                    <p>📅 Upload Date: {{ $document->uploaded_at ? $document->uploaded_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                                                    <p>👤 Uploader: {{ $document->uploader->name ?? 'Unknown' }} ({{ $document->uploader->email ?? 'N/A' }})</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ Storage::url($document->file_path) }}" target="_blank" 
                                       class="text-[#f06e11] hover:text-blue-800 px-3 py-1 rounded border border-blue-200 hover:border-blue-300"
                                       title="Download Document">
                                        <i class="fas fa-download mr-1"></i>Download
                                    </a>
                                    <form method="POST" action="{{ route('documents.destroy', $document) }}" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this document?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 px-3 py-1 rounded border border-red-200 hover:border-red-300"
                                                title="Delete Document">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Document Summary -->
                        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-chart-bar mr-2"></i>Document Summary
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-[#f06e11]">{{ $allDocuments->count() }}</p>
                                    <p class="text-gray-600">Total Documents</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-[#f06e11]">{{ $policyDocuments->count() }}</p>
                                    <p class="text-gray-600">Policy Docs</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-yellow-600">{{ $endorsementDocuments->count() }}</p>
                                    <p class="text-gray-600">Endorsement Docs</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ $allDocuments->where('document_type', 'INVOICE')->count() }}
                                    </p>
                                    <p class="text-gray-600">Invoices</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ $allDocuments->where('document_type', 'CREDIT_NOTE')->count() }}
                                    </p>
                                    <p class="text-gray-600">Credit Notes</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ $allDocuments->where('document_type', 'RECEIPT')->count() }}
                                    </p>
                                    <p class="text-gray-600">Receipts</p>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <i class="fas fa-file text-gray-300 text-3xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No documents uploaded</h3>
                            <p class="text-gray-500 mb-4">Upload policy documents, endorsement documents, financial records, or other related files.</p>
                            <button onclick="document.getElementById('upload-document-modal').classList.remove('hidden')"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                <i class="fas fa-upload mr-2"></i>Upload First Document
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Upload Document Modal -->
                <div id="upload-document-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Upload Document</h3>
                                <button onclick="document.getElementById('upload-document-modal').classList.add('hidden')"
                                        class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('policies.upload-documents', $policy) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                                        <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                        <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each)</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                                        <select name="document_type" id="document_type" required 
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent"
                                                onchange="toggleInvoiceFields()">
                                            <option value="POLICY_DOCUMENT">Policy Document</option>
                                            <option value="ENDORSEMENT_DOCUMENT">Endorsement Document</option>
                                            <option value="FINANCIAL_DOCUMENT">Financial Document</option>
                                            <option value="INVOICE">Invoice</option>
                                            <option value="CREDIT_NOTE">Credit Note</option>
                                            <option value="RECEIPT">Receipt</option>
                                            <option value="OTHER">Other</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Invoice/Credit Note specific fields -->
                                    <div id="invoice-fields" class="hidden space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Invoice/Credit Note Number</label>
                                            <input type="text" name="invoice_number" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent" placeholder="e.g., INV-202412-0001">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount (₹)</label>
                                                <input type="number" name="amount" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent" placeholder="0.00">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Amount (₹)</label>
                                                <input type="number" name="tax_amount" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount (₹)</label>
                                                <input type="number" name="total_amount" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent" placeholder="0.00">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                                    <option value="DRAFT">Draft</option>
                                                    <option value="SENT">Sent</option>
                                                    <option value="PARTIALLY_PAID">Partially Paid</option>
                                                    <option value="PAID">Paid</option>
                                                    <option value="OVERDUE">Overdue</option>
                                                    <option value="CANCELLED">Cancelled</option>
                                                    <option value="ISSUED">Issued</option>
                                                    <option value="APPLIED">Applied</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label>
                                                <input type="date" name="issue_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                                                <input type="date" name="due_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                            <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent" placeholder="Additional notes about this invoice or credit note..."></textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" name="uploaded_by" value="{{ auth()->id() }}">
                                </div>

                                <div class="flex justify-end space-x-4 mt-6">
                                    <button type="button" onclick="document.getElementById('upload-document-modal').classList.add('hidden')"
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

                <!-- Add Entity Modal -->
                <div id="add-entity-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                    <div class="relative top-5 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white my-8">
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Add Entities to Policy</h3>
                                <button onclick="document.getElementById('add-entity-modal').classList.add('hidden')"
                                        class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('policies.add-entity', $policy) }}" id="add-entity-form">
                                @csrf
                                
                                <!-- Search and Filter Controls -->
                                <div class="mb-4 space-y-3">
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <div class="flex-1 relative">
                                            <input type="text" id="modal-entity-search" placeholder="Search entities..." 
                                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                        </div>
                                        <select id="modal-entity-type-filter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                            <option value="ALL">All Types</option>
                                            <option value="EMPLOYEE">Employees</option>
                                            <option value="STUDENT">Students</option>
                                            <option value="COMPANY">Companies</option>
                                            <option value="COURSE">Courses</option>
                                            <option value="VEHICLE">Vehicles</option>
                                            <option value="SHIP">Vessels</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Bulk Actions -->
                                    <div class="flex items-center justify-between bg-blue-50 p-3 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <button type="button" onclick="modalSelectAll()" class="text-sm text-[#f06e11] hover:text-blue-800 font-medium">
                                                <i class="fas fa-check-square mr-1"></i>Select All
                                            </button>
                                            <button type="button" onclick="modalDeselectAll()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                                <i class="fas fa-square mr-1"></i>Deselect All
                                            </button>
                                        </div>
                                        <div class="text-sm font-medium text-gray-700">
                                            <span id="modal-selected-count">0</span> selected
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4 max-h-96 overflow-y-auto border border-gray-300 rounded-lg p-4 bg-gray-50">
                                    @php
                                    $activeEntityIds = $policy->entities()->wherePivot('status', 'ACTIVE')->pluck('entities.id')->toArray();
                                    $availableEntities = \App\Models\Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'COMPANY', 'COURSE', 'VEHICLE', 'SHIP'])
                                        ->whereNotIn('id', $activeEntityIds)
                                        ->get();
                                    $groupedEntities = $availableEntities->groupBy('type');
                                    @endphp
                                    
                                    @if($availableEntities->count() > 0)
                                    <div id="modal-entity-list" class="space-y-4">
                                        @foreach($groupedEntities as $type => $entities)
                                        <div class="modal-entity-group" data-entity-type="{{ $type }}">
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
                                                    {{ $type }} <span class="ml-2 text-sm text-gray-500">({{ $entities->count() }})</span>
                                                </h4>
                                                <button type="button" onclick="modalSelectType('{{ $type }}')" class="text-xs text-[#f06e11] hover:text-blue-800">
                                                    Select All
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 modal-entity-items">
                                                @foreach($entities as $entity)
                                                <label class="modal-entity-item flex items-center space-x-2 p-2 rounded hover:bg-white cursor-pointer" data-entity-name="{{ strtolower($entity->description) }}">
                                                    <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" 
                                                           class="modal-entity-checkbox rounded border-gray-300 text-[#f06e11] focus:ring-[#2b8bd0]"
                                                           onchange="updateModalSelectedCount()">
                                                    <span class="text-sm text-gray-700">{{ $entity->description }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div id="modal-no-results" class="hidden text-center py-8">
                                        <i class="fas fa-search text-4xl text-gray-300 mb-2"></i>
                                        <p class="text-gray-500">No entities found matching your search</p>
                                    </div>
                                    @else
                                    <p class="text-sm text-gray-500 italic text-center py-8">No available entities to add</p>
                                    @endif
                                </div>

                                <div class="flex justify-end space-x-4">
                                    <button type="button" onclick="document.getElementById('add-entity-modal').classList.add('hidden')"
                                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                                        Cancel
                                    </button>
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                        <i class="fas fa-plus mr-2"></i>Add Selected Entities (<span id="submit-count">0</span>)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

@push('scripts')
<script>
    function toggleInvoiceFields() {
        const documentType = document.getElementById('document_type').value;
        const invoiceFields = document.getElementById('invoice-fields');
        
        if (documentType === 'INVOICE' || documentType === 'CREDIT_NOTE' || documentType === 'RECEIPT') {
            invoiceFields.classList.remove('hidden');
        } else {
            invoiceFields.classList.add('hidden');
        }
    }
    
    // Modal search functionality
    document.getElementById('modal-entity-search')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const entityItems = document.querySelectorAll('.modal-entity-item');
        let visibleCount = 0;
        
        entityItems.forEach(item => {
            const entityName = item.getAttribute('data-entity-name');
            const parentGroup = item.closest('.modal-entity-group');
            
            if (entityName.includes(searchTerm)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });
        
        document.querySelectorAll('.modal-entity-group').forEach(group => {
            const visibleItems = group.querySelectorAll('.modal-entity-item:not(.hidden)');
            group.style.display = visibleItems.length > 0 ? 'block' : 'none';
        });
        
        document.getElementById('modal-no-results').classList.toggle('hidden', visibleCount > 0);
    });
    
    // Modal filter by type
    document.getElementById('modal-entity-type-filter')?.addEventListener('change', function(e) {
        const selectedType = e.target.value;
        const groups = document.querySelectorAll('.modal-entity-group');
        
        groups.forEach(group => {
            if (selectedType === 'ALL' || group.getAttribute('data-entity-type') === selectedType) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    });
    
    function modalSelectAll() {
        document.querySelectorAll('.modal-entity-checkbox:not(:disabled)').forEach(checkbox => {
            if (!checkbox.closest('.modal-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateModalSelectedCount();
    }
    
    function modalDeselectAll() {
        document.querySelectorAll('.modal-entity-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateModalSelectedCount();
    }
    
    function modalSelectType(type) {
        document.querySelectorAll(`.modal-entity-group[data-entity-type="${type}"] .modal-entity-checkbox:not(:disabled)`).forEach(checkbox => {
            if (!checkbox.closest('.modal-entity-item').classList.contains('hidden')) {
                checkbox.checked = true;
            }
        });
        updateModalSelectedCount();
    }
    
    function updateModalSelectedCount() {
        const checked = document.querySelectorAll('.modal-entity-checkbox:checked').length;
        document.getElementById('modal-selected-count').textContent = checked;
        document.getElementById('submit-count').textContent = checked;
    }
    
    // Reset modal when opened
    document.getElementById('add-entity-modal')?.addEventListener('click', function(e) {
        if (e.target.id === 'add-entity-modal') {
            document.getElementById('add-entity-modal').classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
