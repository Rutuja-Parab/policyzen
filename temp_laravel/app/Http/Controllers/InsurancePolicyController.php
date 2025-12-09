<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Document;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'nullable|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
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

        // Create audit log for policy creation
        AuditLog::create([
            'action' => 'CREATE',
            'entity_type' => 'InsurancePolicy',
            'entity_id' => $policy->id,
            'policy_id' => $policy->id,
            'metadata' => [
                'policy_number' => $policy->policy_number,
                'insurance_type' => $policy->insurance_type,
                'provider' => $policy->provider,
                'sum_insured' => $policy->sum_insured,
                'premium_amount' => $policy->premium_amount,
                'status' => $policy->status,
                'entity_id' => $request->entity_id,
            ],
            'performed_by' => $request->created_by,
        ]);

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('documents/policies', $fileName, 'public');

                Document::create([
                    'documentable_type' => InsurancePolicy::class,
                    'documentable_id' => $policy->id,
                    'uploaded_by' => $request->created_by,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'document_type' => $request->document_type ?? 'POLICY_DOCUMENT',
                    'uploaded_at' => now(),
                ]);
            }
        }

        return response()->json($policy->load('documents'), 201);
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
        
        // Store original values for audit log
        $originalValues = $policy->only(['entity_id', 'policy_number', 'insurance_type', 'provider', 'start_date', 'end_date', 'sum_insured', 'premium_amount', 'status']);
        
        $policy->update($request->all());

        // Create audit log for policy update
        AuditLog::create([
            'action' => 'UPDATE',
            'entity_type' => 'InsurancePolicy',
            'entity_id' => $policy->id,
            'policy_id' => $policy->id,
            'metadata' => [
                'policy_number' => $policy->policy_number,
                'changes' => $request->only(['entity_id', 'policy_number', 'insurance_type', 'provider', 'start_date', 'end_date', 'sum_insured', 'premium_amount', 'status']),
                'original_values' => $originalValues,
            ],
            'performed_by' => $request->created_by,
        ]);

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
        $originalStatus = $policy->status;
        $policy->update(['status' => $request->status]);

        // Create audit log for status update
        AuditLog::create([
            'action' => 'STATUS_CHANGE',
            'entity_type' => 'InsurancePolicy',
            'entity_id' => $policy->id,
            'policy_id' => $policy->id,
            'metadata' => [
                'policy_number' => $policy->policy_number,
                'original_status' => $originalStatus,
                'new_status' => $request->status,
                'change_reason' => 'Status update via API',
            ],
            'performed_by' => $request->created_by ?? $policy->created_by,
        ]);

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
     * Upload documents for the policy.
     */
    public function uploadDocuments(Request $request, string $id)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
            'uploaded_by' => 'required|uuid|exists:users,id',
            'document_type' => 'required|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ]);

        $policy = InsurancePolicy::findOrFail($id);
        $uploadedDocuments = [];

        foreach ($request->file('documents') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents/policies', $fileName, 'public');

            $document = Document::create([
                'documentable_type' => InsurancePolicy::class,
                'documentable_id' => $policy->id,
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
        DB::beginTransaction();
        try {
            $policy = InsurancePolicy::findOrFail($id);
            
            // Create audit log before deletion
            AuditLog::create([
                'action' => 'DELETE',
                'entity_type' => 'InsurancePolicy',
                'entity_id' => $policy->id,
                'policy_id' => $policy->id,
                'metadata' => [
                    'policy_number' => $policy->policy_number,
                    'insurance_type' => $policy->insurance_type,
                    'provider' => $policy->provider,
                    'status' => $policy->status,
                    'deletion_reason' => 'Policy deletion via API',
                ],
                'performed_by' => $request->created_by ?? $policy->created_by,
            ]);

            // Delete related documents and endorsements
            PolicyEndorsement::where('policy_id', $id)->delete();
            Document::where('documentable_type', InsurancePolicy::class)
                ->where('documentable_id', $id)
                ->delete();

            $policy->delete();

            DB::commit();
            return response()->json(['message' => 'Policy deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete policy', 'error' => $e->getMessage()], 500);
        }
    }
}
