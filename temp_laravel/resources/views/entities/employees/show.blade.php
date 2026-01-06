@extends('layouts.app')

@section('title', 'Employee Details - PolicyZen')
@section('page-title')
<div class="flex items-center">
    <a href="{{ route('entities.employees.index') }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Employee Details</span>
</div>
@endsection

@section('header-actions')
<a href="{{ route('entities.employees.edit', $employee) }}" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
    <i class="fas fa-edit mr-2"></i>Edit Employee
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Employee Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Employee Information</h2>
                    <span class="px-3 py-1 text-sm rounded-full
                        @if($employee->status === 'ACTIVE') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $employee->status }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee Code</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $employee->employee_code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <p class="text-gray-900">{{ $employee->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <p class="text-gray-900">{{ $employee->department ?: 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <p class="text-gray-900">{{ $employee->position ?: 'N/A' }}</p>
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
                <a href="{{ route('entities.employees.edit', $employee) }}" class="w-full bg-[#f06e11] text-white px-4 py-3 rounded-lg hover:bg-[#f28e1f] flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>Edit Employee
                </a>

                <form method="POST" action="{{ route('entities.employees.destroy', $employee) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this employee? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Delete Employee
                    </button>
                </form>
            </div>
        </div>

        <!-- Employee Stats -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Employee Stats</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Created</span>
                    <span class="font-medium">{{ $employee->created_at ? $employee->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Updated</span>
                    <span class="font-medium">{{ $employee->updated_at ? $employee->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
