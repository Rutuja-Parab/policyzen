<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Entity;
use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentPolicyService
{
    protected EndorsementPdfService $pdfService;

    public function __construct(EndorsementPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Add multiple students to a health policy
     * 
     * @param InsurancePolicy $policy
     * @param array $studentIds
     * @param float $premiumPerStudent
     * @param int $userId
     * @return array
     */
    public function addStudentsToPolicy(InsurancePolicy $policy, array $studentIds, float $premiumPerStudent, int $userId): array
    {
        return DB::transaction(function () use ($policy, $studentIds, $premiumPerStudent, $userId) {
            $results = [
                'success' => [],
                'failed' => [],
                'endorsement' => null,
                'total_debited' => 0,
            ];

            $students = Student::whereIn('id', $studentIds)->get();
            $balanceBefore = $policy->sum_insured;
            $totalDebit = 0;
            $addedEntities = [];

            foreach ($students as $student) {
                // Check if student already has an entity
                $entity = Entity::where('type', 'STUDENT')
                    ->where('entity_id', $student->id)
                    ->first();

                if (!$entity) {
                    // Create entity for student
                    $entity = Entity::create([
                        'type' => 'STUDENT',
                        'entity_id' => $student->id,
                        'company_id' => $student->company_id,
                        'description' => 'Student: ' . $student->name,
                    ]);
                }

                // Check if already attached to this policy
                $existingAttachment = DB::table('policy_entities')
                    ->where('policy_id', $policy->id)
                    ->where('entity_id', $entity->id)
                    ->where('status', 'ACTIVE')
                    ->first();

                if ($existingAttachment) {
                    $results['failed'][] = [
                        'student_id' => $student->id,
                        'name' => $student->name,
                        'reason' => 'Already attached to this policy',
                    ];
                    continue;
                }

                // Attach student to policy
                $policy->entities()->attach($entity->id, [
                    'effective_date' => now(),
                    'status' => 'ACTIVE',
                ]);

                $totalDebit += $premiumPerStudent;
                $addedEntities[] = $entity;

                $results['success'][] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'premium' => $premiumPerStudent,
                ];
            }

            if (count($addedEntities) > 0) {
                // Update policy sum_insured (debit)
                $balanceAfter = $balanceBefore - $totalDebit;
                $policy->sum_insured = $balanceAfter;
                $policy->save();

                // Create endorsement
                $endorsement = PolicyEndorsement::create([
                    'policy_id' => $policy->id,
                    'endorsement_number' => $this->generateEndorsementNumber($policy),
                    'description' => 'Addition of ' . count($addedEntities) . ' student(s) to health policy',
                    'effective_date' => now(),
                    'created_by' => $userId,
                ]);

                // Attach entities to endorsement
                foreach ($addedEntities as $entity) {
                    $endorsement->entities()->attach($entity->id);
                }

                // Create audit log
                foreach ($results['success'] as $successStudent) {
                    AuditLog::create([
                        'action' => 'ADD_STUDENT',
                        'entity_type' => 'Student',
                        'entity_id' => $successStudent['student_id'],
                        'policy_id' => $policy->id,
                        'endorsement_id' => $endorsement->id,
                        'amount' => $successStudent['premium'],
                        'transaction_type' => 'DEBIT',
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'metadata' => [
                            'student_name' => $successStudent['name'],
                            'endorsement_number' => $endorsement->endorsement_number,
                        ],
                        'performed_by' => $userId,
                    ]);
                }

                // Generate PDF
                $pdfPath = $this->pdfService->generateAdditionEndorsementPdf($endorsement, $results['success'], $policy);
                
                $results['endorsement'] = $endorsement;
                $results['total_debited'] = $totalDebit;
                $results['pdf_path'] = $pdfPath;
            }

            return $results;
        });
    }

    /**
     * Remove multiple students from a health policy
     * 
     * @param InsurancePolicy $policy
     * @param array $studentIds
     * @param float $refundPerStudent
     * @param int $userId
     * @param string $removalReason
     * @param array|null $documents
     * @param string $documentType
     * @return array
     */
    public function removeStudentsFromPolicy(InsurancePolicy $policy, array $studentIds, float $refundPerStudent, int $userId, string $removalReason = '', array $documents = null, string $documentType = 'OTHER'): array
    {
        return DB::transaction(function () use ($policy, $studentIds, $refundPerStudent, $userId, $removalReason, $documents, $documentType) {
            $results = [
                'success' => [],
                'failed' => [],
                'endorsement' => null,
                'total_credited' => 0,
            ];

            $students = Student::whereIn('id', $studentIds)->get();
            $balanceBefore = $policy->sum_insured;
            $totalCredit = 0;
            $removedEntities = [];

            foreach ($students as $student) {
                // Find entity for student
                $entity = Entity::where('type', 'STUDENT')
                    ->where('entity_id', $student->id)
                    ->first();

                if (!$entity) {
                    $results['failed'][] = [
                        'student_id' => $student->id,
                        'name' => $student->name,
                        'reason' => 'Student entity not found',
                    ];
                    continue;
                }

                // Check if attached to this policy
                $existingAttachment = DB::table('policy_entities')
                    ->where('policy_id', $policy->id)
                    ->where('entity_id', $entity->id)
                    ->where('status', 'ACTIVE')
                    ->first();

                if (!$existingAttachment) {
                    $results['failed'][] = [
                        'student_id' => $student->id,
                        'name' => $student->name,
                        'reason' => 'Not attached to this policy',
                    ];
                    continue;
                }

                // Update policy_entities to mark as terminated
                DB::table('policy_entities')
                    ->where('policy_id', $policy->id)
                    ->where('entity_id', $entity->id)
                    ->update([
                        'status' => 'TERMINATED',
                        'termination_date' => now(),
                        'updated_at' => now(),
                    ]);

                $totalCredit += $refundPerStudent;
                $removedEntities[] = $entity;

                $results['success'][] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'refund' => $refundPerStudent,
                ];
            }

            if (count($removedEntities) > 0) {
                // Update policy sum_insured (credit)
                $balanceAfter = $balanceBefore + $totalCredit;
                $policy->sum_insured = $balanceAfter;
                $policy->save();

                // Create endorsement
                $endorsement = PolicyEndorsement::create([
                    'policy_id' => $policy->id,
                    'endorsement_number' => $this->generateEndorsementNumber($policy),
                    'description' => 'Removal of ' . count($removedEntities) . ' student(s) from health policy. Reason: ' . $removalReason,
                    'effective_date' => now(),
                    'created_by' => $userId,
                ]);

                // Attach entities to endorsement
                foreach ($removedEntities as $entity) {
                    $endorsement->entities()->attach($entity->id);
                }

                // Handle document uploads if provided
                if ($documents && count($documents) > 0) {
                    foreach ($documents as $file) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('documents/endorsements', $fileName, 'public');
                        
                        Document::create([
                            'documentable_type' => PolicyEndorsement::class,
                            'documentable_id' => $endorsement->id,
                            'uploaded_by' => $userId,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_type' => $file->getMimeType(),
                            'document_type' => $documentType,
                            'uploaded_at' => now(),
                        ]);
                    }
                }

                // Create audit log
                foreach ($results['success'] as $successStudent) {
                    AuditLog::create([
                        'action' => 'REMOVE_STUDENT',
                        'entity_type' => 'Student',
                        'entity_id' => $successStudent['student_id'],
                        'policy_id' => $policy->id,
                        'endorsement_id' => $endorsement->id,
                        'amount' => $successStudent['refund'],
                        'transaction_type' => 'CREDIT',
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'metadata' => [
                            'student_name' => $successStudent['name'],
                            'endorsement_number' => $endorsement->endorsement_number,
                        ],
                        'performed_by' => $userId,
                    ]);
                }

                // Generate PDF
                $pdfPath = $this->pdfService->generateRemovalEndorsementPdf($endorsement, $results['success'], $policy);
                
                $results['endorsement'] = $endorsement;
                $results['total_credited'] = $totalCredit;
                $results['pdf_path'] = $pdfPath;
            }

            return $results;
        });
    }

    /**
     * Generate unique endorsement number
     */
    protected function generateEndorsementNumber(InsurancePolicy $policy): string
    {
        $count = PolicyEndorsement::where('policy_id', $policy->id)->count() + 1;
        return $policy->policy_number . '-END-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get students attached to a policy
     */
    public function getStudentsInPolicy(InsurancePolicy $policy): \Illuminate\Support\Collection
    {
        $entityIds = $policy->activeEntities()
            ->where('type', 'STUDENT')
            ->pluck('entity_id');

        return Student::whereIn('id', function ($query) use ($entityIds) {
            $query->select('entity_id')
                ->from('entities')
                ->whereIn('id', $entityIds)
                ->where('type', 'STUDENT');
        })->get();
    }

    /**
     * Get available students (not in policy)
     */
    public function getAvailableStudents(InsurancePolicy $policy, int $companyId): \Illuminate\Support\Collection
    {
        $attachedStudentIds = $this->getStudentsInPolicy($policy)->pluck('id');

        return Student::where('company_id', $companyId)
            ->whereNotIn('id', $attachedStudentIds)
            ->get();
    }
}
