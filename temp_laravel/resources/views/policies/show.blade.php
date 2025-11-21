@extends('layouts.app')

@section('title', 'Policy Details - PolicyZen')
@section('page-title', 'Policy Details')

@section('header-actions')
<a href="{{ route('policies.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
    <i class="fas fa-arrow-left"></i>
</a>
<a href="{{ route('policies.edit', $policy) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
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
                        @if($policy->entities->count() > 0)
                        <div class="space-y-4">
                            @foreach($policy->entities as $entity)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                                        @if($entity->type === 'EMPLOYEE') bg-blue-100 text-blue-600
                                        @elseif($entity->type === 'STUDENT') bg-green-100 text-green-600
                                        @elseif($entity->type === 'COMPANY') bg-purple-100 text-purple-600
                                        @elseif($entity->type === 'COURSE') bg-orange-100 text-orange-600
                                        @else bg-gray-100 text-gray-600 @endif">
                                        @if($entity->type === 'EMPLOYEE')
                                        <i class="fas fa-user"></i>
                                        @elseif($entity->type === 'STUDENT')
                                        <i class="fas fa-graduation-cap"></i>
                                        @elseif($entity->type === 'COMPANY')
                                        <i class="fas fa-building"></i>
                                        @elseif($entity->type === 'COURSE')
                                        <i class="fas fa-book"></i>
                                        @else
                                        <i class="fas fa-question"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $entity->description }}</h3>
                                        <p class="text-sm text-gray-600">{{ $entity->type }}</p>
                                        <p class="text-xs text-gray-500">
                                            Added: {{ $entity->pivot->effective_date ? \Carbon\Carbon::parse($entity->pivot->effective_date)->format('M d, Y') : 'N/A' }}
                                            @if($entity->pivot->termination_date)
                                            | Removed: {{ \Carbon\Carbon::parse($entity->pivot->termination_date)->format('M d, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if($entity->pivot->status === 'ACTIVE')
                                <form method="POST" action="{{ route('policies.remove-entity', $policy) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm"
                                            onclick="return confirm('Are you sure you want to remove this entity from the policy?')">
                                        <i class="fas fa-times mr-1"></i>Remove
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-gray-500">Terminated</span>
                                @endif
                            </div>
                            @endforeach
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
                                        <p class="text-2xl font-bold text-blue-600">${{ number_format($policy->premium_amount, 2) }}</p>
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
                                <a href="{{ route('policies.edit', $policy) }}" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">
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

                <!-- Add Entity Modal -->
                <div id="add-entity-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Add Entity to Policy</h3>
                                <button onclick="document.getElementById('add-entity-modal').classList.add('hidden')"
                                        class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('policies.add-entity', $policy) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Entity</label>
                                    <select name="entity_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Choose an entity...</option>
                                        @php
                                        $availableEntities = \App\Models\Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'COMPANY', 'COURSE'])
                                            ->whereDoesntHave('policies', function($query) use ($policy) {
                                                $query->where('policy_id', $policy->id)->where('status', 'ACTIVE');
                                            })->get();
                                        @endphp
                                        @foreach($availableEntities as $entity)
                                        <option value="{{ $entity->id }}">{{ $entity->description }} ({{ $entity->type }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex justify-end space-x-4">
                                    <button type="button" onclick="document.getElementById('add-entity-modal').classList.add('hidden')"
                                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                                        Cancel
                                    </button>
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                        <i class="fas fa-plus mr-2"></i>Add Entity
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
@endsection