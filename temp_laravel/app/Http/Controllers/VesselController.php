<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VesselController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vessel::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $vessels = $query->orderBy('created_at', 'desc')->get();

        return response()->json($vessels);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'vessel_name' => 'required|string',
            'imo_number' => 'required|string|unique:vessels,imo_number',
            'vessel_type' => 'nullable|string',
            'flag' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vessel = Vessel::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'vessel_name' => $request->vessel_name,
                'imo_number' => $request->imo_number,
                'status' => $request->status ?? 'ACTIVE',
                'vessel_type' => $request->vessel_type,
                'flag' => $request->flag,
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'SHIP',
                'entity_id' => $vessel->id,
                'description' => "Vessel: {$request->vessel_name}",
            ]);

            DB::commit();
            return response()->json($vessel, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create vessel', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vessel $vessel)
    {
        return response()->json($vessel);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vessel $vessel)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'vessel_name' => 'required|string',
            'imo_number' => 'required|string|unique:vessels,imo_number,' . $vessel->id,
            'vessel_type' => 'nullable|string',
            'flag' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vessel->update([
                'company_id' => $request->company_id,
                'vessel_name' => $request->vessel_name,
                'imo_number' => $request->imo_number,
                'status' => $request->status ?? 'ACTIVE',
                'vessel_type' => $request->vessel_type,
                'flag' => $request->flag,
            ]);

            // Update related entity
            Entity::where('entity_id', $vessel->id)->where('type', 'SHIP')->update([
                'description' => "Vessel: {$request->vessel_name}",
            ]);

            DB::commit();
            return response()->json($vessel);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update vessel', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vessel $vessel)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $vessel->id)->where('type', 'SHIP')->delete();

            $vessel->delete();

            DB::commit();
            return response()->json(['message' => 'Vessel deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete vessel', 'error' => $e->getMessage()], 500);
        }
    }

    // Web view methods
    public function webIndex()
    {
        $vessels = Vessel::orderBy('created_at', 'desc')->get();
        return view('entities.vessels.index', compact('vessels'));
    }

    public function webCreate()
    {
        return view('entities.vessels.create');
    }

    public function webStore(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'vessel_name' => 'required|string',
            'imo_number' => 'required|string|unique:vessels,imo_number',
            'vessel_type' => 'nullable|string',
            'flag' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vessel = Vessel::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'vessel_name' => $request->vessel_name,
                'imo_number' => $request->imo_number,
                'status' => $request->status ?? 'ACTIVE',
                'vessel_type' => $request->vessel_type,
                'flag' => $request->flag,
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'SHIP',
                'entity_id' => $vessel->id,
                'description' => "Vessel: {$request->vessel_name}",
            ]);

            DB::commit();
            return redirect()->route('entities.vessels.index')->with('success', 'Vessel created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create vessel: ' . $e->getMessage()]);
        }
    }

    public function webShow(Vessel $vessel)
    {
        return view('entities.vessels.show', compact('vessel'));
    }

    public function webEdit(Vessel $vessel)
    {
        return view('entities.vessels.edit', compact('vessel'));
    }

    public function webUpdate(Request $request, Vessel $vessel)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'vessel_name' => 'required|string',
            'imo_number' => 'required|string|unique:vessels,imo_number,' . $vessel->id,
            'vessel_type' => 'nullable|string',
            'flag' => 'nullable|string',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vessel->update([
                'company_id' => $request->company_id,
                'vessel_name' => $request->vessel_name,
                'imo_number' => $request->imo_number,
                'status' => $request->status ?? 'ACTIVE',
                'vessel_type' => $request->vessel_type,
                'flag' => $request->flag,
            ]);

            // Update related entity
            Entity::where('entity_id', $vessel->id)->where('type', 'SHIP')->update([
                'description' => "Vessel: {$request->vessel_name}",
            ]);

            DB::commit();
            return redirect()->route('entities.vessels.index')->with('success', 'Vessel updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update vessel: ' . $e->getMessage()]);
        }
    }

    public function webDestroy(Vessel $vessel)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $vessel->id)->where('type', 'SHIP')->delete();
            $vessel->delete();

            DB::commit();
            return redirect()->route('entities.vessels.index')->with('success', 'Vessel deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete vessel: ' . $e->getMessage()]);
        }
    }
}
