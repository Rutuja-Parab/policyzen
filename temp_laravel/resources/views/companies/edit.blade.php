@extends('layouts.app')

@section('title', 'Edit Company - PolicyZen')
@section('page-title', 'Edit Company')

@section('header-actions')
<a href="{{ route('companies.show', $company) }}" class="text-gray-400 hover:text-gray-600 mr-4">
    <i class="fas fa-arrow-left"></i>
</a>
@endsection

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
    {{ session('error') }}
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
        <form method="POST" action="{{ route('companies.update', $company) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent Company</label>
                    <select name="parent_company_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <option value="">None (Top-level company)</option>
                        @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ old('parent_company_id', $company->parent_company_id) == $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <option value="active" {{ old('status', $company->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $company->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Inactive companies won't be available for insurance coverage</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('companies.show', $company) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                    <i class="fas fa-save mr-2"></i>Update Company
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

