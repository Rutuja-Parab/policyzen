<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::all();
        return response()->json($companies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'parent_company_id' => 'nullable|uuid|exists:companies,id',
        ]);

        $company = Company::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'parent_company_id' => $request->parent_company_id,
        ]);

        return response()->json($company, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $company = Company::findOrFail($id);
        return response()->json($company);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'parent_company_id' => 'nullable|uuid|exists:companies,id',
        ]);

        $company = Company::findOrFail($id);
        $company->update([
            'name' => $request->name,
            'parent_company_id' => $request->parent_company_id,
        ]);

        return response()->json($company);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return response()->json(['message' => 'Company deleted']);
    }

    // Web methods for views
    public function webIndex()
    {
        $companies = Company::with(['parentCompany', 'childCompanies'])->orderBy('created_at', 'desc')->get();
        return view('companies.index', compact('companies'));
    }

    public function webCreate()
    {
        $companies = Company::all(); // For parent company selection
        return view('companies.create', compact('companies'));
    }

    public function webStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_company_id' => 'nullable|uuid|exists:companies,id',
        ]);

        DB::transaction(function () use ($request) {
            $company = Company::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'parent_company_id' => $request->parent_company_id,
            ]);

            // Create entity record for insurance coverage
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'type' => 'COMPANY',
                'entity_id' => $company->id,
                'description' => $company->name,
            ]);
        });

        return redirect()->route('companies.index')->with('success', 'Company created successfully');
    }

    public function webShow(Company $company)
    {
        $company->load(['parentCompany', 'childCompanies', 'users', 'employees', 'students', 'courses']);
        return view('companies.show', compact('company'));
    }

    public function webEdit(Company $company)
    {
        $companies = Company::where('id', '!=', $company->id)->get(); // Exclude self for parent selection
        return view('companies.edit', compact('company', 'companies'));
    }

    public function webUpdate(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_company_id' => 'nullable|uuid|exists:companies,id',
        ]);

        DB::transaction(function () use ($request, $company) {
            $company->update([
                'name' => $request->name,
                'parent_company_id' => $request->parent_company_id,
            ]);

            // Update entity description
            $company->entity()->update([
                'description' => $request->name,
            ]);
        });

        return redirect()->route('companies.index')->with('success', 'Company updated successfully');
    }

    public function webDestroy(Company $company)
    {
        // Check if company has dependencies
        if ($company->users()->count() > 0 || $company->employees()->count() > 0 ||
            $company->students()->count() > 0 || $company->courses()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete company with existing users, employees, students, or courses.');
        }

        DB::transaction(function () use ($company) {
            $company->entity()->delete();
            $company->delete();
        });

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully');
    }
}
