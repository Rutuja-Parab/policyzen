<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Student;
use App\Models\Vessel;
use App\Models\Vehicle;
use Illuminate\Http\Request;

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
