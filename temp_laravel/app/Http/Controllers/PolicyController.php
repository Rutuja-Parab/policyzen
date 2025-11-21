<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\Entity;
use App\Models\PolicyEndorsement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        // Get policies with their related entities (many-to-many)
        $policies = InsurancePolicy::with(['entities', 'creator'])->orderBy('created_at', 'desc')->get();

        // Get all entities for the create form (only employees and students for group policies)
        $entities = Entity::whereIn('type', ['EMPLOYEE', 'STUDENT'])->get();

        return view('policies.index', compact('policies', 'entities'));
    }

    public function create()
    {
        $entities = Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'COMPANY', 'COURSE'])->get();
        return view('policies.create', compact('entities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'exists:entities,id',
            'policy_number' => 'required|string|unique:policies',
            'insurance_type' => 'required|in:HEALTH,ACCIDENT,PROPERTY,VEHICLE,MARINE',
            'provider' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'sum_insured' => 'required|numeric|min:0',
            'premium_amount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Create the policy
            $policy = InsurancePolicy::create([
                'policy_number' => $request->policy_number,
                'insurance_type' => $request->insurance_type,
                'provider' => $request->provider,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'sum_insured' => $request->sum_insured,
                'premium_amount' => $request->premium_amount,
                'status' => 'ACTIVE',
                'created_by' => Auth::id(),
            ]);

            // Attach entities to the policy and create endorsements
            $entityIds = $request->entity_ids;
            $effectiveDate = now()->toDateString();

            foreach ($entityIds as $entityId) {
                // Attach entity to policy
                $policy->entities()->attach($entityId, [
                    'effective_date' => $effectiveDate,
                    'status' => 'ACTIVE'
                ]);

                // Create endorsement for adding entity
                $entity = Entity::find($entityId);
                PolicyEndorsement::create([
                    'policy_id' => $policy->id,
                    'endorsement_number' => 'END-' . $policy->policy_number . '-' . $entityId . '-' . time(),
                    'description' => 'Added ' . $entity->description . ' to group policy',
                    'effective_date' => $effectiveDate,
                    'created_by' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('policies.index')->with('success', 'Group policy created successfully with ' . count($request->entity_ids) . ' covered entities');
    }

    public function show(InsurancePolicy $policy)
    {
        $policy->load(['entities', 'creator', 'endorsements']);
        return view('policies.show', compact('policy'));
    }

    public function edit(InsurancePolicy $policy)
    {
        $entities = Entity::all();
        return view('policies.edit', compact('policy', 'entities'));
    }

    public function update(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'policy_number' => 'required|string|unique:policies,policy_number,' . $policy->id,
            'insurance_type' => 'required|in:HEALTH,ACCIDENT,PROPERTY,VEHICLE,MARINE',
            'provider' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'sum_insured' => 'required|numeric|min:0',
            'premium_amount' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,EXPIRED,UNDER_REVIEW,CANCELLED',
        ]);

        $policy->update($request->all());

        return redirect()->route('policies.index')->with('success', 'Policy updated successfully');
    }

    public function destroy(InsurancePolicy $policy)
    {
        $policy->delete();
        return redirect()->route('policies.index')->with('success', 'Policy deleted successfully');
    }

    public function updateStatus(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'status' => 'required|in:ACTIVE,EXPIRED,UNDER_REVIEW,CANCELLED',
        ]);

        $policy->update(['status' => $request->status]);

        return redirect()->route('policies.index')->with('success', 'Policy status updated successfully');
    }

    public function addEntity(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
        ]);

        $entityId = $request->entity_id;
        $entity = Entity::find($entityId);

        // Check if entity is already attached to this policy
        if ($policy->entities()->where('entity_id', $entityId)->wherePivot('status', 'ACTIVE')->exists()) {
            return redirect()->back()->with('error', 'Entity is already covered by this policy');
        }

        DB::transaction(function () use ($policy, $entityId, $entity) {
            $effectiveDate = now()->toDateString();

            // Attach entity to policy
            $policy->entities()->attach($entityId, [
                'effective_date' => $effectiveDate,
                'status' => 'ACTIVE'
            ]);

            // Create endorsement for adding entity
            PolicyEndorsement::create([
                'policy_id' => $policy->id,
                'endorsement_number' => 'END-' . $policy->policy_number . '-ADD-' . $entityId . '-' . time(),
                'description' => 'Added ' . $entity->description . ' to group policy',
                'effective_date' => $effectiveDate,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->back()->with('success', 'Entity added to policy successfully');
    }

    public function removeEntity(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
        ]);

        $entityId = $request->entity_id;
        $entity = Entity::find($entityId);

        DB::transaction(function () use ($policy, $entityId, $entity) {
            $terminationDate = now()->toDateString();

            // Update the pivot table to mark as terminated
            $policy->entities()->updateExistingPivot($entityId, [
                'termination_date' => $terminationDate,
                'status' => 'TERMINATED'
            ]);

            // Create endorsement for removing entity
            PolicyEndorsement::create([
                'policy_id' => $policy->id,
                'endorsement_number' => 'END-' . $policy->policy_number . '-REM-' . $entityId . '-' . time(),
                'description' => 'Removed ' . $entity->description . ' from group policy',
                'effective_date' => $terminationDate,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->back()->with('success', 'Entity removed from policy successfully');
    }
}
