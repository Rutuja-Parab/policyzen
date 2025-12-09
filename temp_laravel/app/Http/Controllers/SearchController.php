<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Entity;
use App\Models\Employee;
use App\Models\Student;
use App\Models\Vessel;
use App\Models\Vehicle;
use App\Models\Course;
use App\Models\Company;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display the search page
     */
    public function index()
    {
        return view('search.index');
    }

    /**
     * Search across all entities and policies
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', $request->get('entity_type', 'all'));

        if (empty($query)) {
            return response()->json([
                'policies' => [],
                'endorsements' => [],
                'entities' => [],
            ]);
        }

        $results = [];

        // Search policies
        if ($type === 'all' || $type === 'policies') {
            $policies = InsurancePolicy::where('policy_number', 'like', "%{$query}%")
                ->orWhere('provider', 'like', "%{$query}%")
                ->orWhere('insurance_type', 'like', "%{$query}%")
                ->with(['entities', 'creator'])
                ->limit(20)
                ->get();

            $results['policies'] = $policies;
        }

        // Search endorsements
        if ($type === 'all' || $type === 'endorsements') {
            $endorsements = PolicyEndorsement::where('endorsement_number', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->with(['policy', 'creator'])
                ->limit(20)
                ->get();

            $results['endorsements'] = $endorsements;
        }

        // Search entities
        if ($type === 'all' || $type === 'entities') {
            $entities = Entity::where('description', 'like', "%{$query}%")
                ->with(['company'])
                ->limit(20)
                ->get();

            $results['entities'] = $entities;
        }

        // Search companies
        if ($type === 'all' || $type === 'companies') {
            $companies = Company::where('name', 'like', "%{$query}%")
                ->limit(20)
                ->get();

            $results['companies'] = $companies;
        }

        // Search employees
        if ($type === 'all' || $type === 'employees') {
            $employees = Employee::where('name', 'like', "%{$query}%")
                ->orWhere('employee_code', 'like', "%{$query}%")
                ->with('company')
                ->limit(20)
                ->get();

            $results['employees'] = $employees;
        }

        // Search students
        if ($type === 'all' || $type === 'students') {
            $students = Student::where('name', 'like', "%{$query}%")
                ->orWhere('student_id', 'like', "%{$query}%")
                ->with('company')
                ->limit(20)
                ->get();

            $results['students'] = $students;
        }

        // Search vessels
        if ($type === 'all' || $type === 'vessels') {
            $vessels = Vessel::where('vessel_name', 'like', "%{$query}%")
                ->orWhere('imo_number', 'like', "%{$query}%")
                ->with('company')
                ->limit(20)
                ->get();

            $results['vessels'] = $vessels;
        }

        // Search vehicles
        if ($type === 'all' || $type === 'vehicles') {
            $vehicles = Vehicle::where('make', 'like', "%{$query}%")
                ->orWhere('model', 'like', "%{$query}%")
                ->orWhere('registration_number', 'like', "%{$query}%")
                ->with('company')
                ->limit(20)
                ->get();

            $results['vehicles'] = $vehicles;
        }

        // Search courses
        if ($type === 'all' || $type === 'courses') {
            $courses = Course::where('course_name', 'like', "%{$query}%")
                ->orWhere('course_code', 'like', "%{$query}%")
                ->with('company')
                ->limit(20)
                ->get();

            $results['courses'] = $courses;
        }

        return response()->json($results);
    }
}
