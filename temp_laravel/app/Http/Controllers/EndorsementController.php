<?php

namespace App\Http\Controllers;

use App\Models\PolicyEndorsement;
use App\Models\InsurancePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EndorsementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PolicyEndorsement::with(['policy', 'creator']);

        if ($request->has('policy_id')) {
            $query->where('policy_id', $request->policy_id);
        }

        $endorsements = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('endorsements.index', compact('endorsements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $policies = InsurancePolicy::where('status', 'ACTIVE')->get();
        return view('endorsements.create', compact('policies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'endorsement_number' => 'required|string|unique:endorsements,endorsement_number',
            'description' => 'required|string',
            'effective_date' => 'required|date',
        ]);

        PolicyEndorsement::create([
            'id' => Str::uuid(),
            'policy_id' => $request->policy_id,
            'endorsement_number' => $request->endorsement_number,
            'description' => $request->description,
            'effective_date' => $request->effective_date,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('endorsements.index')->with('success', 'Endorsement created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(PolicyEndorsement $endorsement)
    {
        $endorsement->load(['policy', 'creator']);
        return view('endorsements.show', compact('endorsement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PolicyEndorsement $endorsement)
    {
        $policies = InsurancePolicy::where('status', 'ACTIVE')->get();
        return view('endorsements.edit', compact('endorsement', 'policies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PolicyEndorsement $endorsement)
    {
        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'endorsement_number' => 'required|string|unique:endorsements,endorsement_number,' . $endorsement->id,
            'description' => 'required|string',
            'effective_date' => 'required|date',
        ]);

        $endorsement->update([
            'policy_id' => $request->policy_id,
            'endorsement_number' => $request->endorsement_number,
            'description' => $request->description,
            'effective_date' => $request->effective_date,
        ]);

        return redirect()->route('endorsements.index')->with('success', 'Endorsement updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PolicyEndorsement $endorsement)
    {
        $endorsement->delete();
        return redirect()->route('endorsements.index')->with('success', 'Endorsement deleted successfully');
    }

    // API Methods
    public function apiIndex(Request $request)
    {
        $query = PolicyEndorsement::with(['policy', 'creator']);

        if ($request->has('policy_id')) {
            $query->where('policy_id', $request->policy_id);
        }

        $endorsements = $query->orderBy('created_at', 'desc')->get();

        return response()->json($endorsements);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'policy_id' => 'required|uuid|exists:policies,id',
            'endorsement_number' => 'required|string|unique:endorsements,endorsement_number',
            'description' => 'required|string',
            'effective_date' => 'required|date',
            'created_by' => 'required|uuid|exists:users,id',
        ]);

        $endorsement = PolicyEndorsement::create([
            'id' => Str::uuid(),
            'policy_id' => $request->policy_id,
            'endorsement_number' => $request->endorsement_number,
            'description' => $request->description,
            'effective_date' => $request->effective_date,
            'created_by' => $request->created_by,
        ]);

        return response()->json($endorsement, 201);
    }

    public function apiShow(PolicyEndorsement $endorsement)
    {
        return response()->json($endorsement->load(['policy', 'creator']));
    }

    public function apiUpdate(Request $request, PolicyEndorsement $endorsement)
    {
        $request->validate([
            'policy_id' => 'required|uuid|exists:policies,id',
            'endorsement_number' => 'required|string|unique:endorsements,endorsement_number,' . $endorsement->id,
            'description' => 'required|string',
            'effective_date' => 'required|date',
        ]);

        $endorsement->update($request->only([
            'policy_id', 'endorsement_number', 'description', 'effective_date'
        ]));

        return response()->json($endorsement);
    }

    public function apiDestroy(PolicyEndorsement $endorsement)
    {
        $endorsement->delete();
        return response()->json(['message' => 'Endorsement deleted']);
    }
}