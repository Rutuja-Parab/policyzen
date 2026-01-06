@extends('layouts.app')

@section('title', 'Company Details - PolicyZen')
@section('page-title', 'Company Details')

@section('header-actions')
<a href="{{ route('companies.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
    <i class="fas fa-arrow-left"></i>
</a>
<a href="{{ route('companies.edit', $company) }}" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
    <i class="fas fa-edit mr-2"></i>Edit Company
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Company Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Company Information</h2>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $company->name }}</p>
                    </div>

                    @if($company->parentCompany)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Parent Company</label>
                        <p class="text-gray-900">
                            <a href="{{ route('companies.show', $company->parentCompany) }}" class="text-[#f06e11] hover:underline">
                                {{ $company->parentCompany->name }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($company->childCompanies->count() > 0)
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Subsidiaries</h3>
            </div>
            <div class="p-6">
                <div class="space-y-2">
                    @foreach($company->childCompanies as $child)
                    <a href="{{ route('companies.show', $child) }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 text-gray-900">
                        {{ $child->name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div>
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>

            <div class="p-6 space-y-4">
                <a href="{{ route('companies.edit', $company) }}" class="w-full bg-[#f06e11] text-white px-4 py-3 rounded-lg hover:bg-[#f28e1f] flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>Edit Company
                </a>

                <form method="POST" action="{{ route('companies.destroy', $company) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Delete Company
                    </button>
                </form>
            </div>
        </div>

        <!-- Company Stats -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Company Stats</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Employees</span>
                    <span class="font-medium">{{ $company->employees->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Students</span>
                    <span class="font-medium">{{ $company->students->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Courses</span>
                    <span class="font-medium">{{ $company->courses->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Created</span>
                    <span class="font-medium">{{ $company->created_at ? $company->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

