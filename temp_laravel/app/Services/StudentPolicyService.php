<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Entity;
use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Student;
use App\Models\StudentPolicyPremium;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentPolicyService
{
    protected EndorsementPdfService $pdfService;

    // Premium calculation constants
    protected const RATE = 0.3079;
    protected const RATE_DIVISOR = 1000;
    protected const GST_RATE = 18;
    protected const DAYS_IN_YEAR = 365;

    public function __construct(EndorsementPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Calculate Annual Premium for a student
     * Formula: Rate × Sum Assured / 1000
     * 
     * @param float $sumAssured
     * @return float
     */
    public function calculateAnnualPremium(float $sumAssured): float
    {
        return round(self::RATE * $sumAssured / self::RATE_DIVISOR);
    }

    /**
     * Calculate Pro-rata Days between two dates
     * Formula: Date of Exit - Date of Joining + 1
     * 
     * @param string $dateOfJoining
     * @param string $dateOfExit
     * @return int
     */
    public function calculateProRataDays(string $dateOfJoining, string $dateOfExit): int
    {
        $doj = new \DateTime($dateOfJoining);
        $doe = new \DateTime($dateOfExit);
        $interval = $doj->diff($doe);
        return $interval->days + 1; // +1 to include both dates
    }

    /**
     * Calculate Prorata Premium
     * Formula: Pro-rata Days × Annual Premium / 365
     * 
     * @param int $proRataDays
     * @param float $annualPremium
     * @return float
     */
    public function calculateProrataPremium(int $proRataDays, float $annualPremium): float
    {
        return round($proRataDays * $annualPremium / self::DAYS_IN_YEAR);
    }

    /**
     * Calculate GST amount
     * Formula: Prorata Premium × 18%
     * 
     * @param float $prorataPremium
     * @return float
     */
    public function calculateGST(float $prorataPremium): float
    {
        return round($prorataPremium * self::GST_RATE / 100);
    }

    /**
     * Calculate Final Premium (Prorata + GST)
     * 
     * @param float $prorataPremium
     * @param float|null $gstAmount
     * @return array
     */
    public function calculateFinalPremium(float $prorataPremium, ?float $gstAmount = null): array
    {
        $gst = $gstAmount ?? $this->calculateGST($prorataPremium);
        return [
            'prorata_premium' => $prorataPremium,
            'gst_amount' => $gst,
            'final_premium' => $prorataPremium + $gst,
        ];
    }

    /**
     * Calculate complete premium breakdown for a student
     * 
     * @param float $sumAssured
     * @param string $dateOfJoining
     * @param string $dateOfExit
     * @return array
     */
    public function calculateStudentPremium(float $sumAssured, string $dateOfJoining, string $dateOfExit): array
    {
        $annualPremium = $this->calculateAnnualPremium($sumAssured);
        $proRataDays = $this->calculateProRataDays($dateOfJoining, $dateOfExit);
        $prorataPremium = $this->calculateProrataPremium($proRataDays, $annualPremium);
        $gstAmount = $this->calculateGST($prorataPremium);
        $finalPremium = $this->calculateFinalPremium($prorataPremium, $gstAmount);

        return [
            'sum_insured' => $sumAssured,
            'rate' => self::RATE,
            'rate_divisor' => self::RATE_DIVISOR,
            'annual_premium' => $annualPremium,
            'date_of_joining' => $dateOfJoining,
            'date_of_exit' => $dateOfExit,
            'pro_rata_days' => $proRataDays,
            'prorata_premium' => $prorataPremium,
            'gst_rate' => self::GST_RATE,
            'gst_amount' => $gstAmount,
            'final_premium' => $finalPremium['final_premium'],
        ];
    }

    /**
     * Save student premium record to database
     * 
     * @param Student $student
     * @param InsurancePolicy $policy
     * @param PolicyEndorsement|null $endorsement
     * @param array $premiumBreakdown
     * @param string $premiumType
     * @return StudentPolicyPremium
     */
    public function saveStudentPremium(Student $student, InsurancePolicy $policy, ?PolicyEndorsement $endorsement, array $premiumBreakdown, string $premiumType = 'ADDITION'): StudentPolicyPremium
    {
        return StudentPolicyPremium::create([
            'student_id' => $student->id,
            'policy_id' => $policy->id,
            'endorsement_id' => $endorsement?->id,
            'sum_insured' => $premiumBreakdown['sum_insured'],
            'rate' => $premiumBreakdown['rate'],
            'annual_premium' => $premiumBreakdown['annual_premium'],
            'date_of_joining' => $premiumBreakdown['date_of_joining'],
            'date_of_exit' => $premiumBreakdown['date_of_exit'],
            'pro_rata_days' => $premiumBreakdown['pro_rata_days'],
            'prorata_premium' => $premiumBreakdown['prorata_premium'],
            'gst_rate' => $premiumBreakdown['gst_rate'],
            'gst_amount' => $premiumBreakdown['gst_amount'],
            'final_premium' => $premiumBreakdown['final_premium'],
            'premium_type' => $premiumType,
        ]);
    }

    /**
     * Add students to policy with automatic premium calculation
     * 
     * @param InsurancePolicy $policy
     * @param array $studentIds
     * @param int $userId
     * @return array
     */
    public function addStudentsWithCalculatedPremium(InsurancePolicy $policy, array $studentIds, int $userId): array
    {
        return DB::transaction(function () use ($policy, $studentIds, $userId) {
            $results = [
                'success' => [],
                'failed' => [],
                'endorsement' => null,
                'total_debited' => 0,
                'premium_breakdown' => [],
            ];

            $students = Student::whereIn('id', $studentIds)->get();
            $balanceBefore = $policy->sum_insured;
            $totalDebit = 0;
            $addedEntities = [];

            foreach ($students as $student) {
                // Calculate premium for this student
                $sumAssured = $student->sum_insured ?? 1000000; // Default ₹10,00,000 if not set
                $dateOfJoining = $student->date_of_joining ?? now()->format('Y-m-d');
                // Set date of exit to exactly one year from date of joining
                $dateOfExit = \Carbon\Carbon::parse($dateOfJoining)->addYear()->format('Y-m-d');

                $premiumBreakdown = $this->calculateStudentPremium(
                    $sumAssured,
                    $dateOfJoining,
                    $dateOfExit
                );

                $premiumPerStudent = $premiumBreakdown['final_premium'];

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
                    'premium_breakdown' => $premiumBreakdown,
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
                    'description' => 'Addition of ' . count($addedEntities) . ' student(s) to health policy with calculated premium',
                    'effective_date' => now(),
                    'created_by' => $userId,
                ]);

                // Attach entities to endorsement
                foreach ($addedEntities as $entity) {
                    $endorsement->entities()->attach($entity->id);
                }

                // Create audit log and save premium record
                foreach ($results['success'] as $successStudent) {
                    $student = $students->where('id', $successStudent['student_id'])->first();

                    // Save premium record to database
                    $this->saveStudentPremium(
                        $student,
                        $policy,
                        $endorsement,
                        $successStudent['premium_breakdown'],
                        'ADDITION'
                    );

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
                            'premium_breakdown' => $successStudent['premium_breakdown'],
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
     * Remove students from policy with automatic refund calculation
     * 
     * @param InsurancePolicy $policy
     * @param array $studentIds
     * @param int $userId
     * @param string $removalReason
     * @param array|null $documents
     * @param string $documentType
     * @return array
     */
    public function removeStudentsWithCalculatedRefund(InsurancePolicy $policy, array $studentIds, int $userId, string $removalReason = '', array $documents = null, string $documentType = 'OTHER'): array
    {
        return DB::transaction(function () use ($policy, $studentIds, $userId, $removalReason, $documents, $documentType) {
            $results = [
                'success' => [],
                'failed' => [],
                'endorsement' => null,
                'total_credited' => 0,
                'refund_breakdown' => [],
            ];

            $students = Student::whereIn('id', $studentIds)->get();
            $balanceBefore = $policy->sum_insured;
            $totalCredit = 0;
            $removedEntities = [];

            foreach ($students as $student) {
                // Calculate refund for this student based on actual coverage period
                $sumAssured = $student->sum_insured ?? 1000000;
                $dateOfJoining = $student->date_of_joining ?? now()->format('Y-m-d');
                $dateOfExit = now()->format('Y-m-d'); // Exit date is now

                $refundBreakdown = $this->calculateStudentPremium(
                    $sumAssured,
                    $dateOfJoining,
                    $dateOfExit
                );

                $refundPerStudent = $refundBreakdown['final_premium'];

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
                    'refund_breakdown' => $refundBreakdown,
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

                // Create audit log and save premium record
                foreach ($results['success'] as $successStudent) {
                    $student = $students->where('id', $successStudent['student_id'])->first();

                    // Save premium record to database
                    $this->saveStudentPremium(
                        $student,
                        $policy,
                        $endorsement,
                        $successStudent['refund_breakdown'],
                        'REMOVAL'
                    );

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
                            'refund_breakdown' => $successStudent['refund_breakdown'],
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

        // Get students that have entities in this policy
        return Student::whereHas('entity', function ($query) use ($entityIds) {
            $query->whereIn('id', $entityIds);
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

    /**
     * Get premium history for a student
     * 
     * @param int $studentId
     * @return \Illuminate\Support\Collection
     */
    public function getStudentPremiumHistory(int $studentId): \Illuminate\Support\Collection
    {
        return StudentPolicyPremium::where('student_id', $studentId)
            ->with(['policy', 'endorsement'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all premiums for a policy
     * 
     * @param int $policyId
     * @return \Illuminate\Support\Collection
     */
    public function getPolicyPremiums(int $policyId): \Illuminate\Support\Collection
    {
        return StudentPolicyPremium::where('policy_id', $policyId)
            ->with(['student', 'endorsement'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
