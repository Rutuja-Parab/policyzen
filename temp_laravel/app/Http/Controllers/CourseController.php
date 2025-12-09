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
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request) {
            $course = Course::create([
                'company_id' => $request->company_id,
                'course_code' => $request->course_code,
                'course_name' => $request->course_name,
                'description' => $request->description,
                'department' => $request->department,
                'duration_months' => $request->duration_months,
                'status' => $request->status ?? 'ACTIVE',
            ]);

            // Create entity record
            Entity::create([
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
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request, $course) {
            $course->update([
                'course_code' => $request->course_code,
                'course_name' => $request->course_name,
                'company_id' => $request->company_id,
                'description' => $request->description,
                'department' => $request->department,
                'duration_months' => $request->duration_months,
                'status' => $request->status,
            ]);

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

    // API methods
    public function index(Request $request)
    {
        $query = Course::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $courses = $query->orderBy('created_at', 'desc')->get();
        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_code' => 'required|string|unique:courses,course_code',
            'course_name' => 'required|string',
            'company_id' => 'required|uuid|exists:companies,id',
            'description' => 'nullable|string',
            'department' => 'nullable|string',
            'duration_months' => 'nullable|integer|min:1',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::create([
                'company_id' => $request->company_id,
                'course_code' => $request->course_code,
                'course_name' => $request->course_name,
                'description' => $request->description,
                'department' => $request->department,
                'duration_months' => $request->duration_months,
                'status' => $request->status ?? 'ACTIVE',
            ]);

            // Create entity record
            Entity::create([
                'company_id' => $request->company_id,
                'type' => 'COURSE',
                'entity_id' => $course->id,
                'description' => $request->course_name . ' (' . $request->course_code . ')',
            ]);

            DB::commit();
            return response()->json($course->load('company'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create course', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $course = Course::with('company')->findOrFail($id);
        return response()->json($course);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'course_code' => 'required|string|unique:courses,course_code,' . $id,
            'course_name' => 'required|string',
            'company_id' => 'required|uuid|exists:companies,id',
            'description' => 'nullable|string',
            'department' => 'nullable|string',
            'duration_months' => 'nullable|integer|min:1',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            $course->update([
                'course_code' => $request->course_code,
                'course_name' => $request->course_name,
                'company_id' => $request->company_id,
                'description' => $request->description,
                'department' => $request->department,
                'duration_months' => $request->duration_months,
                'status' => $request->status ?? 'ACTIVE',
            ]);

            // Update entity description
            Entity::where('entity_id', $id)->where('type', 'COURSE')->update([
                'description' => $request->course_name . ' (' . $request->course_code . ')',
            ]);

            DB::commit();
            return response()->json($course->load('company'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update course', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            
            // Delete related entity first
            Entity::where('entity_id', $id)->where('type', 'COURSE')->delete();
            $course->delete();

            DB::commit();
            return response()->json(['message' => 'Course deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete course', 'error' => $e->getMessage()], 500);
        }
    }
}
