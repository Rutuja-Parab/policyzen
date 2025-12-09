@extends('layouts.app')

@section('title', 'Vessel Details - PolicyZen')
@section('page-title')
<div class="flex items-center">
    <a href="{{ route('entities.vessels.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Vessel Details</span>
</div>
@endsection

@section('header-actions')
<a href="{{ route('entities.vessels.edit', $vessel) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-edit mr-2"></i>Edit Vessel
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Vessel Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Vessel Information</h2>
                    <span class="px-3 py-1 text-sm rounded-full
                        @if($vessel->status === 'ACTIVE') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $vessel->status }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vessel Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $vessel->vessel_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">IMO Number</label>
                        <p class="text-gray-900">{{ $vessel->imo_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vessel Type</label>
                        <p class="text-gray-900">{{ $vessel->vessel_type ?: 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Flag</label>
                        <p class="text-gray-900">{{ $vessel->flag ?: 'N/A' }}</p>
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
                <a href="{{ route('entities.vessels.edit', $vessel) }}" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>Edit Vessel
                </a>

                <form method="POST" action="{{ route('entities.vessels.destroy', $vessel) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this vessel? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Delete Vessel
                    </button>
                </form>
            </div>
        </div>

        <!-- Vessel Stats -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Vessel Stats</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Created</span>
                    <span class="font-medium">{{ $vessel->created_at ? $vessel->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Updated</span>
                    <span class="font-medium">{{ $vessel->updated_at ? $vessel->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

