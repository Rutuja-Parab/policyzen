@extends('layouts.app')

@section('title', 'Policy Management - PolicyZen')
@section('page-title', 'Policy Management')

@section('header-actions')
<a href="{{ route('policies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-plus mr-2"></i>Create Policy
</a>
@endsection

@section('content')
                @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
                @endif

                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900">Insurance Policies</h2>
                                <p class="text-sm text-gray-500">Create and manage insurance policies</p>
                            </div>
                            <a href="{{ route('policies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>Create Policy
                            </a>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input type="text" placeholder="Search policies..." class="border border-gray-300 rounded-lg px-3 py-2">
                            <select class="border border-gray-300 rounded-lg px-3 py-2">
                                <option>All Statuses</option>
                                <option>Active</option>
                                <option>Expired</option>
                                <option>Under Review</option>
                                <option>Cancelled</option>
                            </select>
                            <select class="border border-gray-300 rounded-lg px-3 py-2">
                                <option>All Types</option>
                                <option>Health</option>
                                <option>Accident</option>
                                <option>Property</option>
                                <option>Vehicle</option>
                                <option>Marine</option>
                            </select>
                            <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                        </div>
                    </div>

                    <!-- Policy Grid -->
                    <div class="p-6">
                        @if($policies->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($policies as $policy)
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-medium text-gray-900">{{ $policy->policy_number }}</h3>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($policy->status === 'ACTIVE') bg-green-100 text-green-800
                                        @elseif($policy->status === 'EXPIRED') bg-red-100 text-red-800
                                        @elseif($policy->status === 'UNDER_REVIEW') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $policy->status }}
                                    </span>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Type:</span> {{ $policy->insurance_type }}</p>
                                    <p><span class="font-medium">Provider:</span> {{ $policy->provider }}</p>
                                    <p><span class="font-medium">Premium:</span> ${{ number_format($policy->premium_amount, 2) }}</p>
                                    <p><span class="font-medium">Sum Insured:</span> ${{ number_format($policy->sum_insured, 2) }}</p>
                                    <p><span class="font-medium">Covered Entities:</span> {{ $policy->entities->count() }} entities</p>
                                    @if($policy->entities->count() > 0)
                                    <div class="mt-1">
                                        <p class="text-xs text-gray-600">
                                            @foreach($policy->entities->take(2) as $entity)
                                            {{ $entity->description }}@if(!$loop->last), @endif
                                            @endforeach
                                            @if($policy->entities->count() > 2)
                                            +{{ $policy->entities->count() - 2 }} more
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                    <p><span class="font-medium">Valid:</span> {{ $policy->start_date }} to {{ $policy->end_date }}</p>
                                </div>

                                <div class="flex space-x-2 mt-4">
                                    <a href="{{ route('policies.show', $policy) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    <a href="{{ route('policies.edit', $policy) }}" class="text-green-600 hover:text-green-800 text-sm">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <form method="POST" action="{{ route('policies.destroy', $policy) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this policy?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-12">
                            <i class="fas fa-file-alt text-gray-300 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No policies found</h3>
                            <p class="text-gray-500 mb-4">Create your first insurance policy to get started.</p>
                            <a href="{{ route('policies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>Create Policy
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
@endsection