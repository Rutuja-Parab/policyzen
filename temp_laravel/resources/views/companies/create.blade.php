@extends('layouts.app')

@section('title', 'Add Company - PolicyZen')
@section('page-title', 'Add New Company')

@section('header-actions')
<a href="{{ route('companies.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
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
        <form method="POST" action="{{ route('companies.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter company name">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent Company</label>
                    <select name="parent_company_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select parent company (optional)</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Leave empty if this is a top-level company</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('companies.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Create Company
                </button>
            </div>
        </form>
    </div>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
    <div class="flex">
        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
        <div>
            <h4 class="text-sm font-medium text-blue-800">Insurance Coverage</h4>
            <p class="text-sm text-blue-700 mt-1">
                Once created, this company can be added to insurance policies for comprehensive business coverage.
                You can also create employees, students, and courses under this company.
            </p>
        </div>
    </div>
</div>
@endsection