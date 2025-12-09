<?php

namespace App\Http\Controllers;

use App\Models\PolicyEndorsement;
use App\Models\InsurancePolicy;
use App\Models\Document;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PolicyEndorsementController extends Controller
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

        $endorsements = $query->orderBy('created_at', 'desc')->get();

        return response()->json($endorsements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

        // Create audit log for endorsement creation
        AuditLog::create([
            'action' => 'CREATE',
            'entity_type' => 'PolicyEndorsement',
            'entity_id' => $endorsement->id,
            'policy_id' => $request->policy_id,
            'endorsement_id' => $endorsement->id,
            'metadata' => [
                'endorsement_number' => $endorsement->endorsement_number,
                'description' => $endorsement->description,
                'effective_date' => $endorsement->effective_date,
            ],
            'performed_by' => $request->created_by,
        ]);

        return response()->json($endorsement->load(['policy', 'creator']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $endorsement = PolicyEndorsement::with(['policy', 'creator'])->findOrFail($id);
        return response()->json($endorsement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'policy_id' => 'required|uuid|exists:policies,id',
            'endorsement_number' => 'required|string|unique:endorsements,endorsement_number,' . $id,
            'description' => 'required|string',
            'effective_date' => 'required|date',
        ]);

        $endorsement = PolicyEndorsement::findOrFail($id);
        
        // Store original values for audit log
        $originalValues = $endorsement->only(['policy_id', 'endorsement_number', 'description', 'effective_date']);
        
        $endorsement->update([
            'policy_id' => $request->policy_id,
            'endorsement_number' => $request->endorsement_number,
            'description' => $request->description,
            'effective_date' => $request->effective_date,
        ]);

        // Create audit log for endorsement update
        AuditLog::create([
            'action' => 'UPDATE',
            'entity_type' => 'PolicyEndorsement',
            'entity_id' => $endorsement->id,
            'policy_id' => $request->policy_id,
            'endorsement_id' => $endorsement->id,
            'metadata' => [
                'endorsement_number' => $endorsement->endorsement_number,
                'changes' => $request->only(['policy_id', 'endorsement_number', 'description', 'effective_date']),
                'original_values' => $originalValues,
            ],
            'performed_by' => $request->created_by ?? $endorsement->created_by,
        ]);

        return response()->json($endorsement->load(['policy', 'creator']));
    }

    /**
     * Upload documents for the endorsement.
     */
    public function uploadDocuments(Request $request, string $id)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
            'uploaded_by' => 'required|uuid|exists:users,id',
            'document_type' => 'required|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ]);

        $endorsement = PolicyEndorsement::findOrFail($id);
        $uploadedDocuments = [];

        foreach ($request->file('documents') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents/endorsements', $fileName, 'public');

            $document = Document::create([
                'documentable_type' => PolicyEndorsement::class,
                'documentable_id' => $endorsement->id,
                'uploaded_by' => $request->uploaded_by,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'document_type' => $request->document_type,
                'uploaded_at' => now(),
            ]);

            $uploadedDocuments[] = $document;
        }

        return response()->json($uploadedDocuments, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $endorsement = PolicyEndorsement::findOrFail($id);
        
        // Create audit log before deletion
        AuditLog::create([
            'action' => 'DELETE',
            'entity_type' => 'PolicyEndorsement',
            'entity_id' => $endorsement->id,
            'policy_id' => $endorsement->policy_id,
            'endorsement_id' => $endorsement->id,
            'metadata' => [
                'endorsement_number' => $endorsement->endorsement_number,
                'description' => $endorsement->description,
                'effective_date' => $endorsement->effective_date,
                'deletion_reason' => 'Endorsement deletion via API',
            ],
            'performed_by' => $endorsement->created_by,
        ]);

        $endorsement->delete();

        return response()->json(['message' => 'Endorsement deleted']);
    }
}
