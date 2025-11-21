<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $employees = $query->orderBy('created_at', 'desc')->get();

        return response()->json($employees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|uuid|exists:companies,id',
            'employee_code' => 'required|string|unique:employees,employee_code',
            'name' => 'required|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'employee_code' => $request->employee_code,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'department' => $request->department,
                'position' => $request->position,
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'EMPLOYEE',
                'entity_id' => $employee->id,
                'description' => "Employee: {$request->name}",
            ]);

            DB::commit();
            return response()->json($employee, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create employee', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = Employee::findOrFail($id);
        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'company_id' => 'required|uuid|exists:companies,id',
            'employee_code' => 'required|string|unique:employees,employee_code,' . $id,
            'name' => 'required|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($id);
            $employee->update([
                'company_id' => $request->company_id,
                'employee_code' => $request->employee_code,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'department' => $request->department,
                'position' => $request->position,
            ]);

            // Update related entity
            Entity::where('entity_id', $id)->where('type', 'EMPLOYEE')->update([
                'description' => "Employee: {$request->name}",
            ]);

            DB::commit();
            return response()->json($employee);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update employee', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $id)->where('type', 'EMPLOYEE')->delete();

            $employee = Employee::findOrFail($id);
            $employee->delete();

            DB::commit();
            return response()->json(['message' => 'Employee deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete employee', 'error' => $e->getMessage()], 500);
        }
    }

    // Web view methods
    public function webIndex()
    {
        $employees = Employee::orderBy('created_at', 'desc')->get();
        return view('entities.employees.index', compact('employees'));
    }

    public function webCreate()
    {
        $companies = Company::orderBy('name')->get();
        return view('entities.employees.create', compact('companies'));
    }

    public function webStore(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'employee_code' => 'required|string|unique:employees,employee_code',
            'name' => 'required|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'employee_code' => $request->employee_code,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'department' => $request->department,
                'position' => $request->position,
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'EMPLOYEE',
                'entity_id' => $employee->id,
                'description' => "Employee: {$request->name}",
            ]);

            DB::commit();
            return redirect()->route('entities.employees.index')->with('success', 'Employee created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    public function webShow(Employee $employee)
    {
        return view('entities.employees.show', compact('employee'));
    }

    public function webEdit(Employee $employee)
    {
        return view('entities.employees.edit', compact('employee'));
    }

    public function webUpdate(Request $request, Employee $employee)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'employee_code' => 'required|string|unique:employees,employee_code,' . $employee->id,
            'name' => 'required|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $employee->update([
                'company_id' => $request->company_id,
                'employee_code' => $request->employee_code,
                'name' => $request->name,
                'status' => $request->status ?? 'ACTIVE',
                'department' => $request->department,
                'position' => $request->position,
            ]);

            // Update related entity
            Entity::where('entity_id', $employee->id)->where('type', 'EMPLOYEE')->update([
                'description' => "Employee: {$request->name}",
            ]);

            DB::commit();
            return redirect()->route('entities.employees.index')->with('success', 'Employee updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()]);
        }
    }

    public function webDestroy(Employee $employee)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $employee->id)->where('type', 'EMPLOYEE')->delete();
            $employee->delete();

            DB::commit();
            return redirect()->route('entities.employees.index')->with('success', 'Employee deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }
}
