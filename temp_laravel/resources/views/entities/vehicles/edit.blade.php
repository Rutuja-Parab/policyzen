@extends('layouts.app')

@section('title', 'Edit Vehicle - PolicyZen')
@section('page-title')
<div class="flex items-center">
    <a href="{{ route('entities.vehicles.show', $vehicle) }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Edit Vehicle</span>
</div>
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
        <form method="POST" action="{{ route('entities.vehicles.update', $vehicle) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company *</label>
                    <select name="company_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $vehicle->company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number *</label>
                    <input type="text" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Make *</label>
                    <input type="text" name="make" value="{{ old('make', $vehicle->make) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Model *</label>
                    <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Year *</label>
                    <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" required min="1900" max="{{ date('Y') + 1 }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <option value="ACTIVE" {{ old('status', $vehicle->status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ old('status', $vehicle->status) === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('entities.vehicles.show', $vehicle) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                    <i class="fas fa-save mr-2"></i>Update Vehicle
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

