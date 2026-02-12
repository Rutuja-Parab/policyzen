<?php

namespace App\Http\Controllers;

use App\Models\PolicyEndorsement;
use App\Models\InsurancePolicy;
use App\Models\Entity;
use App\Models\Document;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\StudentPolicyPremium;
use App\Services\NotificationService;
use App\Services\StudentPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EndorsementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PolicyEndorsement::with(['policy', 'creator']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('endorsement_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('policy', function ($policyQuery) use ($search) {
                        $policyQuery->where('policy_number', 'like', "%{$search}%")
                            ->orWhere('provider', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($creatorQuery) use ($search) {
                        $creatorQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Policy filter
        if ($request->filled('policy_id')) {
            $query->where('policy_id', $request->policy_id);
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('effective_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('effective_date', '<=', $request->date_to);
        }

        // Description keyword filter
        if ($request->filled('description_keyword')) {
            $query->where('description', 'like', '%' . $request->description_keyword . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['endorsement_number', 'effective_date', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle export
        if ($request->get('export') === 'csv') {
            $endorsements = $query->get();
            $filename = 'endorsements_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($endorsements) {
                $file = fopen('php://output', 'w');

                // CSV headers
                fputcsv($file, [
                    'Endorsement Number',
                    'Policy Number',
                    'Description',
                    'Effective Date',
                    'Created By',
                    'Created At'
                ]);

                foreach ($endorsements as $endorsement) {
                    fputcsv($file, [
                        $endorsement->endorsement_number,
                        $endorsement->policy->policy_number ?? 'N/A',
                        $endorsement->description,
                        $endorsement->effective_date,
                        $endorsement->creator->name ?? 'Unknown',
                        $endorsement->created_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Pagination with configurable per_page
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        $endorsements = $query->paginate($perPage)->appends($request->query());

        // Get filter options
        $policies = InsurancePolicy::select('id', 'policy_number')->orderBy('policy_number')->get();

        return view('endorsements.index', compact('endorsements', 'policies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $policies = InsurancePolicy::where('status', 'ACTIVE')->get();
        $entities = \App\Models\Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'VEHICLE', 'SHIP'])->get();
        return view('endorsements.create', compact('policies', 'entities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Ensure no duplicate entity_ids in the request
        if ($request->has('entity_ids')) {
            $request->merge([
                'entity_ids' => array_unique($request->entity_ids)
            ]);
        }

        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'endorsement_number' => 'required|string|unique:endorsements,endorsement_number',
            'description' => 'required|string',
            'effective_date' => 'required|date',
            'entity_ids' => 'nullable|array',
            'entity_ids.*' => 'exists:entities,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'nullable|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ]);

        DB::transaction(function () use ($request) {
            // Get current policy entities for comparison
            $policy = InsurancePolicy::with(['entities' => function ($query) {
                $query->wherePivot('status', 'ACTIVE');
            }])->find($request->policy_id);

            $currentPolicyEntityIds = $policy->entities->pluck('id')->toArray();
            $selectedEntityIds = $request->has('entity_ids') ? array_unique($request->entity_ids) : [];

            // Determine entities to add vs terminate
            $entitiesToAdd = array_diff($selectedEntityIds, $currentPolicyEntityIds);
            $entitiesToTerminate = array_diff($currentPolicyEntityIds, $selectedEntityIds);

            $endorsement = PolicyEndorsement::create([
                'id' => Str::uuid(),
                'policy_id' => $request->policy_id,
                'endorsement_number' => $request->endorsement_number,
                'description' => $this->buildEndorsementDescription($request->description, $entitiesToAdd, $entitiesToTerminate, $policy),
                'effective_date' => $request->effective_date,
                'created_by' => Auth::id(),
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
                    'entities_to_add' => $entitiesToAdd,
                    'entities_to_terminate' => $entitiesToTerminate,
                    'action' => 'CREATE_ENDORSEMENT',
                ],
                'performed_by' => Auth::id(),
            ]);

            // Attach entities being added to endorsement and calculate premiums
            if (count($entitiesToAdd) > 0) {
                $endorsement->entities()->attach($entitiesToAdd);

                // Add new entities to policy
                $policy->entities()->attach($entitiesToAdd, [
                    'effective_date' => $request->effective_date,
                    'status' => 'ACTIVE'
                ]);

                // Calculate premiums for STUDENT entities being added
                $studentPolicyService = app(StudentPolicyService::class);
                $totalPremium = 0;

                foreach ($entitiesToAdd as $entityId) {
                    $entity = Entity::find($entityId);

                    // Create audit log for entity addition
                    AuditLog::create([
                        'action' => 'ADD_ENTITY',
                        'entity_type' => $entity->type,
                        'entity_id' => $entityId,
                        'policy_id' => $request->policy_id,
                        'endorsement_id' => $endorsement->id,
                        'metadata' => [
                            'policy_number' => $policy->policy_number,
                            'entity_description' => $entity->description,
                            'action' => 'ADD_ENTITY_VIA_ENDORSEMENT',
                            'effective_date' => $request->effective_date,
                            'endorsement_number' => $endorsement->endorsement_number,
                        ],
                        'performed_by' => Auth::id(),
                    ]);

                    // Calculate premium for STUDENT entities
                    if ($entity && $entity->type === 'STUDENT') {
                        $student = Student::find($entity->entity_id);
                        if ($student) {
                            $sumAssured = $student->sum_insured ?? 1000000; // Default ₹10,00,000
                            $dateOfJoining = $request->effective_date;
                            $dateOfExit = \Carbon\Carbon::parse($dateOfJoining)->addYear()->format('Y-m-d');

                            $premiumData = $studentPolicyService->calculateStudentPremium(
                                $sumAssured,
                                $dateOfJoining,
                                $dateOfExit
                            );

                            $finalPremium = $premiumData['final_premium'];
                            $totalPremium += $finalPremium;

                            // Save premium record
                            StudentPolicyPremium::create([
                                'student_id' => $student->id,
                                'policy_id' => $policy->id,
                                'endorsement_id' => $endorsement->id,
                                'sum_insured' => $premiumData['sum_insured'],
                                'rate' => $premiumData['rate'],
                                'annual_premium' => $premiumData['annual_premium'],
                                'date_of_joining' => $premiumData['date_of_joining'],
                                'date_of_exit' => $premiumData['date_of_exit'],
                                'pro_rata_days' => $premiumData['pro_rata_days'],
                                'prorata_premium' => $premiumData['prorata_premium'],
                                'gst_rate' => $premiumData['gst_rate'],
                                'gst_amount' => $premiumData['gst_amount'],
                                'final_premium' => $finalPremium,
                                'premium_type' => 'ADDITION',
                            ]);

                            // Audit log for premium calculation
                            AuditLog::create([
                                'action' => 'CALCULATE_PREMIUM',
                                'entity_type' => 'Student',
                                'entity_id' => $student->id,
                                'policy_id' => $policy->id,
                                'endorsement_id' => $endorsement->id,
                                'amount' => $finalPremium,
                                'transaction_type' => 'DEBIT',
                                'metadata' => [
                                    'student_name' => $student->name,
                                    'endorsement_number' => $endorsement->endorsement_number,
                                    'premium_breakdown' => $premiumData,
                                ],
                                'performed_by' => Auth::id(),
                            ]);
                        }
                    }
                }

                // Update policy coverage pool if premiums calculated
                if ($totalPremium > 0) {
                    $policy->available_coverage_pool = $policy->available_coverage_pool - $totalPremium;
                    $policy->utilized_coverage_pool = $policy->utilized_coverage_pool + $totalPremium;
                    $policy->save();
                }
            }

            // Handle entity terminations and calculate refunds
            if (count($entitiesToTerminate) > 0) {
                $studentPolicyService = app(StudentPolicyService::class);
                $totalRefund = 0;

                foreach ($entitiesToTerminate as $entityId) {
                    $entity = Entity::find($entityId);

                    // Update the pivot table to mark as terminated
                    $policy->entities()->updateExistingPivot($entityId, [
                        'termination_date' => $request->effective_date,
                        'status' => 'TERMINATED'
                    ]);

                    // Calculate refund for STUDENT entities being removed
                    if ($entity && $entity->type === 'STUDENT') {
                        $student = Student::find($entity->entity_id);
                        if ($student) {
                            $sumAssured = $student->sum_insured ?? 1000000;

                            // Get the actual date when the student was attached to the policy
                            $pivotData = DB::table('policy_entities')
                                ->where('policy_id', $policy->id)
                                ->where('entity_id', $entityId)
                                ->first();

                            // Use the pivot effective_date (when actually attached to policy)
                            $dateOfJoining = $pivotData->effective_date ?? $request->effective_date;
                            $dateOfExit = $request->effective_date;

                            $refundData = $studentPolicyService->calculateStudentPremium(
                                $sumAssured,
                                $dateOfJoining,
                                $dateOfExit
                            );

                            $finalRefund = $refundData['final_premium'];
                            $totalRefund += $finalRefund;

                            // Save premium record with REMOVAL type
                            StudentPolicyPremium::create([
                                'student_id' => $student->id,
                                'policy_id' => $policy->id,
                                'endorsement_id' => $endorsement->id,
                                'sum_insured' => $refundData['sum_insured'],
                                'rate' => $refundData['rate'],
                                'annual_premium' => $refundData['annual_premium'],
                                'date_of_joining' => $refundData['date_of_joining'],
                                'date_of_exit' => $refundData['date_of_exit'],
                                'pro_rata_days' => $refundData['pro_rata_days'],
                                'prorata_premium' => $refundData['prorata_premium'],
                                'gst_rate' => $refundData['gst_rate'],
                                'gst_amount' => $refundData['gst_amount'],
                                'final_premium' => $finalRefund,
                                'premium_type' => 'REMOVAL',
                            ]);

                            // Audit log for refund calculation
                            AuditLog::create([
                                'action' => 'CALCULATE_REFUND',
                                'entity_type' => 'Student',
                                'entity_id' => $student->id,
                                'policy_id' => $policy->id,
                                'endorsement_id' => $endorsement->id,
                                'amount' => $finalRefund,
                                'transaction_type' => 'CREDIT',
                                'metadata' => [
                                    'student_name' => $student->name,
                                    'endorsement_number' => $endorsement->endorsement_number,
                                    'refund_breakdown' => $refundData,
                                ],
                                'performed_by' => Auth::id(),
                            ]);
                        }
                    }

                    // Create audit log for entity termination
                    AuditLog::create([
                        'action' => 'TERMINATE_ENTITY',
                        'entity_type' => $entity->type,
                        'entity_id' => $entityId,
                        'policy_id' => $request->policy_id,
                        'endorsement_id' => $endorsement->id,
                        'metadata' => [
                            'policy_number' => $policy->policy_number,
                            'entity_description' => $entity->description,
                            'action' => 'TERMINATE_ENTITY_VIA_ENDORSEMENT',
                            'termination_date' => $request->effective_date,
                            'endorsement_number' => $endorsement->endorsement_number,
                        ],
                        'performed_by' => Auth::id(),
                    ]);
                }

                // Update policy coverage pool if refunds calculated
                if ($totalRefund > 0) {
                    $policy->available_coverage_pool = $policy->available_coverage_pool + $totalRefund;
                    $policy->utilized_coverage_pool = $policy->utilized_coverage_pool - $totalRefund;
                    $policy->save();
                }
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('documents/endorsements', $fileName, 'public');

                    Document::create([
                        'documentable_type' => PolicyEndorsement::class,
                        'documentable_id' => $endorsement->id,
                        'uploaded_by' => Auth::id(),
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getMimeType(),
                        'document_type' => $request->document_type ?? 'ENDORSEMENT_DOCUMENT',
                        'uploaded_at' => now(),
                    ]);
                }
            }

            // Calculate counts for success message
            $policy = InsurancePolicy::with(['entities' => function ($query) {
                $query->wherePivot('status', 'ACTIVE');
            }])->find($request->policy_id);

            $currentPolicyEntityIds = $policy->entities->pluck('id')->toArray();
            $selectedEntityIds = $request->has('entity_ids') ? array_unique($request->entity_ids) : [];
            $entitiesToAdd = array_diff($selectedEntityIds, $currentPolicyEntityIds);
            $entitiesToTerminate = array_diff($currentPolicyEntityIds, $selectedEntityIds);

            $addCount = count($entitiesToAdd);
            $terminateCount = count($entitiesToTerminate);

            // Create notification
            NotificationService::forEndorsement(
                'CREATE',
                $endorsement->endorsement_number,
                $policy->policy_number,
                $endorsement->description,
                [
                    'endorsement_id' => $endorsement->id,
                    'policy_id' => $request->policy_id,
                    'entities_added' => $addCount,
                    'entities_terminated' => $terminateCount,
                    'entity_ids' => $selectedEntityIds,
                    'effective_date' => $request->effective_date,
                ]
            );
        });

        // Calculate counts for success message
        $policy = InsurancePolicy::with(['entities' => function ($query) {
            $query->wherePivot('status', 'ACTIVE');
        }])->find($request->policy_id);

        $currentPolicyEntityIds = $policy->entities->pluck('id')->toArray();
        $selectedEntityIds = $request->has('entity_ids') ? array_unique($request->entity_ids) : [];
        $entitiesToAdd = array_diff($selectedEntityIds, $currentPolicyEntityIds);
        $entitiesToTerminate = array_diff($currentPolicyEntityIds, $selectedEntityIds);

        $addCount = count($entitiesToAdd);
        $terminateCount = count($entitiesToTerminate);
        $message = 'Endorsement created successfully';

        if ($addCount > 0 || $terminateCount > 0) {
            $details = [];
            if ($addCount > 0) $details[] = "added {$addCount} entity(ies)";
            if ($terminateCount > 0) $details[] = "terminated {$terminateCount} entity(ies)";
            $message .= ' with ' . implode(' and ', $details);
        }

        return redirect()->route('endorsements.index')->with('success', $message);
    }

    /**
     * Build enhanced endorsement description based on entity changes
     */
    private function buildEndorsementDescription($baseDescription, $entitiesToAdd, $entitiesToTerminate, $policy)
    {
        $description = $baseDescription;

        if (count($entitiesToAdd) > 0 || count($entitiesToTerminate) > 0) {
            $description .= "\n\nEntity Changes:";

            if (count($entitiesToAdd) > 0) {
                $description .= "\n+ Added: " . count($entitiesToAdd) . " entity(ies)";
            }

            if (count($entitiesToTerminate) > 0) {
                $description .= "\n- Terminated: " . count($entitiesToTerminate) . " entity(ies)";
            }

            $description .= "\nPolicy: {$policy->policy_number}";
        }

        return $description;
    }

    /**
     * Display the specified resource.
     */
    public function show(PolicyEndorsement $endorsement)
    {
        $endorsement->load(['policy', 'creator', 'entities', 'documents']);
        $allEntities = \App\Models\Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'VEHICLE', 'SHIP'])->get();
        return view('endorsements.show', compact('endorsement', 'allEntities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PolicyEndorsement $endorsement)
    {
        $policies = InsurancePolicy::where('status', 'ACTIVE')->get();
        $entities = \App\Models\Entity::whereIn('type', ['EMPLOYEE', 'STUDENT', 'VEHICLE', 'SHIP'])->get();
        $endorsement->load('entities');
        return view('endorsements.edit', compact('endorsement', 'policies', 'entities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PolicyEndorsement $endorsement)
    {
        // Ensure no duplicate entity_ids in the request
        if ($request->has('entity_ids')) {
            $request->merge([
                'entity_ids' => array_unique($request->entity_ids)
            ]);
        }

        $request->validate([
            'policy_id' => 'required|exists:policies,id',
            'endorsement_number' => 'required|string|unique:endorsements,endorsement_number,' . $endorsement->id,
            'description' => 'required|string',
            'effective_date' => 'required|date',
            'entity_ids' => 'nullable|array',
            'entity_ids.*' => 'exists:entities,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'nullable|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ]);

        DB::transaction(function () use ($request, $endorsement) {
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
                    'action' => 'UPDATE_ENDORSEMENT',
                ],
                'performed_by' => Auth::id(),
            ]);

            // Sync entities
            if ($request->has('entity_ids')) {
                $endorsement->entities()->sync(array_unique($request->entity_ids));
            } else {
                $endorsement->entities()->detach();
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('documents/endorsements', $fileName, 'public');

                    Document::create([
                        'documentable_type' => PolicyEndorsement::class,
                        'documentable_id' => $endorsement->id,
                        'uploaded_by' => Auth::id(),
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getMimeType(),
                        'document_type' => $request->document_type ?? 'ENDORSEMENT_DOCUMENT',
                        'uploaded_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('endorsements.index')->with('success', 'Endorsement updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PolicyEndorsement $endorsement)
    {
        $endorsementNumber = $endorsement->endorsement_number;
        $policyNumber = $endorsement->policy->policy_number ?? 'Unknown';
        $description = $endorsement->description;

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
                'deletion_reason' => 'Manual deletion by user',
            ],
            'performed_by' => Auth::id(),
        ]);

        $endorsement->delete();

        // Create notification
        NotificationService::forEndorsement(
            'DELETE',
            $endorsementNumber,
            $policyNumber,
            $description,
            [
                'endorsement_id' => $endorsement->id,
                'policy_id' => $endorsement->policy_id,
                'deletion_reason' => 'Manual deletion by user',
            ]
        );

        return redirect()->route('endorsements.index')->with('success', 'Endorsement deleted successfully');
    }

    /**
     * Add entities to endorsement.
     */
    public function addEntities(Request $request, PolicyEndorsement $endorsement)
    {
        $request->validate([
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'exists:entities,id',
        ]);

        $entityIds = array_unique($request->entity_ids);
        $existingIds = $endorsement->entities()->pluck('entities.id')->toArray();
        $newIds = array_diff($entityIds, $existingIds);

        if (count($newIds) > 0) {
            $endorsement->entities()->attach($newIds);
            $message = "Added " . count($newIds) . " entity(ies) to endorsement successfully";
            if (count($entityIds) > count($newIds)) {
                $message .= ". " . (count($entityIds) - count($newIds)) . " entity(ies) were already associated.";
            }
        } else {
            $message = "All selected entities are already associated with this endorsement.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove entity from endorsement.
     */
    public function removeEntity(Request $request, PolicyEndorsement $endorsement)
    {
        $request->validate([
            'entity_id' => 'required_without:entity_ids|exists:entities,id',
            'entity_ids' => 'nullable|array',
            'entity_ids.*' => 'exists:entities,id',
        ]);

        if ($request->has('entity_ids') && is_array($request->entity_ids) && count($request->entity_ids) > 0) {
            // Bulk remove
            $entityIds = array_unique($request->entity_ids);
            $endorsement->entities()->detach($entityIds);
            $count = count($entityIds);
            return redirect()->back()->with('success', "Successfully removed {$count} entity(ies) from endorsement");
        } else {
            // Single remove
            $endorsement->entities()->detach($request->entity_id);
            return redirect()->back()->with('success', 'Entity removed from endorsement successfully');
        }
    }

    public function uploadDocuments(Request $request, PolicyEndorsement $endorsement)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'required|in:POLICY_DOCUMENT,ENDORSEMENT_DOCUMENT,FINANCIAL_DOCUMENT,OTHER',
        ]);

        $uploadedCount = 0;

        foreach ($request->file('documents') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents/endorsements', $fileName, 'public');

            Document::create([
                'documentable_type' => PolicyEndorsement::class,
                'documentable_id' => $endorsement->id,
                'uploaded_by' => Auth::id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'document_type' => $request->document_type,
                'uploaded_at' => now(),
            ]);

            $uploadedCount++;
        }

        return redirect()->back()->with('success', "Successfully uploaded {$uploadedCount} document(s)");
    }

    // API Methods
    public function getPolicyEntities(Request $request)
    {
        // Get policy_id from route parameter, not request body
        $policyId = $request->route('policy_id');

        if (!$policyId) {
            return response()->json(['error' => 'Policy ID is required'], 400);
        }

        $policy = InsurancePolicy::find($policyId);

        if (!$policy) {
            return response()->json(['error' => 'Policy not found'], 404);
        }

        // Get ONLY ACTIVE entities using proper relationship query
        $activeEntities = $policy->entities()
            ->wherePivot('status', 'ACTIVE')
            ->get();

        // Also get total count for comparison
        $totalEntities = $policy->entities()->count();

        // Debug logging
        error_log("Policy {$policy->policy_number} entities: Total attached: {$totalEntities}, Active: {$activeEntities->count()}");

        $entities = $activeEntities->map(function ($entity) {
            return [
                'id' => $entity->id,
                'description' => $entity->description,
                'type' => $entity->type
            ];
        });

        return response()->json([
            'entities' => $entities,
            'policy_number' => $policy->policy_number,
            'total_entities' => $entities->count(),
            'debug_info' => [
                'total_attached' => $totalEntities,
                'active_count' => $activeEntities->count()
            ]
        ]);
    }

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
            'policy_id',
            'endorsement_number',
            'description',
            'effective_date'
        ]));

        return response()->json($endorsement);
    }

    public function apiDestroy(PolicyEndorsement $endorsement)
    {
        $endorsement->delete();
        return response()->json(['message' => 'Endorsement deleted']);
    }

    /**
     * Bulk actions for endorsements
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'endorsement_ids' => 'required|array|min:1',
            'endorsement_ids.*' => 'exists:endorsements,id',
        ]);

        $action = $request->action;
        $endorsementIds = $request->endorsement_ids;
        $count = count($endorsementIds);

        DB::transaction(function () use ($action, $endorsementIds, $count) {
            switch ($action) {
                case 'delete':
                    // Create audit logs before deletion
                    foreach ($endorsementIds as $endorsementId) {
                        $endorsement = PolicyEndorsement::find($endorsementId);
                        AuditLog::create([
                            'action' => 'DELETE',
                            'entity_type' => 'PolicyEndorsement',
                            'entity_id' => $endorsementId,
                            'policy_id' => $endorsement->policy_id,
                            'endorsement_id' => $endorsementId,
                            'metadata' => [
                                'endorsement_number' => $endorsement->endorsement_number,
                                'description' => $endorsement->description,
                                'effective_date' => $endorsement->effective_date,
                                'bulk_action' => true,
                                'deletion_reason' => 'Bulk deletion by user',
                            ],
                            'performed_by' => Auth::id(),
                        ]);
                    }
                    PolicyEndorsement::whereIn('id', $endorsementIds)->delete();
                    break;
            }
        });

        $actionText = [
            'delete' => 'deleted',
        ];

        $message = "Successfully {$actionText[$action]} {$count} endorsement(s)";
        return redirect()->back()->with('success', $message);
    }
}
