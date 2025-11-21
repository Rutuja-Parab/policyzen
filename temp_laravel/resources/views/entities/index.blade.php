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
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600 text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">{{ $stats['employees'] ?? 0 }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Employees</h3>
                            <p class="text-sm text-gray-600 mb-4">Manage employee records</p>
                            <div class="flex space-x-2">
                                <a href="{{ route('entities.employees.index') }}" class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded-lg hover:bg-blue-700 text-sm">
                                    <i class="fas fa-list mr-1"></i>View All
                                </a>
                                <a href="{{ route('entities.employees.create') }}" class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Students Card -->
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-graduation-cap text-emerald-600 text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">{{ $stats['students'] ?? 0 }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Students</h3>
                            <p class="text-sm text-gray-600 mb-4">Academic records management</p>
                            <div class="flex space-x-2">
                                <a href="{{ route('entities.students.index') }}" class="flex-1 bg-emerald-600 text-white text-center py-2 px-3 rounded-lg hover:bg-emerald-700 text-sm">
                                    <i class="fas fa-list mr-1"></i>View All
                                </a>
                                <a href="{{ route('entities.students.create') }}" class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Vessels Card -->
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-ship text-purple-600 text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">{{ $stats['vessels'] ?? 0 }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Vessels</h3>
                            <p class="text-sm text-gray-600 mb-4">Marine asset management</p>
                            <div class="flex space-x-2">
                                <a href="{{ route('entities.vessels.index') }}" class="flex-1 bg-purple-600 text-white text-center py-2 px-3 rounded-lg hover:bg-purple-700 text-sm">
                                    <i class="fas fa-list mr-1"></i>View All
                                </a>
                                <a href="{{ route('entities.vessels.create') }}" class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicles Card -->
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-car text-orange-600 text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold text-gray-900">{{ $stats['vehicles'] ?? 0 }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Vehicles</h3>
                            <p class="text-sm text-gray-600 mb-4">Automotive asset management</p>
                            <div class="flex space-x-2">
                                <a href="{{ route('entities.vehicles.index') }}" class="flex-1 bg-orange-600 text-white text-center py-2 px-3 rounded-lg hover:bg-orange-700 text-sm">
                                    <i class="fas fa-list mr-1"></i>View All
                                </a>
                                <a href="{{ route('entities.vehicles.create') }}" class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700">
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
                        @if(count($recentEntities ?? []) > 0)
                        <div class="space-y-4">
                            @foreach($recentEntities as $entity)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                        @if($entity['type'] === 'EMPLOYEE') bg-blue-100
                                        @elseif($entity['type'] === 'STUDENT') bg-emerald-100
                                        @elseif($entity['type'] === 'SHIP') bg-purple-100
                                        @else bg-orange-100 @endif">
                                        @if($entity['type'] === 'EMPLOYEE')
                                            <i class="fas fa-user text-blue-600"></i>
                                        @elseif($entity['type'] === 'STUDENT')
                                            <i class="fas fa-graduation-cap text-emerald-600"></i>
                                        @elseif($entity['type'] === 'SHIP')
                                            <i class="fas fa-ship text-purple-600"></i>
                                        @else
                                            <i class="fas fa-car text-orange-600"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $entity['name'] }}</h3>
                                        <p class="text-sm text-gray-600">{{ $entity['type'] }} • {{ $entity['code'] }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($entity['status'] === 'ACTIVE') bg-green-100 text-green-800
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
                                <a href="{{ route('entities.employees.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>Add Employee
                                </a>
                                <a href="{{ route('entities.students.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                                    <i class="fas fa-plus mr-2"></i>Add Student
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
@endsection