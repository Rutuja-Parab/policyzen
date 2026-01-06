@extends('layouts.app')

@section('title', 'Edit Course - PolicyZen')
@section('page-title', 'Edit Course')

@section('header-actions')
<a href="{{ route('entities.courses.show', $course) }}" class="text-gray-400 hover:text-gray-600 mr-4">
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
        <form method="POST" action="{{ route('entities.courses.update', $course) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course Code *</label>
                    <input type="text" name="course_code" value="{{ old('course_code', $course->course_code) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course Name *</label>
                    <input type="text" name="course_name" value="{{ old('course_name', $course->course_name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">{{ old('description', $course->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company *</label>
                    <select name="company_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $course->company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <input type="text" name="department" value="{{ old('department', $course->department) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Months)</label>
                    <input type="number" name="duration_months" value="{{ old('duration_months', $course->duration_months) }}" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <option value="ACTIVE" {{ old('status', $course->status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ old('status', $course->status) === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('entities.courses.show', $course) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                    <i class="fas fa-save mr-2"></i>Update Course
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

