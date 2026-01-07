<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Company;
use App\Models\Course;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
            'student_id' => 'required|string|regex:/^[A-Z0-9]{3,20}$/|unique:students,student_id',
            'name' => 'required|string|min:2|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'course_id' => 'nullable|exists:courses,id',
            'email' => 'nullable|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone' => 'nullable|regex:/^\+91[0-9]{10}$/',
            'dob' => 'nullable|date|before:today',
            'age' => 'nullable|integer|min:1|max:100',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'rank' => 'nullable|string|min:1|max:50',
            'batch' => 'nullable|string|max:50',
            'date_of_joining' => 'nullable|date',
            'date_of_exiting' => 'nullable|date|after_or_equal:date_of_joining',
            'sum_insured' => 'nullable|numeric|min:0',
            'status' => 'in:ACTIVE,INACTIVE',
        ], [
            'student_id.regex' => 'Student ID must be 3-20 characters (A-Z, 0-9 only)',
            'student_id.unique' => 'This student ID already exists',
            'name.regex' => 'Name must contain only letters, spaces, hyphens, and dots',
            'email.email' => 'Please enter a valid email address',
            'email.regex' => 'Please enter a valid email address',
            'phone.regex' => 'Please enter phone number in +91 format (e.g., +919876543210)',
            'dob.before' => 'Date of birth must be before today',
            'age.min' => 'Age must be at least 1 year',
            'age.max' => 'Age cannot exceed 100 years',
            'date_of_exiting.after_or_equal' => 'Date of exiting must be after or equal to date of joining',
        ]);

        DB::beginTransaction();
        try {
            $student = Student::create([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'age' => $request->age,
                'gender' => $request->gender,
                'rank' => $request->rank,
                'batch' => $request->batch,
                'date_of_joining' => $request->date_of_joining,
                'date_of_exiting' => $request->date_of_exiting,
                'sum_insured' => $request->sum_insured,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course_id ? Course::find($request->course_id)->course_name : null,
            ]);

            // Create related entity
            Entity::create([
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
            'student_id' => 'required|string|regex:/^[A-Z0-9]{3,20}$/|unique:students,student_id,' . $student->id,
            'name' => 'required|string|min:2|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'course_id' => 'nullable|exists:courses,id',
            'email' => 'nullable|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone' => 'nullable|regex:/^\+91[0-9]{10}$/',
            'dob' => 'nullable|date|before:today',
            'age' => 'nullable|integer|min:1|max:100',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'rank' => 'nullable|string|min:1|max:50',
            'batch' => 'nullable|string|max:50',
            'date_of_joining' => 'nullable|date',
            'date_of_exiting' => 'nullable|date|after_or_equal:date_of_joining',
            'sum_insured' => 'nullable|numeric|min:0',
            'status' => 'in:ACTIVE,INACTIVE',
        ], [
            'student_id.regex' => 'Student ID must be 3-20 characters (A-Z, 0-9 only)',
            'student_id.unique' => 'This student ID already exists',
            'name.regex' => 'Name must contain only letters, spaces, hyphens, and dots',
            'email.email' => 'Please enter a valid email address',
            'email.regex' => 'Please enter a valid email address',
            'phone.regex' => 'Please enter phone number in +91 format (e.g., +919876543210)',
            'dob.before' => 'Date of birth must be before today',
            'age.min' => 'Age must be at least 1 year',
            'age.max' => 'Age cannot exceed 100 years',
            'date_of_exiting.after_or_equal' => 'Date of exiting must be after or equal to date of joining',
        ]);

        DB::beginTransaction();
        try {
            $student->update([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'age' => $request->age,
                'gender' => $request->gender,
                'rank' => $request->rank,
                'batch' => $request->batch,
                'date_of_joining' => $request->date_of_joining,
                'date_of_exiting' => $request->date_of_exiting,
                'sum_insured' => $request->sum_insured,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course_id ? Course::find($request->course_id)->course_name : null,
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
            'student_id' => 'required|string|regex:/^[A-Z0-9]{3,20}$/|unique:students,student_id',
            'name' => 'required|string|min:2|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'email' => 'nullable|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone' => 'nullable|regex:/^\+91[0-9]{10}$/',
            'dob' => 'nullable|date|before:today',
            'age' => 'nullable|integer|min:1|max:100',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'rank' => 'nullable|string|min:1|max:50',
            'batch' => 'nullable|string|max:50',
            'date_of_joining' => 'nullable|date',
            'date_of_exiting' => 'nullable|date|after_or_equal:date_of_joining',
            'sum_insured' => 'nullable|numeric|min:0',
            'course_id' => 'nullable|exists:courses,id',
            'status' => 'in:ACTIVE,INACTIVE',
        ], [
            'student_id.regex' => 'Student ID must be 3-20 characters (A-Z, 0-9 only)',
            'student_id.unique' => 'This student ID already exists',
            'name.regex' => 'Name must contain only letters, spaces, hyphens, and dots',
            'email.email' => 'Please enter a valid email address',
            'email.regex' => 'Please enter a valid email address',
            'phone.regex' => 'Please enter phone number in +91 format (e.g., +919876543210)',
            'dob.before' => 'Date of birth must be before today',
            'age.min' => 'Age must be at least 1 year',
            'age.max' => 'Age cannot exceed 100 years',
            'date_of_exiting.after_or_equal' => 'Date of exiting must be after or equal to date of joining',
        ]);

        DB::beginTransaction();
        try {
            $student = Student::create([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'age' => $request->age,
                'gender' => $request->gender,
                'rank' => $request->rank,
                'batch' => $request->batch,
                'date_of_joining' => $request->date_of_joining,
                'date_of_exiting' => $request->date_of_exiting,
                'sum_insured' => $request->sum_insured,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course_id ? Course::find($request->course_id)->course_name : null,
            ]);

            // Create related entity
            Entity::create([
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
        $companies = Company::orderBy('name')->get();
        $courses = Course::orderBy('course_name')->get();
        // Find the course ID if student has a course name
        $selectedCourseId = null;
        if ($student->course) {
            $course = Course::where('course_name', $student->course)->first();
            $selectedCourseId = $course ? $course->id : null;
        }
        return view('entities.students.edit', compact('student', 'companies', 'courses', 'selectedCourseId'));
    }

    public function webUpdate(Request $request, Student $student)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|string|regex:/^[A-Z0-9]{3,20}$/|unique:students,student_id,' . $student->id,
            'name' => 'required|string|min:2|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'email' => 'nullable|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone' => 'nullable|regex:/^\+91[0-9]{10}$/',
            'dob' => 'nullable|date|before:today',
            'age' => 'nullable|integer|min:1|max:100',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'rank' => 'nullable|string|min:1|max:50',
            'batch' => 'nullable|string|max:50',
            'date_of_joining' => 'nullable|date',
            'date_of_exiting' => 'nullable|date|after_or_equal:date_of_joining',
            'sum_insured' => 'nullable|numeric|min:0',
            'course_id' => 'nullable|exists:courses,id',
            'status' => 'in:ACTIVE,INACTIVE',
        ], [
            'student_id.regex' => 'Student ID must be 3-20 characters (A-Z, 0-9 only)',
            'student_id.unique' => 'This student ID already exists',
            'name.regex' => 'Name must contain only letters, spaces, hyphens, and dots',
            'email.email' => 'Please enter a valid email address',
            'email.regex' => 'Please enter a valid email address',
            'phone.regex' => 'Please enter phone number in +91 format (e.g., +919876543210)',
            'dob.before' => 'Date of birth must be before today',
            'age.min' => 'Age must be at least 1 year',
            'age.max' => 'Age cannot exceed 100 years',
            'date_of_exiting.after_or_equal' => 'Date of exiting must be after or equal to date of joining',
        ]);

        DB::beginTransaction();
        try {
            $student->update([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'age' => $request->age,
                'gender' => $request->gender,
                'rank' => $request->rank,
                'batch' => $request->batch,
                'date_of_joining' => $request->date_of_joining,
                'date_of_exiting' => $request->date_of_exiting,
                'sum_insured' => $request->sum_insured,
                'status' => $request->status ?? 'ACTIVE',
                'course' => $request->course_id ? Course::find($request->course_id)->course_name : null,
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

    /**
     * Show the CSV import form.
     */
    public function webImport()
    {
        $companies = Company::orderBy('name')->get();
        $courses = Course::orderBy('course_name')->get();
        return view('entities.students.import', compact('companies', 'courses'));
    }

    /**
     * Process CSV import.
     */
    public function webImportProcess(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'company_id' => 'required|exists:companies,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $file = $request->file('csv_file');
        $companyId = $request->company_id;
        $courseId = $request->course_id;
        $courseName = $courseId ? Course::find($courseId)->course_name : null;

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            $handle = fopen($file->getRealPath(), 'r');

            // Skip header row
            $header = fgetcsv($handle);

            // Normalize header (remove spaces, convert to lowercase)
            $header = array_map(function ($h) {
                return strtolower(trim(str_replace([' ', '.', '-'], '_', $h)));
            }, $header);

            // Map CSV columns to expected fields
            $columnMap = [
                'sr_no' => null,
                'srno' => null,
                's_no' => null,
                'student_id' => 'student_id',
                'studentid' => 'student_id',
                'name' => 'name',
                'email' => 'email',
                'phone' => 'phone',
                'dob' => 'dob',
                'date_of_birth' => 'dob',
                'age' => 'age',
                'gender' => 'gender',
                'rank' => 'rank',
                'company_name' => 'company_name',
                'company' => 'company_name',
                'course' => 'course',
                'batch' => 'batch',
                'date_of_joining' => 'date_of_joining',
                'joining_date' => 'date_of_joining',
                'date_of_exit' => 'date_of_exiting',
                'date_of_exiting' => 'date_of_exiting',
                'exit_date' => 'date_of_exiting',
                'sum_insured' => 'sum_insured',
                'suminsured' => 'sum_insured',
                'insurance_amount' => 'sum_insured',
            ];

            // Find column indices
            $columnIndices = [];
            foreach ($columnMap as $csvKey => $fieldName) {
                $index = array_search($csvKey, $header);
                if ($index !== false) {
                    $columnIndices[$fieldName ?: $csvKey] = $index;
                }
            }

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Extract data from CSV row
                    $studentId = isset($columnIndices['student_id']) ? trim($row[$columnIndices['student_id']]) : null;
                    $name = isset($columnIndices['name']) ? trim($row[$columnIndices['name']]) : null;
                    $email = isset($columnIndices['email']) ? trim($row[$columnIndices['email']]) : null;
                    $phone = isset($columnIndices['phone']) ? trim($row[$columnIndices['phone']]) : null;
                    $dob = isset($columnIndices['dob']) ? trim($row[$columnIndices['dob']]) : null;
                    $age = isset($columnIndices['age']) ? trim($row[$columnIndices['age']]) : null;
                    $gender = isset($columnIndices['gender']) ? trim($row[$columnIndices['gender']]) : null;
                    $rank = isset($columnIndices['rank']) ? trim($row[$columnIndices['rank']]) : null;
                    $csvCompanyName = isset($columnIndices['company_name']) ? trim($row[$columnIndices['company_name']]) : null;

                    // Validate required fields
                    if (empty($studentId) || empty($name)) {
                        $errorCount++;
                        $errors[] = "Row {$rowNumber}: Missing required fields (student_id or name)";
                        continue;
                    }

                    // Use company from CSV if provided, otherwise use form selection
                    $finalCompanyId = $companyId;
                    if ($csvCompanyName) {
                        $csvCompany = Company::where('name', 'like', "%{$csvCompanyName}%")->first();
                        if ($csvCompany) {
                            $finalCompanyId = $csvCompany->id;
                        }
                    }

                    // Check if student already exists
                    $existingStudent = Student::where('student_id', $studentId)
                        ->where('company_id', $finalCompanyId)
                        ->first();

                    if ($existingStudent) {
                        $errorCount++;
                        $errors[] = "Row {$rowNumber}: Student ID '{$studentId}' already exists for this company";
                        continue;
                    }

                    // Parse date of birth
                    $parsedDob = null;
                    if ($dob) {
                        try {
                            // Try different date formats
                            $parsedDob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // If parsing fails, try common formats
                            $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
                            foreach ($formats as $format) {
                                try {
                                    $parsedDob = \Carbon\Carbon::createFromFormat($format, $dob)->format('Y-m-d');
                                    break;
                                } catch (\Exception $e2) {
                                    continue;
                                }
                            }
                        }
                    }

                    // Calculate age from DOB if age not provided
                    if (empty($age) && $parsedDob) {
                        $age = \Carbon\Carbon::parse($parsedDob)->age;
                    }

                    // Create student
                    $student = Student::create([
                        'company_id' => $finalCompanyId,
                        'student_id' => $studentId,
                        'name' => $name,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                        'dob' => $parsedDob,
                        'age' => $age ? (int)$age : null,
                        'gender' => $gender ?: null,
                        'rank' => $rank ?: null,
                        'status' => 'ACTIVE',
                        'course' => $courseName,
                    ]);

                    // Create related entity
                    Entity::create([
                        'company_id' => $finalCompanyId,
                        'type' => 'STUDENT',
                        'entity_id' => $student->id,
                        'description' => "Student: {$name}",
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error("CSV Import Error Row {$rowNumber}: " . $e->getMessage());
                }
            }

            fclose($handle);
            DB::commit();

            $message = "Successfully imported {$successCount} student(s)";
            if ($errorCount > 0) {
                $message .= ". {$errorCount} row(s) had errors.";
            }

            return redirect()->route('entities.students.index')
                ->with('success', $message)
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'CSV import failed: ' . $e->getMessage()]);
        }
    }
}
