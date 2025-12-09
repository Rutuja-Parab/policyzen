@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('page-title', 'Audit Log Details')

@section('header-actions')
<a href="{{ route('audit-logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
    <i class="fas fa-arrow-left mr-2"></i>
    Back to Logs
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Audit Log #{{ $auditLog->id }}</h1>
                <p class="mt-1 text-sm text-gray-500">Created on {{ $auditLog->created_at->format('F d, Y at h:i A') }}</p>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                    {{ $auditLog->action == 'CREATE' ? 'bg-green-100 text-green-800' : 
                       ($auditLog->action == 'UPDATE' ? 'bg-blue-100 text-blue-800' : 
                       ($auditLog->action == 'DELETE' ? 'bg-red-100 text-red-800' : 
                       ($auditLog->action == 'STATUS_CHANGE' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))) }}">
                    {{ $auditLog->action }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Action</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->action }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Entity Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->entity_type ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Entity ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->entity_id ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Transaction Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->transaction_type ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($auditLog->amount)
                            ${{ number_format($auditLog->amount, 2) }}
                        @else
                            N/A
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- User Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Performed By</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($auditLog->performer)
                            {{ $auditLog->performer->name }}
                            <div class="text-xs text-gray-500">{{ $auditLog->performer->email }}</div>
                        @else
                            System
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->created_at->format('F d, Y h:i:s A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->updated_at->format('F d, Y h:i:s A') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Related Records -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Related Records</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Policy Information -->
            @if($auditLog->policy)
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-2">Related Policy</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Policy #{{ $auditLog->policy->id }}</p>
                                <p class="text-sm text-gray-500">{{ $auditLog->policy->policy_number }}</p>
                            </div>
                            <a href="{{ route('policies.show', $auditLog->policy) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                View Policy
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Endorsement Information -->
            @if($auditLog->endorsement)
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-2">Related Endorsement</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Endorsement #{{ $auditLog->endorsement->id }}</p>
                                <p class="text-sm text-gray-500">{{ $auditLog->endorsement->endorsement_number }}</p>
                            </div>
                            <a href="{{ route('endorsements.show', $auditLog->endorsement) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                View Endorsement
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Financial Information -->
    @if($auditLog->amount || $auditLog->balance_before || $auditLog->balance_after)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($auditLog->balance_before !== null)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Balance Before</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($auditLog->balance_before, 2) }}</dd>
                    </div>
                @endif
                @if($auditLog->amount)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Transaction Amount</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($auditLog->amount, 2) }}</dd>
                    </div>
                @endif
                @if($auditLog->balance_after !== null)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Balance After</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($auditLog->balance_after, 2) }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif

    <!-- Additional Metadata -->
    @if($auditLog->metadata)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    <!-- Entity Information -->
    @if($auditLog->entity)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Entity Information</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-700">
                    <strong>Type:</strong> {{ $auditLog->entity_type }}<br>
                    <strong>ID:</strong> {{ $auditLog->entity_id }}<br>
                    <strong>Details:</strong> {{ $auditLog->entity->name ?? $auditLog->entity->title ?? 'N/A' }}
                </p>
            </div>
        </div>
    @endif
</div>
@endsection