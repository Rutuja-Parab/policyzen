<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Student;
use App\Models\Vessel;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Entity;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $employees = Employee::whereIn('id', $employeeIds)->get();
            
            switch ($action) {
                case 'status_active':
                    Employee::whereIn('id', $employeeIds)->update(['status' => 'ACTIVE']);
                    
                    // Create notification for bulk status change
                    NotificationService::forEntity(
                        'BULK_UPDATE',
                        'Employee',
                        'Bulk Status Update',
                        'bulk',
                        [
                            'action' => 'status_active',
                            'employee_ids' => $employeeIds,
                            'count' => $count,
                            'employees' => $employees->pluck('name', 'id')->toArray(),
                        ]
                    );
                    break;
                    
                case 'status_inactive':
                    Employee::whereIn('id', $employeeIds)->update(['status' => 'INACTIVE']);
                    
                    // Create notification for bulk status change
                    NotificationService::forEntity(
                        'BULK_UPDATE',
                        'Employee',
                        'Bulk Status Update',
                        'bulk',
                        [
                            'action' => 'status_inactive',
                            'employee_ids' => $employeeIds,
                            'count' => $count,
                            'employees' => $employees->pluck('name', 'id')->toArray(),
                        ]
                    );
                    break;
                    
                case 'delete':
                    // Delete Entity records first
                    $entityIds = $employees->pluck('entity.id')->filter()->toArray();
                    Entity::whereIn('id', $entityIds)->delete();
                    
                    Employee::whereIn('id', $employeeIds)->delete();
                    
                    // Create notification for bulk deletion
                    NotificationService::forEntity(
                        'BULK_DELETE',
                        'Employee',
                        'Bulk Deletion',
                        'bulk',
                        [
                            'employee_ids' => $employeeIds,
                            'count' => $count,
                            'employees' => $employees->pluck('name', 'id')->toArray(),
                            'entity_ids' => $entityIds,
                        ]
                    );
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
     * Show the form for creating a new employee.
     */
    public function createEmployee()
    {
        $companies = Company::orderBy('name')->get();
        return view('entities.employees.create', compact('companies'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function storeEmployee(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'employee_code' => 'required|string|unique:employees,employee_code',
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request) {
            // Create the employee
            $employee = Employee::create([
                'company_id' => $request->company_id,
                'employee_code' => $request->employee_code,
                'name' => $request->name,
                'department' => $request->department,
                'position' => $request->position,
                'status' => $request->status,
            ]);

            // Create corresponding Entity record
            $entity = Entity::create([
                'company_id' => $request->company_id,
                'type' => 'EMPLOYEE',
                'entity_id' => $employee->id,
                'description' => "Employee: {$employee->name} ({$employee->employee_code})",
            ]);

            // Create notification
            NotificationService::forEntity(
                'CREATE',
                'Employee',
                $employee->name,
                $employee->id,
                [
                    'employee_code' => $employee->employee_code,
                    'company_id' => $employee->company_id,
                    'department' => $employee->department,
                    'position' => $employee->position,
                ]
            );
        });

        return redirect()->route('entities.employees.index')->with('success', 'Employee created successfully');
    }

    /**
     * Display the specified employee.
     */
    public function showEmployee(Employee $employee)
    {
        $employee->load('company', 'entity');
        return view('entities.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function editEmployee(Employee $employee)
    {
        $companies = Company::orderBy('name')->get();
        $employee->load('company', 'entity');
        return view('entities.employees.edit', compact('employee', 'companies'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function updateEmployee(Request $request, Employee $employee)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'employee_code' => 'required|string|unique:employees,employee_code,' . $employee->id,
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request, $employee) {
            $originalValues = $employee->only(['name', 'employee_code', 'department', 'position', 'status']);
            
            // Update the employee
            $employee->update([
                'company_id' => $request->company_id,
                'employee_code' => $request->employee_code,
                'name' => $request->name,
                'department' => $request->department,
                'position' => $request->position,
                'status' => $request->status,
            ]);

            // Update corresponding Entity record
            if ($employee->entity) {
                $employee->entity->update([
                    'company_id' => $request->company_id,
                    'description' => "Employee: {$employee->name} ({$employee->employee_code})",
                ]);
            }

            // Create notification
            NotificationService::forEntity(
                'UPDATE',
                'Employee',
                $employee->name,
                $employee->id,
                [
                    'employee_code' => $employee->employee_code,
                    'company_id' => $employee->company_id,
                    'department' => $employee->department,
                    'position' => $employee->position,
                    'original_values' => $originalValues,
                    'changes' => $request->only(['name', 'employee_code', 'department', 'position', 'status']),
                ]
            );
        });

        return redirect()->route('entities.employees.index')->with('success', 'Employee updated successfully');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroyEmployee(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            $employeeName = $employee->name;
            $employeeCode = $employee->employee_code;
            $entityId = $employee->entity?->id;

            // Delete Entity record first
            if ($employee->entity) {
                $employee->entity->delete();
            }

            // Delete the employee
            $employee->delete();

            // Create notification
            NotificationService::forEntity(
                'DELETE',
                'Employee',
                $employeeName,
                $employee->id,
                [
                    'employee_code' => $employeeCode,
                    'entity_id' => $entityId,
                ]
            );
        });

        return redirect()->route('entities.employees.index')->with('success', 'Employee deleted successfully');
    }

    // Student CRUD methods
    public function createStudent()
    {
        $companies = Company::orderBy('name')->get();
        return view('entities.students.create', compact('companies'));
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|string|unique:students,student_id',
            'name' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'rank' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:1|max:150',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request) {
            // Create the student
            $student = Student::create([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'course' => $request->course,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'rank' => $request->rank,
                'age' => $request->age,
                'status' => $request->status,
            ]);

            // Create corresponding Entity record
            $entity = Entity::create([
                'company_id' => $request->company_id,
                'type' => 'STUDENT',
                'entity_id' => $student->id,
                'description' => "Student: {$student->name} ({$student->student_id})",
            ]);

            // Create notification
            NotificationService::forEntity(
                'CREATE',
                'Student',
                $student->name,
                $student->id,
                [
                    'student_id' => $student->student_id,
                    'company_id' => $student->company_id,
                    'course' => $student->course,
                    'rank' => $student->rank,
                    'age' => $student->age,
                ]
            );
        });

        return redirect()->route('entities.students.index')->with('success', 'Student created successfully');
    }

    public function showStudent(Student $student)
    {
        $student->load('company', 'entity');
        return view('entities.students.show', compact('student'));
    }

    public function editStudent(Student $student)
    {
        $companies = Company::orderBy('name')->get();
        $student->load('company', 'entity');
        return view('entities.students.edit', compact('student', 'companies'));
    }

    public function updateStudent(Request $request, Student $student)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'student_id' => 'required|string|unique:students,student_id,' . $student->id,
            'name' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'rank' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:1|max:150',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request, $student) {
            $originalValues = $student->only(['name', 'student_id', 'course', 'email', 'phone', 'rank', 'age', 'status']);
            
            // Update the student
            $student->update([
                'company_id' => $request->company_id,
                'student_id' => $request->student_id,
                'name' => $request->name,
                'course' => $request->course,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'rank' => $request->rank,
                'age' => $request->age,
                'status' => $request->status,
            ]);

            // Update corresponding Entity record
            if ($student->entity) {
                $student->entity->update([
                    'company_id' => $request->company_id,
                    'description' => "Student: {$student->name} ({$student->student_id})",
                ]);
            }

            // Create notification
            NotificationService::forEntity(
                'UPDATE',
                'Student',
                $student->name,
                $student->id,
                [
                    'student_id' => $student->student_id,
                    'company_id' => $student->company_id,
                    'course' => $student->course,
                    'rank' => $student->rank,
                    'age' => $student->age,
                    'original_values' => $originalValues,
                    'changes' => $request->only(['name', 'student_id', 'course', 'email', 'phone', 'rank', 'age', 'status']),
                ]
            );
        });

        return redirect()->route('entities.students.index')->with('success', 'Student updated successfully');
    }

    public function destroyStudent(Student $student)
    {
        DB::transaction(function () use ($student) {
            $studentName = $student->name;
            $studentId = $student->student_id;
            $entityId = $student->entity?->id;

            // Delete Entity record first
            if ($student->entity) {
                $student->entity->delete();
            }

            // Delete the student
            $student->delete();

            // Create notification
            NotificationService::forEntity(
                'DELETE',
                'Student',
                $studentName,
                $student->id,
                [
                    'student_id' => $studentId,
                    'entity_id' => $entityId,
                ]
            );
        });

        return redirect()->route('entities.students.index')->with('success', 'Student deleted successfully');
    }
    // Vehicle CRUD methods
    public function createVehicle()
    {
        $companies = Company::orderBy('name')->get();
        return view('entities.vehicles.create', compact('companies'));
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|unique:vehicles,registration_number',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request) {
            // Create the vehicle
            $vehicle = Vehicle::create([
                'company_id' => $request->company_id,
                'registration_number' => $request->registration_number,
                'make' => $request->make,
                'model' => $request->model,
                'year' => $request->year,
                'status' => $request->status,
            ]);

            // Create corresponding Entity record
            $entity = Entity::create([
                'company_id' => $request->company_id,
                'type' => 'VEHICLE',
                'entity_id' => $vehicle->id,
                'description' => "Vehicle: {$vehicle->make} {$vehicle->model} ({$vehicle->registration_number})",
            ]);

            // Create notification
            NotificationService::forEntity(
                'CREATE',
                'Vehicle',
                $vehicle->make . ' ' . $vehicle->model,
                $vehicle->id,
                [
                    'registration_number' => $vehicle->registration_number,
                    'company_id' => $vehicle->company_id,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                ]
            );
        });

        return redirect()->route('entities.vehicles.index')->with('success', 'Vehicle created successfully');
    }

    public function showVehicle(Vehicle $vehicle)
    {
        $vehicle->load('company', 'entity');
        return view('entities.vehicles.show', compact('vehicle'));
    }

    public function editVehicle(Vehicle $vehicle)
    {
        $companies = Company::orderBy('name')->get();
        $vehicle->load('company', 'entity');
        return view('entities.vehicles.edit', compact('vehicle', 'companies'));
    }

    public function updateVehicle(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|unique:vehicles,registration_number,' . $vehicle->id,
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request, $vehicle) {
            $originalValues = $vehicle->only(['registration_number', 'make', 'model', 'year', 'status']);
            
            // Update the vehicle
            $vehicle->update([
                'company_id' => $request->company_id,
                'registration_number' => $request->registration_number,
                'make' => $request->make,
                'model' => $request->model,
                'year' => $request->year,
                'status' => $request->status,
            ]);

            // Update corresponding Entity record
            if ($vehicle->entity) {
                $vehicle->entity->update([
                    'company_id' => $request->company_id,
                    'description' => "Vehicle: {$vehicle->make} {$vehicle->model} ({$vehicle->registration_number})",
                ]);
            }

            // Create notification
            NotificationService::forEntity(
                'UPDATE',
                'Vehicle',
                $vehicle->make . ' ' . $vehicle->model,
                $vehicle->id,
                [
                    'registration_number' => $vehicle->registration_number,
                    'company_id' => $vehicle->company_id,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'original_values' => $originalValues,
                    'changes' => $request->only(['registration_number', 'make', 'model', 'year', 'status']),
                ]
            );
        });

        return redirect()->route('entities.vehicles.index')->with('success', 'Vehicle updated successfully');
    }

    public function destroyVehicle(Vehicle $vehicle)
    {
        DB::transaction(function () use ($vehicle) {
            $vehicleName = $vehicle->make . ' ' . $vehicle->model;
            $registrationNumber = $vehicle->registration_number;
            $entityId = $vehicle->entity?->id;

            // Delete Entity record first
            if ($vehicle->entity) {
                $vehicle->entity->delete();
            }

            // Delete the vehicle
            $vehicle->delete();

            // Create notification
            NotificationService::forEntity(
                'DELETE',
                'Vehicle',
                $vehicleName,
                $vehicle->id,
                [
                    'registration_number' => $registrationNumber,
                    'entity_id' => $entityId,
                ]
            );
        });

        return redirect()->route('entities.vehicles.index')->with('success', 'Vehicle deleted successfully');
    }

    // Vessel CRUD methods
    public function createVessel()
    {
        $companies = Company::orderBy('name')->get();
        return view('entities.vessels.create', compact('companies'));
    }

    public function storeVessel(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'vessel_name' => 'required|string|max:255',
            'imo_number' => 'required|string|unique:vessels,imo_number',
            'vessel_type' => 'nullable|string|max:255',
            'flag_state' => 'nullable|string|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request) {
            // Create the vessel
            $vessel = Vessel::create([
                'company_id' => $request->company_id,
                'vessel_name' => $request->vessel_name,
                'imo_number' => $request->imo_number,
                'vessel_type' => $request->vessel_type,
                'flag_state' => $request->flag_state,
                'status' => $request->status,
            ]);

            // Create corresponding Entity record
            $entity = Entity::create([
                'company_id' => $request->company_id,
                'type' => 'SHIP',
                'entity_id' => $vessel->id,
                'description' => "Vessel: {$vessel->vessel_name} ({$vessel->imo_number})",
            ]);

            // Create notification
            NotificationService::forEntity(
                'CREATE',
                'Vessel',
                $vessel->vessel_name,
                $vessel->id,
                [
                    'imo_number' => $vessel->imo_number,
                    'company_id' => $vessel->company_id,
                    'vessel_type' => $vessel->vessel_type,
                    'flag_state' => $vessel->flag_state,
                ]
            );
        });

        return redirect()->route('entities.vessels.index')->with('success', 'Vessel created successfully');
    }

    public function showVessel(Vessel $vessel)
    {
        $vessel->load('company', 'entity');
        return view('entities.vessels.show', compact('vessel'));
    }

    public function editVessel(Vessel $vessel)
    {
        $companies = Company::orderBy('name')->get();
        $vessel->load('company', 'entity');
        return view('entities.vessels.edit', compact('vessel', 'companies'));
    }

    public function updateVessel(Request $request, Vessel $vessel)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'vessel_name' => 'required|string|max:255',
            'imo_number' => 'required|string|unique:vessels,imo_number,' . $vessel->id,
            'vessel_type' => 'nullable|string|max:255',
            'flag_state' => 'nullable|string|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::transaction(function () use ($request, $vessel) {
            $originalValues = $vessel->only(['vessel_name', 'imo_number', 'vessel_type', 'flag_state', 'status']);
            
            // Update the vessel
            $vessel->update([
                'company_id' => $request->company_id,
                'vessel_name' => $request->vessel_name,
                'imo_number' => $request->imo_number,
                'vessel_type' => $request->vessel_type,
                'flag_state' => $request->flag_state,
                'status' => $request->status,
            ]);

            // Update corresponding Entity record
            if ($vessel->entity) {
                $vessel->entity->update([
                    'company_id' => $request->company_id,
                    'description' => "Vessel: {$vessel->vessel_name} ({$vessel->imo_number})",
                ]);
            }

            // Create notification
            NotificationService::forEntity(
                'UPDATE',
                'Vessel',
                $vessel->vessel_name,
                $vessel->id,
                [
                    'imo_number' => $vessel->imo_number,
                    'company_id' => $vessel->company_id,
                    'vessel_type' => $vessel->vessel_type,
                    'flag_state' => $vessel->flag_state,
                    'original_values' => $originalValues,
                    'changes' => $request->only(['vessel_name', 'imo_number', 'vessel_type', 'flag_state', 'status']),
                ]
            );
        });

        return redirect()->route('entities.vessels.index')->with('success', 'Vessel updated successfully');
    }

    public function destroyVessel(Vessel $vessel)
    {
        DB::transaction(function () use ($vessel) {
            $vesselName = $vessel->vessel_name;
            $imoNumber = $vessel->imo_number;
            $entityId = $vessel->entity?->id;

            // Delete Entity record first
            if ($vessel->entity) {
                $vessel->entity->delete();
            }

            // Delete the vessel
            $vessel->delete();

            // Create notification
            NotificationService::forEntity(
                'DELETE',
                'Vessel',
                $vesselName,
                $vessel->id,
                [
                    'imo_number' => $imoNumber,
                    'entity_id' => $entityId,
                ]
            );
        });

        return redirect()->route('entities.vessels.index')->with('success', 'Vessel deleted successfully');
    }


    // Stub methods for general entity operations (not typically used)
    public function create()
    {
        return redirect()->route('entities.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('entities.index');
    }

    public function show(string $id)
    {
        return redirect()->route('entities.index');
    }

    public function edit(string $id)
    {
        return redirect()->route('entities.index');
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('entities.index');
    }

    public function destroy(string $id)
    {
        return redirect()->route('entities.index');
    }
}
