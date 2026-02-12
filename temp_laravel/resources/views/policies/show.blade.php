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
    <!-- Policy Header Card -->
    <div class="bg-gradient-to-r from-[#f06e11] to-[#f28e1f] rounded-lg shadow-lg mb-6 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $policy->policy_number }}</h1>
                    <p class="text-white/80">{{ $policy->insurance_type }} - {{ $policy->provider }}</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-4">
                <span class="px-4 py-2 bg-white/20 text-white rounded-full text-sm font-medium">
                    <i
                        class="fas fa-circle mr-2 {{ $policy->status === 'ACTIVE' ? 'text-green-300' : ($policy->status === 'EXPIRED' ? 'text-red-300' : 'text-yellow-300') }}"></i>
                    {{ $policy->status }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Policy Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Core Policy Details -->
            <div class="bg-white rounded-lg shadow border-l-4 border-[#f06e11]">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-[#f06e11]"></i>
                        Core Policy Details
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Policy
                                Number</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $policy->policy_number }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Insurance
                                Type</label>
                            <p class="text-gray-900 font-medium">{{ $policy->insurance_type }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label
                                class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Provider</label>
                            <p class="text-gray-900 font-medium">{{ $policy->provider }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total
                                Entities</label>
                            <p class="text-gray-900 font-medium">{{ $policy->entities->count() }} entities covered</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coverage Summary -->
            <div class="bg-white rounded-lg shadow border-l-4 border-green-500">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-green-500"></i>
                        Coverage Summary
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-green-600 uppercase">Total Coverage Pool</p>
                                    <p class="text-2xl font-bold text-green-700">
                                        ₹{{ number_format($policy->starting_coverage_pool, 2) }}</p>
                                </div>
                                <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-university text-green-600"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-blue-600 uppercase">Available Coverage</p>
                                    <p class="text-2xl font-bold text-blue-700">
                                        ₹{{ number_format($policy->available_coverage_pool, 2) }}</p>
                                </div>
                                <div class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check-circle text-blue-600"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-orange-600 uppercase">Utilized Premium</p>
                                    <p class="text-2xl font-bold text-orange-700">
                                        ₹{{ number_format($policy->utilized_coverage_pool, 2) }}</p>
                                </div>
                                <div class="w-10 h-10 bg-orange-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-percentage text-orange-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coverage Progress Bar -->
                    <div class="mt-6">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Coverage Utilization</span>
                            <span class="font-medium text-gray-900">
                                {{ round(($policy->utilized_coverage_pool / $policy->starting_coverage_pool) * 100, 1) }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-[#f06e11] h-3 rounded-full transition-all duration-300"
                                style="width: {{ min(($policy->utilized_coverage_pool / $policy->starting_coverage_pool) * 100, 100) }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Premium Summary -->
            @if (isset($totalAdditions) || isset($totalRemovals) || isset($netPremium))
                <div class="bg-white rounded-lg shadow border-l-4 border-blue-500">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-rupee-sign mr-2 text-blue-500"></i>
                            Premium Summary
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                                <p class="text-xs text-green-600 uppercase font-medium">Additions</p>
                                <p class="text-xl font-bold text-green-700">₹{{ number_format($totalAdditions ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                <p class="text-xs text-red-600 uppercase font-medium">Removals</p>
                                <p class="text-xl font-bold text-red-700">₹{{ number_format($totalRemovals ?? 0, 2) }}</p>
                            </div>
                            <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-xs text-blue-600 uppercase font-medium">Renewals</p>
                                <p class="text-xl font-bold text-blue-700">₹{{ number_format($totalRenewals ?? 0, 2) }}</p>
                            </div>
                            <div class="text-center p-4 bg-orange-50 rounded-lg border border-orange-200">
                                <p class="text-xs text-orange-600 uppercase font-medium">Net Premium</p>
                                <p class="text-xl font-bold text-orange-700">₹{{ number_format($netPremium ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Covered Entities -->
            @php
                $activeEntitiesList = $activeEntities->take(5);
                $hiddenActiveCount = $activeEntities->count() - 5;
                $terminatedEntitiesList = $terminatedEntities->take(5);
                $hiddenTerminatedCount = $terminatedEntities->count() - 5;
            @endphp
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-users mr-2 text-[#f06e11]"></i>
                        Covered Entities
                    </h2>
                    <button onclick="document.getElementById('add-entity-modal').classList.remove('hidden')"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Entity
                    </button>
                </div>
                <div class="p-6">
                    @if ($activeEntities->count() > 0 || $terminatedEntities->count() > 0)
                        <div class="space-y-4">
                            <!-- Active Entities -->
                            @if ($activeEntities->count() > 0)
                                <div>
                                    <div class="flex items-center mb-3">
                                        <span
                                            class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1 rounded-full mr-3">
                                            {{ $activeEntities->count() }} Active
                                        </span>
                                        <h3 class="text-sm font-medium text-gray-700">Currently Covered Entities</h3>
                                    </div>
                                    <div class="space-y-3 max-h-80 overflow-y-auto" id="active-entities-container">
                                        @foreach ($activeEntitiesList as $entity)
                                            <div
                                                class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-white rounded-lg border border-green-200 hover:shadow-md transition-shadow">
                                                <div class="flex items-center space-x-4">
                                                    <div
                                                        class="w-12 h-12 rounded-full flex items-center justify-center
                                                        @if ($entity->type === 'EMPLOYEE') bg-blue-100 text-[#f06e11]
                                                        @elseif($entity->type === 'STUDENT') bg-green-100 text-green-600
                                                        @elseif($entity->type === 'COMPANY') bg-purple-100 text-purple-600
                                                        @elseif($entity->type === 'COURSE') bg-orange-100 text-orange-600
                                                        @elseif($entity->type === 'VEHICLE') bg-indigo-100 text-indigo-600
                                                        @elseif($entity->type === 'SHIP') bg-teal-100 text-teal-600
                                                        @else bg-gray-100 text-gray-600 @endif">
                                                        @if ($entity->type === 'EMPLOYEE')
                                                            <i class="fas fa-user text-lg"></i>
                                                        @elseif($entity->type === 'STUDENT')
                                                            <i class="fas fa-graduation-cap text-lg"></i>
                                                        @elseif($entity->type === 'COMPANY')
                                                            <i class="fas fa-building text-lg"></i>
                                                        @elseif($entity->type === 'COURSE')
                                                            <i class="fas fa-book text-lg"></i>
                                                        @elseif($entity->type === 'VEHICLE')
                                                            <i class="fas fa-car text-lg"></i>
                                                        @elseif($entity->type === 'SHIP')
                                                            <i class="fas fa-ship text-lg"></i>
                                                        @else
                                                            <i class="fas fa-question text-lg"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">{{ $entity->description }}
                                                        </h4>
                                                        <p class="text-sm text-gray-500">{{ $entity->type }}</p>
                                                        <p class="text-xs text-green-600 mt-1">
                                                            <i class="fas fa-calendar-check mr-1"></i>
                                                            Effective:
                                                            {{ $entity->pivot->effective_date ? \Carbon\Carbon::parse($entity->pivot->effective_date)->format('M d, Y') : 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <form method="POST"
                                                    action="{{ route('policies.remove-entity', $policy) }}"
                                                    class="inline">
                                                    @csrf
                                                    <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-800 text-sm px-3 py-1.5 rounded border border-red-200 hover:border-red-300 transition-colors"
                                                        onclick="return confirm('Are you sure you want to terminate this entity?')">
                                                        <i class="fas fa-times mr-1"></i>Terminate
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($hiddenActiveCount > 0)
                                        <button onclick="toggleAllEntities('active')"
                                            class="mt-3 text-sm text-[#f06e11] hover:text-[#d85a0a] font-medium">
                                            <i class="fas fa-chevron-down mr-1"></i>Show {{ $hiddenActiveCount }} more
                                            active entities
                                        </button>
                                    @endif
                                </div>
                            @endif

                            <!-- Terminated Entities -->
                            @if ($terminatedEntities->count() > 0)
                                <div class="mt-6">
                                    <div class="flex items-center mb-3">
                                        <span
                                            class="bg-gray-100 text-gray-600 text-xs font-medium px-3 py-1 rounded-full mr-3">
                                            {{ $terminatedEntities->count() }} Terminated
                                        </span>
                                        <h3 class="text-sm font-medium text-gray-700">Previously Covered Entities</h3>
                                    </div>
                                    <div class="space-y-3 max-h-64 overflow-y-auto" id="terminated-entities-container">
                                        @foreach ($terminatedEntitiesList as $entity)
                                            <div
                                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 opacity-70">
                                                <div class="flex items-center space-x-4">
                                                    <div
                                                        class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-500">
                                                        <i class="fas fa-user-slash text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium text-gray-700">{{ $entity->description }}
                                                        </h4>
                                                        <p class="text-sm text-gray-500">{{ $entity->type }}</p>
                                                        <div class="text-xs text-gray-500 space-y-1 mt-1">
                                                            <p>
                                                                <i class="fas fa-calendar-plus mr-1"></i>
                                                                Added:
                                                                {{ $entity->pivot->effective_date ? \Carbon\Carbon::parse($entity->pivot->effective_date)->format('M d, Y') : 'N/A' }}
                                                            </p>
                                                            <p class="text-red-600">
                                                                <i class="fas fa-calendar-minus mr-1"></i>
                                                                Terminated:
                                                                {{ $entity->pivot->termination_date ? \Carbon\Carbon::parse($entity->pivot->termination_date)->format('M d, Y') : 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-xs text-gray-500 bg-gray-200 px-3 py-1 rounded-full">Terminated</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($hiddenTerminatedCount > 0)
                                        <button onclick="toggleAllEntities('terminated')"
                                            class="mt-3 text-sm text-gray-600 hover:text-gray-800 font-medium">
                                            <i class="fas fa-chevron-down mr-1"></i>Show {{ $hiddenTerminatedCount }} more
                                            terminated entities
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No entities covered</h3>
                            <p class="text-gray-500 mb-4">Add employees or students to this policy.</p>
                            <button onclick="document.getElementById('add-entity-modal').classList.remove('hidden')"
                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Add First Entity
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Premium History -->
            @php
                $premiums = \App\Models\StudentPolicyPremium::where('policy_id', $policy->id)
                    ->with(['student', 'endorsement'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                $premiumsList = $premiums->take(10);
                $hiddenPremiumCount = $premiums->count() - 10;
            @endphp
            @if ($premiums->count() > 0)
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-history mr-2 text-[#f06e11]"></i>
                            Premium History
                        </h2>
                        <span class="text-sm text-gray-500">{{ $premiums->count() }} records</span>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto max-h-96 overflow-y-auto" id="premium-history-container">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sum Insured</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Days</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Premium</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            GST</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Final</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Endorsement</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($premiumsList as $premium)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $premium->student->name ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500">{{ $premium->student->email ?? '' }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if ($premium->premium_type === 'ADDITION') bg-green-100 text-green-800
                                                    @elseif($premium->premium_type === 'REMOVAL') bg-red-100 text-red-800
                                                    @else bg-blue-100 text-blue-800 @endif">
                                                    {{ $premium->premium_type }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                ₹{{ number_format($premium->sum_insured, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ $premium->pro_rata_days }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                ₹{{ number_format($premium->prorata_premium, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                ₹{{ number_format($premium->gst_amount, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-[#f06e11]">
                                                ₹{{ number_format($premium->final_premium, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                @if ($premium->endorsement)
                                                    <a href="{{ route('endorsements.show', $premium->endorsement) }}"
                                                        class="text-[#f06e11] hover:text-blue-800">
                                                        {{ $premium->endorsement->endorsement_number }}
                                                    </a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $premium->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($hiddenPremiumCount > 0)
                            <button onclick="toggleAllPremiums()"
                                class="mt-3 text-sm text-[#f06e11] hover:text-[#d85a0a] font-medium">
                                <i class="fas fa-chevron-down mr-1"></i>Show {{ $hiddenPremiumCount }} more records
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Documents Section -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-[#f06e11]"></i>
                        Documents
                    </h2>
                    <button onclick="document.getElementById('upload-document-modal').classList.remove('hidden')"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-upload mr-2"></i>Upload
                    </button>
                </div>
                <div class="p-6">
                    @if ($allDocuments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($allDocuments as $document)
                                @php
                                    $isEndorsementDoc = $document->documentable_type === 'App\Models\PolicyEndorsement';
                                    $relatedEndorsement = $isEndorsementDoc
                                        ? \App\Models\PolicyEndorsement::find($document->documentable_id)
                                        : null;
                                @endphp
                                <div
                                    class="flex items-start justify-between p-4 rounded-lg border 
                                    @if ($isEndorsementDoc) border-yellow-200 bg-yellow-50 
                                    @elseif(in_array($document->document_type, ['INVOICE', 'CREDIT_NOTE', 'RECEIPT'])) border-green-200 bg-green-50
                                    @else border-blue-200 bg-blue-50 @endif">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                            @if ($isEndorsementDoc) bg-yellow-100 text-yellow-600 
                                            @elseif(in_array($document->document_type, ['INVOICE', 'CREDIT_NOTE', 'RECEIPT'])) bg-green-100 text-green-600
                                            @else bg-blue-100 text-[#f06e11] @endif">
                                            @if ($isEndorsementDoc)
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
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $document->file_name }}</h3>
                                            <div class="flex items-center space-x-2 mt-1">
                                                @if ($isEndorsementDoc)
                                                    <span
                                                        class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                                                        <i class="fas fa-tag mr-1"></i>Endorsement
                                                    </span>
                                                @elseif(in_array($document->document_type, ['INVOICE', 'CREDIT_NOTE', 'RECEIPT']))
                                                    <span
                                                        class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full">
                                                        @if ($document->document_type === 'INVOICE')
                                                            <i class="fas fa-file-invoice mr-1"></i>Invoice
                                                        @elseif($document->document_type === 'CREDIT_NOTE')
                                                            <i class="fas fa-file-invoice-dollar mr-1"></i>Credit Note
                                                        @elseif($document->document_type === 'RECEIPT')
                                                            <i class="fas fa-receipt mr-1"></i>Receipt
                                                        @endif
                                                    </span>
                                                @else
                                                    <span
                                                        class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                                        <i class="fas fa-file-contract mr-1"></i>Policy
                                                    </span>
                                                @endif
                                                <span
                                                    class="text-xs text-gray-500">{{ number_format($document->file_size / 1024, 1) }}
                                                    KB</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ $document->created_at->format('M d, Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ Storage::url($document->file_path) }}" target="_blank"
                                            class="text-[#f06e11] hover:text-[#d85a0a] p-2 rounded-lg hover:bg-white/50 transition-colors"
                                            title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        {{-- <a href="{{ Storage::url($document->file_path) }}" target="_blank"
                                            class="text-[#f06e11] hover:text-blue-800 px-3 py-1 rounded border border-blue-200 hover:border-blue-300"
                                            title="Download Document">
                                            <i class="fas fa-download mr-1"></i>Download
                                        </a> --}}
                                        @if (!$isEndorsementDoc)
                                            <form method="POST" action="{{ route('documents.destroy', $document) }}"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                                    onclick="return confirm('Are you sure you want to delete this document?')"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No documents uploaded</h3>
                            <p class="text-gray-500">Upload policy documents, invoices, or endorsements.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Key Dates -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-[#f06e11]"></i>
                        Key Dates
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-play text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-blue-600 uppercase font-medium">Start Date</p>
                                <p class="font-medium text-gray-900">
                                    {{ $policy->start_date ? $policy->start_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-stop text-red-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-red-600 uppercase font-medium">End Date</p>
                                <p class="font-medium text-gray-900">
                                    {{ $policy->end_date ? $policy->end_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($policy->status === 'EXPIRED')
                        <div
                            class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-orange-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-orange-600 uppercase font-medium">Expired</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $policy->end_date ? $policy->end_date->diffForHumans() : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Policy Stats -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-[#f06e11]"></i>
                        Policy Statistics
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-plus-circle mr-2 text-green-500"></i>
                            Active Entities
                        </span>
                        <span class="font-semibold text-gray-900">{{ $activeEntities->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-minus-circle mr-2 text-red-500"></i>
                            Terminated
                        </span>
                        <span class="font-semibold text-gray-900">{{ $terminatedEntities->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-file-signature mr-2 text-blue-500"></i>
                            Endorsements
                        </span>
                        <span class="font-semibold text-gray-900">{{ $policy->endorsements->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-file mr-2 text-gray-500"></i>
                            Documents
                        </span>
                        <span class="font-semibold text-gray-900">{{ $allDocuments->count() }}</span>
                    </div>
                    <hr class="border-gray-200">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-calendar-plus mr-2 text-[#f06e11]"></i>
                            Created
                        </span>
                        <span
                            class="font-medium text-gray-900 text-sm">{{ $policy->created_at ? $policy->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-edit mr-2 text-gray-500"></i>
                            Updated
                        </span>
                        <span
                            class="font-medium text-gray-900 text-sm">{{ $policy->updated_at ? $policy->updated_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-[#f06e11]"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('policies.edit', $policy) }}"
                        class="w-full bg-[#f06e11] text-white px-4 py-3 rounded-lg hover:bg-[#f28e1f] flex items-center justify-center transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit Policy
                    </a>

                    @if ($policy->status === 'ACTIVE')
                        <form method="POST" action="{{ route('policies.update', $policy) }}" class="w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="CANCELLED">
                            <button type="submit"
                                class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center transition-colors"
                                onclick="return confirm('Are you sure you want to cancel this policy?')">
                                <i class="fas fa-times mr-2"></i>Cancel Policy
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('policies.destroy', $policy) }}" class="w-full"
                        onsubmit="return confirm('Are you sure you want to delete this policy? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full bg-gray-600 text-white px-4 py-3 rounded-lg hover:bg-gray-700 flex items-center justify-center transition-colors">
                            <i class="fas fa-trash mr-2"></i>Delete Policy
                        </button>
                    </form>
                </div>
            </div>

            <!-- Entity Tags -->
            @if ($policy->entities->count() > 0)
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-tags mr-2 text-[#f06e11]"></i>
                            Entity Tags
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($policy->entities->unique('type') as $entity)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if ($entity->type === 'EMPLOYEE') bg-blue-100 text-blue-800
                                    @elseif($entity->type === 'STUDENT') bg-green-100 text-green-800
                                    @elseif($entity->type === 'COMPANY') bg-purple-100 text-purple-800
                                    @elseif($entity->type === 'COURSE') bg-orange-100 text-orange-800
                                    @elseif($entity->type === 'VEHICLE') bg-indigo-100 text-indigo-800
                                    @elseif($entity->type === 'SHIP') bg-teal-100 text-teal-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if ($entity->type === 'EMPLOYEE')
                                        <i class="fas fa-user mr-1"></i>
                                    @elseif($entity->type === 'STUDENT')
                                        <i class="fas fa-graduation-cap mr-1"></i>
                                    @elseif($entity->type === 'COMPANY')
                                        <i class="fas fa-building mr-1"></i>
                                    @elseif($entity->type === 'COURSE')
                                        <i class="fas fa-book mr-1"></i>
                                    @elseif($entity->type === 'VEHICLE')
                                        <i class="fas fa-car mr-1"></i>
                                    @elseif($entity->type === 'SHIP')
                                        <i class="fas fa-ship mr-1"></i>
                                    @endif
                                    {{ $entity->type }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
