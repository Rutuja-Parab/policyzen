<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\Entity;
use App\Models\PolicyEndorsement;
use App\Models\Document;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = InsurancePolicy::with(['entities', 'creator']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('policy_number', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%")
                  ->orWhere('insurance_type', 'like', "%{$search}%")
                  ->orWhereHas('entities', function($entityQuery) use ($search) {
                      $entityQuery->where('description', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Insurance type filter
        if ($request->filled('insurance_type')) {
            $query->where('insurance_type', $request->insurance_type);
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        // Premium amount range
        if ($request->filled('premium_min')) {
            $query->where('premium_amount', '>=', $request->premium_min);
        }

        if ($request->filled('premium_max')) {
            $query->where('premium_amount', '<=', $request->premium_max);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['policy_number', 'provider', 'insurance_type', 'status', 'premium_amount', 'start_date', 'end_date', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle export
        if ($request->get('export') === 'csv') {
            // Check if this is a bulk export with selected IDs
            if ($request->has('selected_ids')) {
                $selectedIds = explode(',', $request->get('selected_ids'));
                $policies = InsurancePolicy::with(['entities', 'creator'])->whereIn('id', $selectedIds)->get();
            } else {
                $policies = $query->get();
            }
            $filename = 'policies_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($policies) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Policy Number', 'Insurance Type', 'Provider', 'Status', 
                    'Premium Amount', 'Sum Insured', 'Start Date', 'End Date', 
                    'Entities Covered', 'Created At'
                ]);

                foreach ($policies as $policy) {
                    fputcsv($file, [
                        $policy->policy_number,
                        $policy->insurance_type,
                        $policy->provider,
                        $policy->status,
                        $policy->premium_amount,
                        $policy->sum_insured,
                        $policy->start_date,
                        $policy->end_date,
                        $policy->entities->count(),
                        $policy->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Pagination with configurable per_page
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $policies = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $statusOptions = ['ACTIVE', 'EXPIRED', 'UNDER_REVIEW', 'CANCELLED'];
        $insuranceTypes = ['HEALTH', 'ACCIDENT', 'PROPERTY', 'VEHICLE', 'MARINE'];
        
        // Get all entities for the create form (only employees and students for group policies)
        $entities = Entity::whereIn('type', ['EMPLOYEE', 'STUDENT'])->get();

        return view('policies.index', compact('policies', 'entities', 'statusOptions', 'insuranceTypes'));
    }

    public function create()
    {
        $entities = Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'VEHICLE', 'SHIP'])->get();
        return view('policies.create', compact('entities'));
    }

    public function store(Request $request)
    {
        // Ensure no duplicate entity_ids in the request
        $request->merge([
            'entity_ids' => array_unique($request->entity_ids)
        ]);

        $request->validate([
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'exists:entities,id',
            'policy_number' => 'required|string|regex:/^[A-Z0-9\-]{5,20}$/|unique:policies',
            'insurance_type' => 'required|in:HEALTH,ACCIDENT,PROPERTY,VEHICLE,MARINE',
            'provider' => 'required|string|min:2|max:100|regex:/^[a-zA-Z0-9\s\-\.&]+$/',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date|after:today',
            'sum_insured' => 'required|numeric|min:1|max:999999999.99|regex:/^[0-9]+(?:\.[0-9]{1,2})?$/',
            'premium_amount' => 'required|numeric|min:1|max:99999999.99|regex:/^[0-9]+(?:\.[0-9]{1,2})?$/',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'nullable|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ], [
            'policy_number.regex' => 'Policy number must be 5-20 characters (A-Z, 0-9, - only)',
            'policy_number.unique' => 'This policy number already exists',
            'provider.regex' => 'Provider name must contain only letters, numbers, spaces, hyphens, ampersands, and periods',
            'start_date.before' => 'Start date must be before end date',
            'end_date.after' => 'End date must be after start date and in the future',
            'sum_insured.min' => 'Sum insured amount must be greater than 0',
            'sum_insured.max' => 'Sum insured amount cannot exceed ₹999,999,999.99',
            'sum_insured.regex' => 'Sum insured must be a valid amount with maximum 2 decimal places',
            'premium_amount.min' => 'Premium amount must be greater than 0',
            'premium_amount.max' => 'Premium amount cannot exceed ₹99,999,999.99',
            'premium_amount.regex' => 'Premium amount must be a valid amount with maximum 2 decimal places',
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
                    'entities_covered' => count($request->entity_ids),
                ],
                'performed_by' => Auth::id(),
            ]);

            // Attach entities to the policy and create endorsements
            $entityIds = array_unique($request->entity_ids); // Remove duplicates
            $effectiveDate = now()->toDateString();

            foreach ($entityIds as $entityId) {
                // Check if entity is already attached to this policy
                if ($policy->entities()->where('entities.id', $entityId)->wherePivot('status', 'ACTIVE')->exists()) {
                    continue; // Skip if already attached
                }

                // Attach entity to policy
                $policy->entities()->attach($entityId, [
                    'effective_date' => $effectiveDate,
                    'status' => 'ACTIVE'
                ]);

                // Create endorsement for adding entity
                $entity = Entity::find($entityId);
                $endorsement = PolicyEndorsement::create([
                    'policy_id' => $policy->id,
                    'endorsement_number' => 'END-' . $policy->policy_number . '-' . $entityId . '-' . time(),
                    'description' => 'Added ' . $entity->description . ' to group policy',
                    'effective_date' => $effectiveDate,
                    'created_by' => Auth::id(),
                ]);

                // Create audit log for endorsement creation
                AuditLog::create([
                    'action' => 'CREATE',
                    'entity_type' => 'PolicyEndorsement',
                    'entity_id' => $entityId,
                    'policy_id' => $policy->id,
                    'endorsement_id' => $endorsement->id,
                    'metadata' => [
                        'endorsement_number' => $endorsement->endorsement_number,
                        'action' => 'ADD_ENTITY',
                        'entity_description' => $entity->description,
                        'entity_type' => $entity->type,
                    ],
                    'performed_by' => Auth::id(),
                ]);
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('documents/policies', $fileName, 'public');

                    Document::create([
                        'documentable_type' => InsurancePolicy::class,
                        'documentable_id' => $policy->id,
                        'uploaded_by' => Auth::id(),
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getMimeType(),
                        'document_type' => $request->document_type ?? 'POLICY_DOCUMENT',
                        'uploaded_at' => now(),
                    ]);
                }
            }

            // Create notification
            NotificationService::forPolicy(
                'CREATE',
                $policy->policy_number,
                $policy->insurance_type,
                $policy->provider,
                [
                    'policy_id' => $policy->id,
                    'entity_ids' => $request->entity_ids,
                    'entity_count' => count($request->entity_ids),
                    'sum_insured' => $policy->sum_insured,
                    'premium_amount' => $policy->premium_amount,
                    'start_date' => $policy->start_date,
                    'end_date' => $policy->end_date,
                ]
            );
        });

        return redirect()->route('policies.index')->with('success', 'Group policy created successfully with ' . count($request->entity_ids) . ' covered entities');
    }

    public function show(InsurancePolicy $policy)
    {
        // Load policy with all relationships
        $policy->load([
            'creator', 
            'endorsements.creator',
            'endorsements.documents.uploader',
            'documents.uploader'
        ]);
        
        // Get all entities (both active and terminated) with pivot data
        $allEntities = $policy->entities()->withPivot(['effective_date', 'termination_date', 'status'])->get();
        
        // Separate active and terminated entities
        $activeEntities = $allEntities->where('pivot.status', 'ACTIVE');
        $terminatedEntities = $allEntities->where('pivot.status', 'TERMINATED');
        
        // Get all documents (policy documents + endorsement documents)
        $policyDocuments = $policy->documents()->with('uploader')->get();
        $endorsementDocuments = collect();
        
        foreach ($policy->endorsements as $endorsement) {
            $endorsementDocs = $endorsement->documents()->with('uploader')->get();
            $endorsementDocuments = $endorsementDocuments->merge($endorsementDocs);
        }
        
        // Combine and sort all documents by upload date
        $allDocuments = $policyDocuments->merge($endorsementDocuments)->sortByDesc('uploaded_at');
        
        return view('policies.show', compact(
            'policy', 
            'activeEntities', 
            'terminatedEntities', 
            'allDocuments',
            'policyDocuments',
            'endorsementDocuments'
        ));
    }

    public function edit(InsurancePolicy $policy)
    {
        $entities = Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'VEHICLE', 'SHIP'])->get();
        
        // Load currently associated entities with pivot data
        $policy->load(['entities' => function($query) {
            $query->withPivot(['effective_date', 'termination_date', 'status']);
        }]);
        
        // Get currently active entity IDs for the form
        $currentEntityIds = $policy->entities()
            ->wherePivot('status', 'ACTIVE')
            ->pluck('entities.id')
            ->toArray();
        
        return view('policies.edit', compact('policy', 'entities', 'currentEntityIds'));
    }

    public function update(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'exists:entities,id',
            'policy_number' => 'required|string|regex:/^[A-Z0-9\-]{5,20}$/|unique:policies,policy_number,' . $policy->id,
            'insurance_type' => 'required|in:HEALTH,ACCIDENT,PROPERTY,VEHICLE,MARINE',
            'provider' => 'required|string|min:2|max:100|regex:/^[a-zA-Z0-9\s\-\.&]+$/',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'sum_insured' => 'required|numeric|min:1|max:999999999.99|regex:/^[0-9]+(?:\.[0-9]{1,2})?$/',
            'premium_amount' => 'required|numeric|min:1|max:99999999.99|regex:/^[0-9]+(?:\.[0-9]{1,2})?$/',
            'status' => 'required|in:ACTIVE,EXPIRED,UNDER_REVIEW,CANCELLED',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'nullable|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ], [
            'entity_ids.required' => 'At least one entity must be selected for the policy',
            'policy_number.regex' => 'Policy number must be 5-20 characters (A-Z, 0-9, - only)',
            'policy_number.unique' => 'This policy number already exists',
            'provider.regex' => 'Provider name must contain only letters, numbers, spaces, hyphens, ampersands, and periods',
            'start_date.before' => 'Start date must be before end date',
            'end_date.after' => 'End date must be after start date',
            'sum_insured.min' => 'Sum insured amount must be greater than 0',
            'sum_insured.max' => 'Sum insured amount cannot exceed ₹999,999,999.99',
            'sum_insured.regex' => 'Sum insured must be a valid amount with maximum 2 decimal places',
            'premium_amount.min' => 'Premium amount must be greater than 0',
            'premium_amount.max' => 'Premium amount cannot exceed ₹99,999,999.99',
            'premium_amount.regex' => 'Premium amount must be a valid amount with maximum 2 decimal places',
        ]);

        // Store original values for audit log
        $originalValues = $policy->only(['policy_number', 'insurance_type', 'provider', 'start_date', 'end_date', 'sum_insured', 'premium_amount', 'status']);

        // Get current active entity IDs
        $currentEntityIds = $policy->entities()
            ->wherePivot('status', 'ACTIVE')
            ->pluck('entities.id')
            ->toArray();

        // Ensure no duplicate entity_ids in the request
        $request->merge([
            'entity_ids' => array_unique($request->entity_ids)
        ]);

        // Determine changes in entities
        $newEntityIds = array_unique($request->entity_ids); // Additional safety
        $entitiesToAdd = array_diff($newEntityIds, $currentEntityIds);
        $entitiesToRemove = array_diff($currentEntityIds, $newEntityIds);
        $entitiesToKeep = array_intersect($newEntityIds, $currentEntityIds);

        DB::transaction(function () use ($request, $policy, $originalValues, $entitiesToAdd, $entitiesToRemove, $entitiesToKeep, $newEntityIds) {
            // Update policy basic information
            $policy->update($request->only([
                'policy_number',
                'insurance_type',
                'provider',
                'start_date',
                'end_date',
                'sum_insured',
                'premium_amount',
                'status',
            ]));

            // Create audit log for policy update
            AuditLog::create([
                'action' => 'UPDATE',
                'entity_type' => 'InsurancePolicy',
                'entity_id' => $policy->id,
                'policy_id' => $policy->id,
                'metadata' => [
                    'policy_number' => $policy->policy_number,
                    'changes' => $request->only(['policy_number', 'insurance_type', 'provider', 'start_date', 'end_date', 'sum_insured', 'premium_amount', 'status']),
                    'original_values' => $originalValues,
                    'entities_added' => count($entitiesToAdd),
                    'entities_removed' => count($entitiesToRemove),
                    'entities_kept' => count($entitiesToKeep),
                ],
                'performed_by' => Auth::id(),
            ]);

            $effectiveDate = now()->toDateString();

            // Handle entities to add
            foreach ($entitiesToAdd as $entityId) {
                $entity = Entity::find($entityId);
                
                // Attach entity to policy
                $policy->entities()->attach($entityId, [
                    'effective_date' => $effectiveDate,
                    'status' => 'ACTIVE'
                ]);

                // Create endorsement for adding entity
                $endorsement = PolicyEndorsement::create([
                    'policy_id' => $policy->id,
                    'endorsement_number' => 'END-' . $policy->policy_number . '-ADD-' . $entityId . '-' . time(),
                    'description' => 'Added ' . $entity->description . ' to group policy during policy update',
                    'effective_date' => $effectiveDate,
                    'created_by' => Auth::id(),
                ]);

                // Create audit log for entity addition
                AuditLog::create([
                    'action' => 'ADD_ENTITY',
                    'entity_type' => $entity->type,
                    'entity_id' => $entityId,
                    'policy_id' => $policy->id,
                    'endorsement_id' => $endorsement->id,
                    'metadata' => [
                        'policy_number' => $policy->policy_number,
                        'entity_description' => $entity->description,
                        'action' => 'ADD_ENTITY_DURING_UPDATE',
                        'effective_date' => $effectiveDate,
                        'endorsement_number' => $endorsement->endorsement_number,
                    ],
                    'performed_by' => Auth::id(),
                ]);
            }

            // Handle entities to remove
            foreach ($entitiesToRemove as $entityId) {
                $entity = Entity::find($entityId);
                
                // Update the pivot table to mark as terminated
                $policy->entities()->updateExistingPivot($entityId, [
                    'termination_date' => $effectiveDate,
                    'status' => 'TERMINATED'
                ]);

                // Create endorsement for removing entity
                $endorsement = PolicyEndorsement::create([
                    'policy_id' => $policy->id,
                    'endorsement_number' => 'END-' . $policy->policy_number . '-REM-' . $entityId . '-' . time(),
                    'description' => 'Removed ' . $entity->description . ' from group policy during policy update',
                    'effective_date' => $effectiveDate,
                    'created_by' => Auth::id(),
                ]);

                // Create audit log for entity removal
                AuditLog::create([
                    'action' => 'REMOVE_ENTITY',
                    'entity_type' => $entity->type,
                    'entity_id' => $entityId,
                    'policy_id' => $policy->id,
                    'endorsement_id' => $endorsement->id,
                    'metadata' => [
                        'policy_number' => $policy->policy_number,
                        'entity_description' => $entity->description,
                        'action' => 'REMOVE_ENTITY_DURING_UPDATE',
                        'termination_date' => $effectiveDate,
                        'endorsement_number' => $endorsement->endorsement_number,
                    ],
                    'performed_by' => Auth::id(),
                ]);
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('documents/policies', $fileName, 'public');

                    Document::create([
                        'documentable_type' => InsurancePolicy::class,
                        'documentable_id' => $policy->id,
                        'uploaded_by' => Auth::id(),
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getMimeType(),
                        'document_type' => $request->document_type ?? 'POLICY_DOCUMENT',
                        'uploaded_at' => now(),
                    ]);
                }
            }

            // Create notification for policy update
            NotificationService::forPolicy(
                'UPDATE',
                $policy->policy_number,
                $policy->insurance_type,
                $policy->provider,
                [
                    'policy_id' => $policy->id,
                    'original_values' => $originalValues,
                    'changes' => $request->only(['policy_number', 'insurance_type', 'provider', 'start_date', 'end_date', 'sum_insured', 'premium_amount', 'status']),
                    'entities_added' => count($entitiesToAdd),
                    'entities_removed' => count($entitiesToRemove),
                    'entities_kept' => count($entitiesToKeep),
                ]
            );
        });

        // Prepare success message with entity change details
        $message = 'Policy updated successfully';
        $changes = [];
        if (count($entitiesToAdd) > 0) {
            $changes[] = 'added ' . count($entitiesToAdd) . ' entity(ies)';
        }
        if (count($entitiesToRemove) > 0) {
            $changes[] = 'removed ' . count($entitiesToRemove) . ' entity(ies)';
        }
        if (!empty($changes)) {
            $message .= ' and ' . implode(' and ', $changes);
        }

        return redirect()->route('policies.index')->with('success', $message);
    }

    public function destroy(InsurancePolicy $policy)
    {
        $policyNumber = $policy->policy_number;
        $insuranceType = $policy->insurance_type;
        $provider = $policy->provider;

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
                'deletion_reason' => 'Manual deletion by user',
            ],
            'performed_by' => Auth::id(),
        ]);

        $policy->delete();

        // Create notification
        NotificationService::forPolicy(
            'DELETE',
            $policyNumber,
            $insuranceType,
            $provider,
            [
                'policy_id' => $policy->id,
                'deletion_reason' => 'Manual deletion by user',
            ]
        );

        return redirect()->route('policies.index')->with('success', 'Policy deleted successfully');
    }

    public function updateStatus(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'status' => 'required|in:ACTIVE,EXPIRED,UNDER_REVIEW,CANCELLED',
        ]);

        $originalStatus = $policy->status;
        $newStatus = $request->status;
        
        $policy->update(['status' => $newStatus]);

        // Create audit log for status update
        AuditLog::create([
            'action' => 'STATUS_CHANGE',
            'entity_type' => 'InsurancePolicy',
            'entity_id' => $policy->id,
            'policy_id' => $policy->id,
            'metadata' => [
                'policy_number' => $policy->policy_number,
                'original_status' => $originalStatus,
                'new_status' => $newStatus,
                'change_reason' => 'Status update by user',
            ],
            'performed_by' => Auth::id(),
        ]);

        // Create notification
        NotificationService::forPolicy(
            'STATUS_CHANGE',
            $policy->policy_number,
            $policy->insurance_type,
            $policy->provider,
            [
                'policy_id' => $policy->id,
                'original_status' => $originalStatus,
                'new_status' => $newStatus,
                'change_reason' => 'Status update by user',
            ]
        );

        return redirect()->route('policies.index')->with('success', 'Policy status updated successfully');
    }

    public function addEntity(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'exists:entities,id',
        ]);

        // Ensure no duplicate entity_ids in the request
        $request->merge([
            'entity_ids' => array_unique($request->entity_ids)
        ]);

        $entityIds = array_unique($request->entity_ids); // Additional safety
        $addedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($policy, $entityIds, &$addedCount, &$skippedCount) {
            $effectiveDate = now()->toDateString();

            foreach ($entityIds as $entityId) {
                // Check if entity is already attached to this policy
                if ($policy->entities()->where('entities.id', $entityId)->wherePivot('status', 'ACTIVE')->exists()) {
                    $skippedCount++;
                    continue;
                }

                $entity = Entity::find($entityId);

                // Attach entity to policy
                $policy->entities()->attach($entityId, [
                    'effective_date' => $effectiveDate,
                    'status' => 'ACTIVE'
                ]);

                // Create endorsement for adding entity
                $endorsement = PolicyEndorsement::create([
                    'policy_id' => $policy->id,
                    'endorsement_number' => 'END-' . $policy->policy_number . '-ADD-' . $entityId . '-' . time(),
                    'description' => 'Added ' . $entity->description . ' to group policy',
                    'effective_date' => $effectiveDate,
                    'created_by' => Auth::id(),
                ]);

                // Create audit log for entity addition
                AuditLog::create([
                    'action' => 'ADD_ENTITY',
                    'entity_type' => $entity->type,
                    'entity_id' => $entityId,
                    'policy_id' => $policy->id,
                    'endorsement_id' => $endorsement->id,
                    'metadata' => [
                        'policy_number' => $policy->policy_number,
                        'entity_description' => $entity->description,
                        'action' => 'ADD_ENTITY_TO_POLICY',
                        'effective_date' => $effectiveDate,
                        'endorsement_number' => $endorsement->endorsement_number,
                    ],
                    'performed_by' => Auth::id(),
                ]);

                $addedCount++;
            }
        });

        $message = "Added {$addedCount} entity(ies) to policy successfully";
        if ($skippedCount > 0) {
            $message .= ". {$skippedCount} entity(ies) were already covered.";
        }

        return redirect()->back()->with('success', $message);
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
            $endorsement = PolicyEndorsement::create([
                'policy_id' => $policy->id,
                'endorsement_number' => 'END-' . $policy->policy_number . '-REM-' . $entityId . '-' . time(),
                'description' => 'Removed ' . $entity->description . ' from group policy',
                'effective_date' => $terminationDate,
                'created_by' => Auth::id(),
            ]);

            // Create audit log for entity removal
            AuditLog::create([
                'action' => 'REMOVE_ENTITY',
                'entity_type' => $entity->type,
                'entity_id' => $entityId,
                'policy_id' => $policy->id,
                'endorsement_id' => $endorsement->id,
                'metadata' => [
                    'policy_number' => $policy->policy_number,
                    'entity_description' => $entity->description,
                    'action' => 'REMOVE_ENTITY_FROM_POLICY',
                    'termination_date' => $terminationDate,
                    'endorsement_number' => $endorsement->endorsement_number,
                ],
                'performed_by' => Auth::id(),
            ]);
        });

        return redirect()->back()->with('success', 'Entity removed from policy successfully');
    }

    public function uploadDocuments(Request $request, InsurancePolicy $policy)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'required|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,INVOICE,CREDIT_NOTE,RECEIPT,OTHER',
            'invoice_number' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0|max:99999999.99',
            'tax_amount' => 'nullable|numeric|min:0|max:99999999.99',
            'total_amount' => 'nullable|numeric|min:0|max:99999999.99',
            'status' => 'nullable|in:DRAFT,SENT,PARTIALLY_PAID,PAID,OVERDUE,CANCELLED,ISSUED,APPLIED',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $uploadedCount = 0;

        foreach ($request->file('documents') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents/policies', $fileName, 'public');

            Document::create([
                'documentable_type' => InsurancePolicy::class,
                'documentable_id' => $policy->id,
                'uploaded_by' => Auth::id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'document_type' => $request->document_type,
                'invoice_number' => $request->invoice_number,
                'amount' => $request->amount,
                'tax_amount' => $request->tax_amount,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'uploaded_at' => now(),
            ]);

            $uploadedCount++;
        }

        $documentTypeText = $this->getDocumentTypeText($request->document_type);
        return redirect()->back()->with('success', "Successfully uploaded {$uploadedCount} {$documentTypeText}(s)");
    }

    /**
     * Get human-readable document type text
     */
    private function getDocumentTypeText($documentType)
    {
        $types = [
            'POLICY_DOCUMENT' => 'policy document',
            'ENDORSEMENT_DOCUMENT' => 'endorsement document',
            'FINANCIAL_DOCUMENT' => 'financial document',
            'INVOICE' => 'invoice',
            'CREDIT_NOTE' => 'credit note',
            'RECEIPT' => 'receipt',
            'OTHER' => 'document',
        ];

        return $types[$documentType] ?? 'document';
    }

    public function destroyDocument(Document $document)
    {
        // Check if the document belongs to a policy or endorsement that the user can access
        // For now, just delete it
        Storage::delete($document->file_path);
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully');
    }

    public function updateDocument(Request $request, Document $document)
    {
        $request->validate([
            'document_type' => 'required|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ]);

        $document->update(['document_type' => $request->document_type]);

        return redirect()->back()->with('success', 'Document updated successfully');
    }

    /**
     * Bulk actions for policies
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'policy_ids' => 'required|array|min:1',
            'policy_ids.*' => 'exists:policies,id',
        ]);

        $action = $request->action;
        $policyIds = $request->policy_ids;
        $count = count($policyIds);

        DB::transaction(function () use ($action, $policyIds, $count) {
            switch ($action) {
                case 'status_active':
                    InsurancePolicy::whereIn('id', $policyIds)->update(['status' => 'ACTIVE']);
                    // Create audit logs
                    foreach ($policyIds as $policyId) {
                        $policy = InsurancePolicy::find($policyId);
                        AuditLog::create([
                            'action' => 'STATUS_CHANGE',
                            'entity_type' => 'InsurancePolicy',
                            'entity_id' => $policyId,
                            'policy_id' => $policyId,
                            'metadata' => [
                                'policy_number' => $policy->policy_number,
                                'original_status' => $policy->status,
                                'new_status' => 'ACTIVE',
                                'bulk_action' => true,
                            ],
                            'performed_by' => Auth::id(),
                        ]);
                    }
                    break;

                case 'status_expired':
                    InsurancePolicy::whereIn('id', $policyIds)->update(['status' => 'EXPIRED']);
                    // Create audit logs
                    foreach ($policyIds as $policyId) {
                        $policy = InsurancePolicy::find($policyId);
                        AuditLog::create([
                            'action' => 'STATUS_CHANGE',
                            'entity_type' => 'InsurancePolicy',
                            'entity_id' => $policyId,
                            'policy_id' => $policyId,
                            'metadata' => [
                                'policy_number' => $policy->policy_number,
                                'original_status' => $policy->status,
                                'new_status' => 'EXPIRED',
                                'bulk_action' => true,
                            ],
                            'performed_by' => Auth::id(),
                        ]);
                    }
                    break;

                case 'status_review':
                    InsurancePolicy::whereIn('id', $policyIds)->update(['status' => 'UNDER_REVIEW']);
                    // Create audit logs
                    foreach ($policyIds as $policyId) {
                        $policy = InsurancePolicy::find($policyId);
                        AuditLog::create([
                            'action' => 'STATUS_CHANGE',
                            'entity_type' => 'InsurancePolicy',
                            'entity_id' => $policyId,
                            'policy_id' => $policyId,
                            'metadata' => [
                                'policy_number' => $policy->policy_number,
                                'original_status' => $policy->status,
                                'new_status' => 'UNDER_REVIEW',
                                'bulk_action' => true,
                            ],
                            'performed_by' => Auth::id(),
                        ]);
                    }
                    break;

                case 'status_cancelled':
                    InsurancePolicy::whereIn('id', $policyIds)->update(['status' => 'CANCELLED']);
                    // Create audit logs
                    foreach ($policyIds as $policyId) {
                        $policy = InsurancePolicy::find($policyId);
                        AuditLog::create([
                            'action' => 'STATUS_CHANGE',
                            'entity_type' => 'InsurancePolicy',
                            'entity_id' => $policyId,
                            'policy_id' => $policyId,
                            'metadata' => [
                                'policy_number' => $policy->policy_number,
                                'original_status' => $policy->status,
                                'new_status' => 'CANCELLED',
                                'bulk_action' => true,
                            ],
                            'performed_by' => Auth::id(),
                        ]);
                    }
                    break;

                case 'delete':
                    // Create audit logs before deletion
                    foreach ($policyIds as $policyId) {
                        $policy = InsurancePolicy::find($policyId);
                        AuditLog::create([
                            'action' => 'DELETE',
                            'entity_type' => 'InsurancePolicy',
                            'entity_id' => $policyId,
                            'policy_id' => $policyId,
                            'metadata' => [
                                'policy_number' => $policy->policy_number,
                                'insurance_type' => $policy->insurance_type,
                                'provider' => $policy->provider,
                                'status' => $policy->status,
                                'bulk_action' => true,
                                'deletion_reason' => 'Bulk deletion by user',
                            ],
                            'performed_by' => Auth::id(),
                        ]);
                    }
                    InsurancePolicy::whereIn('id', $policyIds)->delete();
                    break;
            }
        });

        $actionText = [
            'status_active' => 'set to Active',
            'status_expired' => 'set to Expired',
            'status_review' => 'set to Under Review',
            'status_cancelled' => 'set to Cancelled',
            'delete' => 'deleted',
        ];

        $message = "Successfully {$actionText[$action]} {$count} policy(ies)";
        return redirect()->back()->with('success', $message);
    }
}
