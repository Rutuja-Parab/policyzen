<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsurancePolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InsurancePolicy::query();

        if ($request->has('company_id')) {
            $query->whereHas('entity', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('insurance_type')) {
            $query->where('insurance_type', $request->insurance_type);
        }

        $policies = $query->get();

        return response()->json($policies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'entity_id' => 'required|uuid|exists:entities,id',
            'policy_number' => 'required|string|unique:insurance_policies,policy_number',
            'insurance_type' => 'required|in:HEALTH,ACCIDENT,PROPERTY,VEHICLE,MARINE',
            'provider' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'sum_insured' => 'required|numeric|min:0',
            'premium_amount' => 'required|numeric|min:0',
            'created_by' => 'required|uuid|exists:users,id',
            'status' => 'in:ACTIVE,EXPIRED,UNDER_REVIEW,CANCELLED',
        ]);

        $policy = InsurancePolicy::create([
            'id' => Str::uuid(),
            'entity_id' => $request->entity_id,
            'policy_number' => $request->policy_number,
            'insurance_type' => $request->insurance_type,
            'provider' => $request->provider,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'sum_insured' => $request->sum_insured,
            'premium_amount' => $request->premium_amount,
            'status' => $request->status ?? 'ACTIVE',
            'created_by' => $request->created_by,
        ]);

        return response()->json($policy, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $policy = InsurancePolicy::findOrFail($id);
        return response()->json($policy);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'entity_id' => 'required|uuid|exists:entities,id',
            'policy_number' => 'required|string|unique:insurance_policies,policy_number,' . $id,
            'insurance_type' => 'required|in:HEALTH,ACCIDENT,PROPERTY,VEHICLE,MARINE',
            'provider' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'sum_insured' => 'required|numeric|min:0',
            'premium_amount' => 'required|numeric|min:0',
            'created_by' => 'required|uuid|exists:users,id',
            'status' => 'in:ACTIVE,EXPIRED,UNDER_REVIEW,CANCELLED',
        ]);

        $policy = InsurancePolicy::findOrFail($id);
        $policy->update($request->all());

        return response()->json($policy);
    }

    /**
     * Update policy status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:ACTIVE,EXPIRED,UNDER_REVIEW,CANCELLED',
        ]);

        $policy = InsurancePolicy::findOrFail($id);
        $policy->update(['status' => $request->status]);

        return response()->json(['message' => 'Policy status updated']);
    }

    /**
     * Get expiring policies.
     */
    public function expiring(Request $request)
    {
        $days = $request->get('days', 30);
        $until = now()->addDays($days);

        $policies = InsurancePolicy::where('status', 'ACTIVE')
            ->where('end_date', '>=', now()->toDateString())
            ->where('end_date', '<=', $until->toDateString())
            ->orderBy('end_date', 'asc')
            ->get();

        return response()->json($policies);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            // Delete related documents and endorsements
            PolicyEndorsement::where('policy_id', $id)->delete();
            Document::where('policy_id', $id)->delete();

            $policy = InsurancePolicy::findOrFail($id);
            $policy->delete();

            DB::commit();
            return response()->json(['message' => 'Policy deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete policy', 'error' => $e->getMessage()], 500);
        }
    }
}
