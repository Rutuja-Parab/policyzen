<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\Student;
use App\Models\AuditLog;
use App\Services\StudentPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPolicyController extends Controller
{
    protected StudentPolicyService $studentPolicyService;

    public function __construct(StudentPolicyService $studentPolicyService)
    {
        $this->studentPolicyService = $studentPolicyService;
    }

    /**
     * Show the student management page for a policy
     */
    public function index(InsurancePolicy $policy)
    {
        $attachedStudents = $this->studentPolicyService->getStudentsInPolicy($policy);
        $availableStudents = $this->studentPolicyService->getAvailableStudents($policy, Auth::user()->company_id);
        $auditLogs = AuditLog::where('policy_id', $policy->id)
            ->where('entity_type', 'Student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('policies.students.index', compact('policy', 'attachedStudents', 'availableStudents', 'auditLogs'));
    }

    /**
     * Add students to policy with automatic premium calculation
     */
    public function addStudents(Request $request, InsurancePolicy $policy)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'premium_per_student' => 'nullable|numeric|min:0',
        ]);

        $result = $this->studentPolicyService->addStudentsWithCalculatedPremium(
            $policy,
            $validated['student_ids'],
            Auth::id()
        );

        if (count($result['success']) > 0) {
            $message = count($result['success']) . ' student(s) added successfully. Total debited: ₹' . number_format($result['total_debited'], 2);

            if (count($result['failed']) > 0) {
                $message .= '. ' . count($result['failed']) . ' student(s) failed to add.';
            }

            return redirect()->route('policies.students.index', $policy)
                ->with('success', $message)
                ->with('endorsement', $result['endorsement'])
                ->with('pdf_path', $result['pdf_path'] ?? null);
        }

        return redirect()->route('policies.students.index', $policy)
            ->with('error', 'Failed to add students. ' . collect($result['failed'])->pluck('reason')->implode(', '));
    }

    /**
     * Remove students from policy with automatic refund calculation
     */
    public function removeStudents(Request $request, InsurancePolicy $policy)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'removal_reason' => 'required|string|max:255',
            'endorsement_documents' => 'nullable|array',
            'endorsement_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_type' => 'nullable|in:REMOVAL_CERTIFICATE,MEDICAL_CERTIFICATE,TRANSFER_LETTER,RESIGNATION_LETTER,OTHER',
        ], [
            'removal_reason.required' => 'Please provide a reason for student removal',
            'student_ids.required' => 'Please select at least one student to remove',
        ]);

        $result = $this->studentPolicyService->removeStudentsWithCalculatedRefund(
            $policy,
            $validated['student_ids'],
            Auth::id(),
            $validated['removal_reason'],
            $request->file('endorsement_documents'),
            $validated['document_type'] ?? 'OTHER'
        );

        if (count($result['success']) > 0) {
            $message = count($result['success']) . ' student(s) removed successfully. Total credited: ₹' . number_format($result['total_credited'], 2);

            if (count($result['failed']) > 0) {
                $message .= '. ' . count($result['failed']) . ' student(s) failed to remove.';
            }

            if ($request->hasFile('endorsement_documents')) {
                $message .= ' Documents uploaded successfully.';
            }

            return redirect()->route('policies.students.index', $policy)
                ->with('success', $message)
                ->with('endorsement', $result['endorsement'])
                ->with('pdf_path', $result['pdf_path'] ?? null);
        }

        return redirect()->route('policies.students.index', $policy)
            ->with('error', 'Failed to remove students. ' . collect($result['failed'])->pluck('reason')->implode(', '));
    }

    /**
     * Get audit logs for a policy
     */
    public function auditLogs(InsurancePolicy $policy)
    {
        $logs = AuditLog::where('policy_id', $policy->id)
            ->where('entity_type', 'Student')
            ->with(['endorsement', 'performer'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('policies.students.audit_logs', compact('policy', 'logs'));
    }

    /**
     * Download endorsement PDF
     */
    public function downloadEndorsement(InsurancePolicy $policy, string $endorsementId)
    {
        $endorsement = $policy->endorsements()->findOrFail($endorsementId);
        $document = $endorsement->documents()->first();

        if (!$document) {
            return redirect()->back()->with('error', 'Endorsement PDF not found.');
        }

        return response()->download(storage_path('app/public/' . $document->file_path));
    }
}
