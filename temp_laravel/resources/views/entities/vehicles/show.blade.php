@extends('layouts.app')

@section('title', 'Vehicle Details - PolicyZen')
@section('page-title')
<div class="flex items-center">
    <a href="{{ route('entities.vehicles.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Vehicle Details</span>
</div>
@endsection

@section('header-actions')
<a href="{{ route('entities.vehicles.edit', $vehicle) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-edit mr-2"></i>Edit Vehicle
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Vehicle Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Vehicle Information</h2>
                    <span class="px-3 py-1 text-sm rounded-full
                        @if($vehicle->status === 'ACTIVE') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $vehicle->status }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $vehicle->registration_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Make</label>
                        <p class="text-gray-900">{{ $vehicle->make }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <p class="text-gray-900">{{ $vehicle->model }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                        <p class="text-gray-900">{{ $vehicle->year }}</p>
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
                <a href="{{ route('entities.vehicles.edit', $vehicle) }}" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>Edit Vehicle
                </a>

                <form method="POST" action="{{ route('entities.vehicles.destroy', $vehicle) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Delete Vehicle
                    </button>
                </form>
            </div>
        </div>

        <!-- Vehicle Stats -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Vehicle Stats</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Created</span>
                    <span class="font-medium">{{ $vehicle->created_at ? $vehicle->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Updated</span>
                    <span class="font-medium">{{ $vehicle->updated_at ? $vehicle->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

