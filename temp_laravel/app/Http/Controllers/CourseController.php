<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Company;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    // Web methods for views
    public function webIndex()
    {
        $courses = Course::with('company')->orderBy('created_at', 'desc')->get();
        return view('entities.courses.index', compact('courses'));
    }

    public function webCreate()
    {
        $companies = Company::all();
        return view('entities.courses.create', compact('companies'));
    }

    public function webStore(Request $request)
    {
        $request->validate([
            'course_code' => 'required|string|unique:courses',
            'course_name' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
            'department' => 'nullable|string',
            'duration_months' => 'nullable|integer|min:1',
            'course_fee' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $course = Course::create([
                'id' => (string) Str::uuid(),
                'company_id' => $request->company_id,
                'course_code' => $request->course_code,
                'course_name' => $request->course_name,
                'description' => $request->description,
                'department' => $request->department,
                'duration_months' => $request->duration_months,
                'course_fee' => $request->course_fee,
                'status' => 'ACTIVE',
            ]);

            // Create entity record
            Entity::create([
                'id' => (string) Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'COURSE',
                'entity_id' => $course->id,
                'description' => $request->course_name . ' (' . $request->course_code . ')',
            ]);
        });

        return redirect()->route('entities.courses.index')->with('success', 'Course created successfully');
    }

    public function webShow(Course $course)
    {
        $course->load('company');
        return view('entities.courses.show', compact('course'));
    }

    public function webEdit(Course $course)
    {
        $companies = Company::all();
        return view('entities.courses.edit', compact('course', 'companies'));
    }

    public function webUpdate(Request $request, Course $course)
    {
        $request->validate([
            'course_code' => 'required|string|unique:courses,course_code,' . $course->id,
            'course_name' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
            'department' => 'nullable|string',
            'duration_months' => 'nullable|integer|min:1',
            'course_fee' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request, $course) {
            $course->update($request->all());

            // Update entity description
            $course->entity()->update([
                'description' => $request->course_name . ' (' . $request->course_code . ')',
            ]);
        });

        return redirect()->route('entities.courses.index')->with('success', 'Course updated successfully');
    }

    public function webDestroy(Course $course)
    {
        DB::transaction(function () use ($course) {
            $course->entity()->delete();
            $course->delete();
        });

        return redirect()->route('entities.courses.index')->with('success', 'Course deleted successfully');
    }

    // API methods (if needed)
    public function index()
    {
        return Course::with('company')->get();
    }

    public function store(Request $request)
    {
        // API store method
    }

    public function show(Course $course)
    {
        return $course->load('company');
    }

    public function update(Request $request, Course $course)
    {
        // API update method
    }

    public function destroy(Course $course)
    {
        // API destroy method
    }
}
