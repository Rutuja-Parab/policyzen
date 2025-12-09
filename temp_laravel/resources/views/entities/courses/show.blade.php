@extends('layouts.app')

@section('title', 'Course Details - PolicyZen')
@section('page-title', 'Course Details')

@section('header-actions')
<a href="{{ route('entities.courses.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
    <i class="fas fa-arrow-left"></i>
</a>
<a href="{{ route('entities.courses.edit', $course) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-edit mr-2"></i>Edit Course
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Course Information -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Course Information</h2>
                    <span class="px-3 py-1 text-sm rounded-full
                        @if($course->status === 'ACTIVE') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $course->status }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course Code</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $course->course_code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course Name</label>
                        <p class="text-gray-900">{{ $course->course_name }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <p class="text-gray-900">{{ $course->description ?: 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <p class="text-gray-900">{{ $course->department ?: 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Months)</label>
                        <p class="text-gray-900">{{ $course->duration_months ?: 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                        <p class="text-gray-900">
                            @if($course->company)
                            <a href="{{ route('companies.show', $course->company) }}" class="text-blue-600 hover:underline">
                                {{ $course->company->name }}
                            </a>
                            @else
                            N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div>
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>

            <div class="p-6 space-y-4">
                <a href="{{ route('entities.courses.edit', $course) }}" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>Edit Course
                </a>

                <form method="POST" action="{{ route('entities.courses.destroy', $course) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Delete Course
                    </button>
                </form>
            </div>
        </div>

        <!-- Course Stats -->
        <div class="bg-white rounded-lg shadow mt-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Course Stats</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Created</span>
                    <span class="font-medium">{{ $course->created_at ? $course->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Updated</span>
                    <span class="font-medium">{{ $course->updated_at ? $course->updated_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

