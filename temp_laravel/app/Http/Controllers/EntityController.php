<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Student;
use App\Models\Vessel;
use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get entity counts
        $stats = [
            'employees' => Employee::count(),
            'students' => Student::count(),
            'vessels' => Vessel::count(),
            'vehicles' => Vehicle::count(),
        ];

        // Get recent entities (last 10 from each type)
        $recentEntities = [];

        // Recent employees
        $employees = Employee::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($employees as $employee) {
            $recentEntities[] = [
                'type' => 'EMPLOYEE',
                'name' => $employee->name,
                'code' => $employee->employee_code,
                'status' => $employee->status,
                'created_at' => $employee->created_at->diffForHumans(),
            ];
        }

        // Recent students
        $students = Student::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($students as $student) {
            $recentEntities[] = [
                'type' => 'STUDENT',
                'name' => $student->name,
                'code' => $student->student_id,
                'status' => $student->status,
                'created_at' => $student->created_at->diffForHumans(),
            ];
        }

        // Recent vessels
        $vessels = Vessel::orderBy('created_at', 'desc')->limit(2)->get();
        foreach ($vessels as $vessel) {
            $recentEntities[] = [
                'type' => 'SHIP',
                'name' => $vessel->vessel_name,
                'code' => $vessel->imo_number,
                'status' => $vessel->status,
                'created_at' => $vessel->created_at->diffForHumans(),
            ];
        }

        // Recent vehicles
        $vehicles = Vehicle::orderBy('created_at', 'desc')->limit(2)->get();
        foreach ($vehicles as $vehicle) {
            $recentEntities[] = [
                'type' => 'VEHICLE',
                'name' => $vehicle->make . ' ' . $vehicle->model,
                'code' => $vehicle->registration_number,
                'status' => $vehicle->status,
                'created_at' => $vehicle->created_at->diffForHumans(),
            ];
        }

        // Sort by creation date (most recent first)
        usort($recentEntities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Take only the 10 most recent
        $recentEntities = array_slice($recentEntities, 0, 10);

        return view('entities.index', compact('stats', 'recentEntities'));
    }

    /**
     * Display employees listing
     */
    public function employees(Request $request)
    {
        $query = Employee::with('company');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhereHas('company', function($companyQuery) use ($search) {
                      $companyQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name', 'employee_code', 'department', 'position', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle export
        if ($request->get('export') === 'csv') {
            $employees = $query->get();
            $filename = 'employees_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($employees) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Employee Code', 'Name', 'Department', 'Position', 
                    'Company', 'Status', 'Created At'
                ]);

                foreach ($employees as $employee) {
                    fputcsv($file, [
                        $employee->employee_code,
                        $employee->name,
                        $employee->department ?? 'N/A',
                        $employee->position ?? 'N/A',
                        $employee->company->name ?? 'N/A',
                        $employee->status,
                        $employee->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $employees = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $companies = Company::orderBy('name')->get();
        $departments = Employee::select('department')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('entities.employees.index', compact('employees', 'companies', 'departments'));
    }

    /**
     * Display students listing
     */
    public function students(Request $request)
    {
        $query = Student::with('company');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('rank', 'like', "%{$search}%")
                  ->orWhereHas('company', function($companyQuery) use ($search) {
                      $companyQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhere('course', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Course filter
        if ($request->filled('course_id')) {
            $query->where('course', 'like', '%' . \App\Models\Course::find($request->course_id)->course_name . '%');
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Age range filters
        if ($request->filled('age_min')) {
            $query->where('age', '>=', $request->age_min);
        }

        if ($request->filled('age_max')) {
            $query->where('age', '<=', $request->age_max);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name', 'student_id', 'age', 'gender', 'rank', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle export
        if ($request->get('export') === 'csv') {
            // Check if this is a bulk export with selected IDs
            if ($request->has('selected_ids')) {
                $selectedIds = explode(',', $request->get('selected_ids'));
                $students = Student::with('company')->whereIn('id', $selectedIds)->get();
            } else {
                $students = $query->get();
            }
            $filename = 'students_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($students) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Student ID', 'Name', 'Email', 'Age', 'Gender', 'Rank',
                    'Company', 'Course', 'Status', 'Created At'
                ]);

                foreach ($students as $student) {
                    fputcsv($file, [
                        $student->student_id,
                        $student->name,
                        $student->email ?? 'N/A',
                        $student->age ?? 'N/A',
                        $student->gender ?? 'N/A',
                        $student->rank ?? 'N/A',
                        $student->company->name ?? 'N/A',
                        $student->course ?? 'N/A',
                        $student->status,
                        $student->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $students = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $companies = Company::orderBy('name')->get();
        $courses = \App\Models\Course::orderBy('course_name')->get();

        return view('entities.students.index', compact('students', 'companies', 'courses'));
    }

    /**
     * Display vehicles listing
     */
    public function vehicles(Request $request)
    {
        $query = Vehicle::with('company');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%")
                  ->orWhereHas('company', function($companyQuery) use ($search) {
                      $companyQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Make filter
        if ($request->filled('make')) {
            $query->where('make', 'like', '%' . $request->make . '%');
        }

        // Year range filters
        if ($request->filled('year_min')) {
            $query->where('year', '>=', $request->year_min);
        }

        if ($request->filled('year_max')) {
            $query->where('year', '<=', $request->year_max);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['registration_number', 'make', 'model', 'year', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle export
        if ($request->get('export') === 'csv') {
            $vehicles = $query->get();
            $filename = 'vehicles_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($vehicles) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Registration Number', 'Make', 'Model', 'Year', 
                    'Company', 'Status', 'Created At'
                ]);

                foreach ($vehicles as $vehicle) {
                    fputcsv($file, [
                        $vehicle->registration_number,
                        $vehicle->make,
                        $vehicle->model,
                        $vehicle->year,
                        $vehicle->company->name ?? 'N/A',
                        $vehicle->status,
                        $vehicle->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $vehicles = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $companies = Company::orderBy('name')->get();
        $makes = Vehicle::select('make')
            ->distinct()
            ->orderBy('make')
            ->pluck('make');

        return view('entities.vehicles.index', compact('vehicles', 'companies', 'makes'));
    }

    /**
     * Display vessels listing
     */
    public function vessels(Request $request)
    {
        $query = Vessel::with('company');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vessel_name', 'like', "%{$search}%")
                  ->orWhere('imo_number', 'like', "%{$search}%")
                  ->orWhere('vessel_type', 'like', "%{$search}%")
                  ->orWhere('flag_state', 'like', "%{$search}%")
                  ->orWhereHas('company', function($companyQuery) use ($search) {
                      $companyQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Vessel type filter
        if ($request->filled('vessel_type')) {
            $query->where('vessel_type', 'like', '%' . $request->vessel_type . '%');
        }

        // Flag state filter
        if ($request->filled('flag_state')) {
            $query->where('flag_state', 'like', '%' . $request->flag_state . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['vessel_name', 'imo_number', 'vessel_type', 'flag_state', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle export
        if ($request->get('export') === 'csv') {
            $vessels = $query->get();
            $filename = 'vessels_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($vessels) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Vessel Name', 'IMO Number', 'Vessel Type', 'Flag State', 
                    'Company', 'Status', 'Created At'
                ]);

                foreach ($vessels as $vessel) {
                    fputcsv($file, [
                        $vessel->vessel_name,
                        $vessel->imo_number,
                        $vessel->vessel_type ?? 'N/A',
                        $vessel->flag_state ?? 'N/A',
                        $vessel->company->name ?? 'N/A',
                        $vessel->status,
                        $vessel->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $vessels = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $companies = Company::orderBy('name')->get();
        $vesselTypes = Vessel::select('vessel_type')
            ->whereNotNull('vessel_type')
            ->distinct()
            ->orderBy('vessel_type')
            ->pluck('vessel_type');

        return view('entities.vessels.index', compact('vessels', 'companies', 'vesselTypes'));
    }

    /**
     * Bulk actions for employees
     */
    public function employeesBulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $action = $request->action;
        $employeeIds = $request->employee_ids;
        $count = count($employeeIds);

        DB::transaction(function () use ($action, $employeeIds, $count) {
            switch ($action) {
                case 'status_active':
                    Employee::whereIn('id', $employeeIds)->update(['status' => 'ACTIVE']);
                    break;
                case 'status_inactive':
                    Employee::whereIn('id', $employeeIds)->update(['status' => 'INACTIVE']);
                    break;
                case 'delete':
                    Employee::whereIn('id', $employeeIds)->delete();
                    break;
            }
        });

        $actionText = [
            'status_active' => 'set to Active',
            'status_inactive' => 'set to Inactive',
            'delete' => 'deleted',
        ];

        $message = "Successfully {$actionText[$action]} {$count} employee(s)";
        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk actions for students
     */
    public function studentsBulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $action = $request->action;
        $studentIds = $request->student_ids;
        $count = count($studentIds);

        DB::transaction(function () use ($action, $studentIds, $count) {
            switch ($action) {
                case 'status_active':
                    Student::whereIn('id', $studentIds)->update(['status' => 'ACTIVE']);
                    break;
                case 'status_inactive':
                    Student::whereIn('id', $studentIds)->update(['status' => 'INACTIVE']);
                    break;
                case 'delete':
                    Student::whereIn('id', $studentIds)->delete();
                    break;
            }
        });

        $actionText = [
            'status_active' => 'set to Active',
            'status_inactive' => 'set to Inactive',
            'delete' => 'deleted',
        ];

        $message = "Successfully {$actionText[$action]} {$count} student(s)";
        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk actions for vehicles
     */
    public function vehiclesBulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'vehicle_ids' => 'required|array|min:1',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $action = $request->action;
        $vehicleIds = $request->vehicle_ids;
        $count = count($vehicleIds);

        DB::transaction(function () use ($action, $vehicleIds, $count) {
            switch ($action) {
                case 'status_active':
                    Vehicle::whereIn('id', $vehicleIds)->update(['status' => 'ACTIVE']);
                    break;
                case 'status_inactive':
                    Vehicle::whereIn('id', $vehicleIds)->update(['status' => 'INACTIVE']);
                    break;
                case 'delete':
                    Vehicle::whereIn('id', $vehicleIds)->delete();
                    break;
            }
        });

        $actionText = [
            'status_active' => 'set to Active',
            'status_inactive' => 'set to Inactive',
            'delete' => 'deleted',
        ];

        $message = "Successfully {$actionText[$action]} {$count} vehicle(s)";
        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk actions for vessels
     */
    public function vesselsBulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'vessel_ids' => 'required|array|min:1',
            'vessel_ids.*' => 'exists:vessels,id',
        ]);

        $action = $request->action;
        $vesselIds = $request->vessel_ids;
        $count = count($vesselIds);

        DB::transaction(function () use ($action, $vesselIds, $count) {
            switch ($action) {
                case 'status_active':
                    Vessel::whereIn('id', $vesselIds)->update(['status' => 'ACTIVE']);
                    break;
                case 'status_inactive':
                    Vessel::whereIn('id', $vesselIds)->update(['status' => 'INACTIVE']);
                    break;
                case 'delete':
                    Vessel::whereIn('id', $vesselIds)->delete();
                    break;
            }
        });

        $actionText = [
            'status_active' => 'set to Active',
            'status_inactive' => 'set to Inactive',
            'delete' => 'deleted',
        ];

        $message = "Successfully {$actionText[$action]} {$count} vessel(s)";
        return redirect()->back()->with('success', $message);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
