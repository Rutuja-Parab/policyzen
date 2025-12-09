@extends('layouts.app')

@section('title', 'Courses - PolicyZen')
@section('page-title', 'Course Management')

@section('header-actions')
<a href="{{ route('entities.courses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-plus mr-2"></i>Add Course
</a>
@endsection

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Courses</h2>
                <p class="text-sm text-gray-500">Manage educational courses and programs</p>
            </div>
            <a href="{{ route('entities.courses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Course
            </a>
        </div>
    </div>

    <div class="p-6">
        @if($courses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
            <div class="bg-gray-50 rounded-lg p-4 border">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-gray-900">{{ $course->course_name }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($course->status === 'ACTIVE') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $course->status }}
                    </span>
                </div>

                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">Code:</span> {{ $course->course_code }}</p>
                    <p><span class="font-medium">Company:</span> {{ $course->company->name }}</p>
                    @if($course->department)
                    <p><span class="font-medium">Department:</span> {{ $course->department }}</p>
                    @endif
                    @if($course->duration_months)
                    <p><span class="font-medium">Duration:</span> {{ $course->duration_months }} months</p>
                    @endif
                </div>

                <div class="flex space-x-2 mt-4">
                    <a href="{{ route('entities.courses.show', $course) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <a href="{{ route('entities.courses.edit', $course) }}" class="text-green-600 hover:text-green-800 text-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('entities.courses.destroy', $course) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this course?')">
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
            <i class="fas fa-book text-gray-300 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No courses found</h3>
            <p class="text-gray-500 mb-4">Create your first course to get started.</p>
            <a href="{{ route('entities.courses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Course
            </a>
        </div>
        @endif
    </div>
</div>
@endsection