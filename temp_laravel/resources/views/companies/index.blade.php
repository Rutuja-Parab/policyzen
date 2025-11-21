@extends('layouts.app')

@section('title', 'Companies - PolicyZen')
@section('page-title', 'Company Management')

@section('header-actions')
<a href="{{ route('companies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-plus mr-2"></i>Add Company
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

<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Companies</h2>
                <p class="text-sm text-gray-500">Manage organizations and their insurance coverage</p>
            </div>
            <a href="{{ route('companies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Company
            </a>
        </div>
    </div>

    <div class="p-6">
        @if($companies->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($companies as $company)
            <div class="bg-gray-50 rounded-lg p-4 border">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-gray-900">{{ $company->name }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                        Active
                    </span>
                </div>

                <div class="space-y-2 text-sm">
                    @if($company->parentCompany)
                    <p><span class="font-medium">Parent:</span> {{ $company->parentCompany->name }}</p>
                    @endif
                    <p><span class="font-medium">Users:</span> {{ $company->users->count() }}</p>
                    <p><span class="font-medium">Employees:</span> {{ $company->employees->count() }}</p>
                    <p><span class="font-medium">Students:</span> {{ $company->students->count() }}</p>
                    <p><span class="font-medium">Courses:</span> {{ $company->courses->count() }}</p>
                    @if($company->childCompanies->count() > 0)
                    <p><span class="font-medium">Subsidiaries:</span> {{ $company->childCompanies->count() }}</p>
                    @endif
                </div>

                <div class="flex space-x-2 mt-4">
                    <a href="{{ route('companies.show', $company) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <a href="{{ route('companies.edit', $company) }}" class="text-green-600 hover:text-green-800 text-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this company? This will also remove it from insurance coverage.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-building text-gray-300 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No companies found</h3>
            <p class="text-gray-500 mb-4">Create your first company to get started with insurance coverage.</p>
            <a href="{{ route('companies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Company
            </a>
        </div>
        @endif
    </div>
</div>
@endsection