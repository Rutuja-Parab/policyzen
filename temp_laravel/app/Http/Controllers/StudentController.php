<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Company;
use App\Models\Course;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $students = $query->orderBy('created_at', 'desc')->get();

        return response()->json($students);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|string|unique:students,student_id',
            'name' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'year_of_study' => 'nullable|integer|min:1|max:7',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $student = Student::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course_id ? Course::find($request->course_id)->course_name : null,
                'year_of_study' => $request->year_of_study,
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'STUDENT',
                'entity_id' => $student->id,
                'description' => "Student: {$request->name}",
            ]);

            DB::commit();
            return response()->json($student, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create student', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return response()->json($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|string|unique:students,student_id,' . $student->id,
            'name' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'year_of_study' => 'nullable|integer|min:1|max:7',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $student->update([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course_id ? Course::find($request->course_id)->course_name : null,
                'year_of_study' => $request->year_of_study,
            ]);

            // Update related entity
            Entity::where('entity_id', $student->id)->where('type', 'STUDENT')->update([
                'description' => "Student: {$request->name}",
            ]);

            DB::commit();
            return response()->json($student);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update student', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $student->id)->where('type', 'STUDENT')->delete();

            $student->delete();

            DB::commit();
            return response()->json(['message' => 'Student deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete student', 'error' => $e->getMessage()], 500);
        }
    }

    // Web view methods
    public function webIndex()
    {
        $students = Student::orderBy('created_at', 'desc')->get();
        return view('entities.students.index', compact('students'));
    }

    public function webCreate()
    {
        $companies = Company::orderBy('name')->get();
        $courses = Course::orderBy('course_name')->get();
        return view('entities.students.create', compact('companies', 'courses'));
    }

    public function webStore(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|string|unique:students,student_id',
            'name' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'year_of_study' => 'nullable|integer|min:1|max:7',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $student = Student::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course_id ? Course::find($request->course_id)->course_name : null,
                'year_of_study' => $request->year_of_study,
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'STUDENT',
                'entity_id' => $student->id,
                'description' => "Student: {$request->name}",
            ]);

            DB::commit();
            return redirect()->route('entities.students.index')->with('success', 'Student created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create student: ' . $e->getMessage()]);
        }
    }

    public function webShow(Student $student)
    {
        return view('entities.students.show', compact('student'));
    }

    public function webEdit(Student $student)
    {
        return view('entities.students.edit', compact('student'));
    }

    public function webUpdate(Request $request, Student $student)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|string|unique:students,student_id,' . $student->id,
            'name' => 'required|string',
            'course' => 'nullable|string',
            'year_of_study' => 'nullable|integer|min:1|max:7',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $student->update([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course,
                'year_of_study' => $request->year_of_study,
            ]);

            // Update related entity
            Entity::where('entity_id', $student->id)->where('type', 'STUDENT')->update([
                'description' => "Student: {$request->name}",
            ]);

            DB::commit();
            return redirect()->route('entities.students.index')->with('success', 'Student updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update student: ' . $e->getMessage()]);
        }
    }

    public function webDestroy(Student $student)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $student->id)->where('type', 'STUDENT')->delete();
            $student->delete();

            DB::commit();
            return redirect()->route('entities.students.index')->with('success', 'Student deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete student: ' . $e->getMessage()]);
        }
    }
}
