@extends('layouts.app')

@section('title', 'Entity Management - PolicyZen')
@section('page-title', 'Entity Management')

@section('content')
    <!-- Entity Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Employees Card -->
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#04315b]/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user text-[#04315b] text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['employees'] ?? 0 }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Employees</h3>
                <p class="text-sm text-gray-600 mb-4">Manage employee records</p>
                <div class="flex space-x-2">
                    <a href="{{ route('entities.employees.index') }}"
                        class="flex-1 bg-[#04315b] text-white text-center py-2 px-3 rounded-lg hover:bg-[#2b8bd0] text-sm">
                        <i class="fas fa-list mr-1"></i>View All
                    </a>
                    <a href="{{ route('entities.employees.create') }}"
                        class="bg-[#f06e11] text-white p-2 rounded-lg hover:bg-[#f28e1f]">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Students Card -->
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#f06e11]/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-[#f06e11] text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['students'] ?? 0 }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Students</h3>
                <p class="text-sm text-gray-600 mb-4">Academic records management</p>
                <div class="flex space-x-2">
                    <a href="{{ route('entities.students.index') }}"
                        class="flex-1 bg-[#f06e11] text-white text-center py-2 px-3 rounded-lg hover:bg-[#f28e1f] text-sm">
                        <i class="fas fa-list mr-1"></i>View All
                    </a>
                    <a href="{{ route('entities.students.create') }}"
                        class="bg-[#2b8bd0] text-white p-2 rounded-lg hover:bg-[#04315b]">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vessels Card -->
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#2b8bd0]/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ship text-[#2b8bd0] text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['vessels'] ?? 0 }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Vessels</h3>
                <p class="text-sm text-gray-600 mb-4">Marine asset management</p>
                <div class="flex space-x-2">
                    <a href="{{ route('entities.vessels.index') }}"
                        class="flex-1 bg-[#2b8bd0] text-white text-center py-2 px-3 rounded-lg hover:bg-[#04315b] text-sm">
                        <i class="fas fa-list mr-1"></i>View All
                    </a>
                    <a href="{{ route('entities.vessels.create') }}"
                        class="bg-[#f06e11] text-white p-2 rounded-lg hover:bg-[#f28e1f]">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vehicles Card -->
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-[#f28e1f]/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-car text-[#f28e1f] text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ $stats['vehicles'] ?? 0 }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Vehicles</h3>
                <p class="text-sm text-gray-600 mb-4">Automotive asset management</p>
                <div class="flex space-x-2">
                    <a href="{{ route('entities.vehicles.index') }}"
                        class="flex-1 bg-[#f28e1f] text-white text-center py-2 px-3 rounded-lg hover:bg-[#f06e11] text-sm">
                        <i class="fas fa-list mr-1"></i>View All
                    </a>
                    <a href="{{ route('entities.vehicles.create') }}"
                        class="bg-[#2b8bd0] text-white p-2 rounded-lg hover:bg-[#04315b]">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Entities -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Recent Entities</h2>
            <p class="text-sm text-gray-600">Latest additions across all entity types</p>
        </div>

        <div class="p-6">
            @if (count($recentEntities ?? []) > 0)
                <div class="space-y-4">
                    @foreach ($recentEntities as $entity)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center
                                        @if ($entity['type'] === 'EMPLOYEE') bg-[#04315b]/10
                                        @elseif($entity['type'] === 'STUDENT') bg-[#f06e11]/10
                                        @elseif($entity['type'] === 'SHIP') bg-[#2b8bd0]/10
                                        @else bg-[#f28e1f]/10 @endif">
                                    @if ($entity['type'] === 'EMPLOYEE')
                                        <i class="fas fa-user text-[#04315b]"></i>
                                    @elseif($entity['type'] === 'STUDENT')
                                        <i class="fas fa-graduation-cap text-[#f06e11]"></i>
                                    @elseif($entity['type'] === 'SHIP')
                                        <i class="fas fa-ship text-[#2b8bd0]"></i>
                                    @else
                                        <i class="fas fa-car text-[#f28e1f]"></i>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $entity['name'] }}</h3>
                                    <p class="text-sm text-gray-600">{{ $entity['type'] }} • {{ $entity['code'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span
                                    class="px-2 py-1 text-xs rounded-full
                                        @if ($entity['status'] === 'ACTIVE') bg-[#f06e11]/10 text-[#f06e11]
                                        @else bg-gray-100 text-gray-800 @endif">
                                    {{ $entity['status'] }}
                                </span>
                                <span class="text-sm text-gray-500">{{ $entity['created_at'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No entities found</h3>
                    <p class="text-gray-500 mb-4">Get started by adding your first entity.</p>
                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('entities.employees.create') }}"
                            class="bg-[#04315b] text-white px-4 py-2 rounded-lg hover:bg-[#2b8bd0]">
                            <i class="fas fa-plus mr-2"></i>Add Employee
                        </a>
                        <a href="{{ route('entities.students.create') }}"
                            class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                            <i class="fas fa-plus mr-2"></i>Add Student
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
