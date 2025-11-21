<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->get();

        return response()->json($vehicles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|unique:vehicles,registration_number',
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vehicle = Vehicle::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'registration_number' => $request->registration_number,
                'make' => $request->make,
                'model' => $request->model,
                'year' => $request->year,
                'status' => $request->status ?? 'ACTIVE',
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'VEHICLE',
                'entity_id' => $vehicle->id,
                'description' => "Vehicle: {$request->make} {$request->model}",
            ]);

            DB::commit();
            return response()->json($vehicle, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create vehicle', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        return response()->json($vehicle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|unique:vehicles,registration_number,' . $vehicle->id,
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vehicle->update([
                'company_id' => $request->company_id,
                'registration_number' => $request->registration_number,
                'make' => $request->make,
                'model' => $request->model,
                'year' => $request->year,
                'status' => $request->status ?? 'ACTIVE',
            ]);

            // Update related entity
            Entity::where('entity_id', $vehicle->id)->where('type', 'VEHICLE')->update([
                'description' => "Vehicle: {$request->make} {$request->model}",
            ]);

            DB::commit();
            return response()->json($vehicle);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update vehicle', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $vehicle->id)->where('type', 'VEHICLE')->delete();

            $vehicle->delete();

            DB::commit();
            return response()->json(['message' => 'Vehicle deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete vehicle', 'error' => $e->getMessage()], 500);
        }
    }

    // Web view methods
    public function webIndex()
    {
        $vehicles = Vehicle::orderBy('created_at', 'desc')->get();
        return view('entities.vehicles.index', compact('vehicles'));
    }

    public function webCreate()
    {
        return view('entities.vehicles.create');
    }

    public function webStore(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|unique:vehicles,registration_number',
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vehicle = Vehicle::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'registration_number' => $request->registration_number,
                'make' => $request->make,
                'model' => $request->model,
                'year' => $request->year,
                'status' => $request->status ?? 'ACTIVE',
            ]);

            // Create related entity
            Entity::create([
                'id' => Str::uuid(),
                'company_id' => $request->company_id,
                'type' => 'VEHICLE',
                'entity_id' => $vehicle->id,
                'description' => "Vehicle: {$request->make} {$request->model}",
            ]);

            DB::commit();
            return redirect()->route('entities.vehicles.index')->with('success', 'Vehicle created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create vehicle: ' . $e->getMessage()]);
        }
    }

    public function webShow(Vehicle $vehicle)
    {
        return view('entities.vehicles.show', compact('vehicle'));
    }

    public function webEdit(Vehicle $vehicle)
    {
        return view('entities.vehicles.edit', compact('vehicle'));
    }

    public function webUpdate(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'registration_number' => 'required|string|unique:vehicles,registration_number,' . $vehicle->id,
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {
            $vehicle->update([
                'company_id' => $request->company_id,
                'registration_number' => $request->registration_number,
                'make' => $request->make,
                'model' => $request->model,
                'year' => $request->year,
                'status' => $request->status ?? 'ACTIVE',
            ]);

            // Update related entity
            Entity::where('entity_id', $vehicle->id)->where('type', 'VEHICLE')->update([
                'description' => "Vehicle: {$request->make} {$request->model}",
            ]);

            DB::commit();
            return redirect()->route('entities.vehicles.index')->with('success', 'Vehicle updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update vehicle: ' . $e->getMessage()]);
        }
    }

    public function webDestroy(Vehicle $vehicle)
    {
        DB::beginTransaction();
        try {
            // Delete related entity first
            Entity::where('entity_id', $vehicle->id)->where('type', 'VEHICLE')->delete();
            $vehicle->delete();

            DB::commit();
            return redirect()->route('entities.vehicles.index')->with('success', 'Vehicle deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete vehicle: ' . $e->getMessage()]);
        }
    }
}
