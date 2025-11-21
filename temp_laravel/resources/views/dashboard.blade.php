@extends('layouts.app')

@section('title', 'Dashboard - PolicyZen')
@section('page-title', 'Dashboard')

@section('content')
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden mb-8">
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
                        <p class="text-blue-100 text-lg">Here's your insurance portfolio overview</p>
                    </div>
                    <div class="absolute top-0 right-0 w-64 h-64 opacity-10">
                        <i class="fas fa-shield-alt w-full h-full"></i>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Policy Status Distribution -->
                    <div class="bg-white rounded-lg shadow p-6 md:col-span-2 lg:col-span-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Policy Status Distribution</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700">Active Policies</span>
                                    <span class="text-green-600 font-semibold">{{ $stats['active_policies'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['total_policies'] > 0 ? ($stats['active_policies'] / $stats['total_policies']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700">Expired Policies</span>
                                    <span class="text-red-600 font-semibold">{{ $stats['expired_policies'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full" style="width: {{ $stats['total_policies'] > 0 ? ($stats['expired_policies'] / $stats['total_policies']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700">Under Review</span>
                                    <span class="text-yellow-600 font-semibold">{{ $stats['total_policies'] - $stats['active_policies'] - $stats['expired_policies'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $stats['total_policies'] > 0 ? (($stats['total_policies'] - $stats['active_policies'] - $stats['expired_policies']) / $stats['total_policies']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        @if($stats['total_policies'] > 0)
                        <div class="mt-4 pt-4 border-t">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ round(($stats['active_policies'] / $stats['total_policies']) * 100) }}%</p>
                                <p class="text-sm text-gray-600">Policy Success Rate</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Detailed Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Policies</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_policies']) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Entities</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_entities']) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Premium</p>
                                <p class="text-3xl font-bold text-gray-900">${{ number_format($stats['total_premium'], 2) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-purple-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Active Policies</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['active_policies']) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-emerald-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Expired Policies</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['expired_policies']) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-times-circle text-red-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Recent Endorsements</p>
                                <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['recent_endorsements']) }}</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-edit text-orange-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Policies and Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Policies -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-medium text-gray-900">Recent Policies</h2>
                            <a href="{{ route('policies.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View All
                            </a>
                        </div>

                        @if($recentPolicies->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentPolicies as $policy)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ $policy->policy_number }}</p>
                                    <p class="text-xs text-gray-600">{{ $policy->insurance_type }} • {{ $policy->entity ? $policy->entity->description : 'N/A' }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($policy->status === 'ACTIVE') bg-green-100 text-green-800
                                        @elseif($policy->status === 'EXPIRED') bg-red-100 text-red-800
                                        @elseif($policy->status === 'UNDER_REVIEW') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $policy->status }}
                                    </span>
                                    <a href="{{ route('policies.show', $policy) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm mb-4">No policies found</p>
                            <a href="{{ route('policies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                                Create First Policy
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Quick Actions</h2>
                        <div class="grid grid-cols-1 gap-4">
                            <a href="{{ route('policies.create') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white p-4 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus mr-2"></i>
                                <span>New Policy</span>
                            </a>

                            <a href="{{ route('entities.index') }}" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white p-4 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-plus mr-2"></i>
                                <span>Add Entity</span>
                            </a>

                            <a href="{{ route('endorsements.create') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white p-4 rounded-lg flex items-center justify-center">
                                <i class="fas fa-edit mr-2"></i>
                                <span>Create Endorsement</span>
                            </a>

                            <a href="{{ route('reports.index') }}" class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white p-4 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line mr-2"></i>
                                <span>View Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
@endsection
