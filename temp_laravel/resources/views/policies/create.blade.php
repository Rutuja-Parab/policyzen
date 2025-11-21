@extends('layouts.app')

@section('title', 'Create Policy - PolicyZen')
@section('page-title', 'Create New Policy')

@section('header-actions')
<a href="{{ route('policies.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
    <i class="fas fa-arrow-left"></i>
</a>
@endsection

@section('content')
                @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <form method="POST" action="{{ route('policies.store') }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Covered Entities *</label>
                                <p class="text-xs text-gray-500 mb-2">Select entities to cover under this group policy</p>
                                <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Employees -->
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                                                <i class="fas fa-user mr-2 text-blue-600"></i>Employees
                                            </h4>
                                            @foreach($entities->where('type', 'EMPLOYEE') as $entity)
                                            <label class="flex items-center space-x-2 mb-2">
                                                <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm">{{ $entity->description }}</span>
                                            </label>
                                            @endforeach
                                            @if($entities->where('type', 'EMPLOYEE')->count() === 0)
                                            <p class="text-sm text-gray-500 italic">No employees available</p>
                                            @endif
                                        </div>

                                        <!-- Students -->
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                                                <i class="fas fa-graduation-cap mr-2 text-green-600"></i>Students
                                            </h4>
                                            @foreach($entities->where('type', 'STUDENT') as $entity)
                                            <label class="flex items-center space-x-2 mb-2">
                                                <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm">{{ $entity->description }}</span>
                                            </label>
                                            @endforeach
                                            @if($entities->where('type', 'STUDENT')->count() === 0)
                                            <p class="text-sm text-gray-500 italic">No students available</p>
                                            @endif
                                        </div>

                                        <!-- Companies -->
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                                                <i class="fas fa-building mr-2 text-purple-600"></i>Companies
                                            </h4>
                                            @foreach($entities->where('type', 'COMPANY') as $entity)
                                            <label class="flex items-center space-x-2 mb-2">
                                                <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm">{{ $entity->description }}</span>
                                            </label>
                                            @endforeach
                                            @if($entities->where('type', 'COMPANY')->count() === 0)
                                            <p class="text-sm text-gray-500 italic">No companies available</p>
                                            @endif
                                        </div>

                                        <!-- Courses -->
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                                                <i class="fas fa-book mr-2 text-orange-600"></i>Courses
                                            </h4>
                                            @foreach($entities->where('type', 'COURSE') as $entity)
                                            <label class="flex items-center space-x-2 mb-2">
                                                <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm">{{ $entity->description }}</span>
                                            </label>
                                            @endforeach
                                            @if($entities->where('type', 'COURSE')->count() === 0)
                                            <p class="text-sm text-gray-500 italic">No courses available</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Policy Number *</label>
                                    <input type="text" name="policy_number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Insurance Type *</label>
                                    <select name="insurance_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="HEALTH">Health Insurance</option>
                                        <option value="ACCIDENT">Accident Insurance</option>
                                        <option value="PROPERTY">Property Insurance</option>
                                        <option value="VEHICLE">Vehicle Insurance</option>
                                        <option value="MARINE">Marine Insurance</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Provider *</label>
                                    <input type="text" name="provider" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                                    <input type="date" name="start_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                                    <input type="date" name="end_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sum Insured ($)</label>
                                    <input type="number" name="sum_insured" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Premium Amount ($)</label>
                                    <input type="number" name="premium_amount" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-4">
                                <a href="{{ route('policies.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-save mr-2"></i>Create Policy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
@endsection