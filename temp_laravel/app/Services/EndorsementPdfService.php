<?php

namespace App\Services;

use App\Models\Document;
use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EndorsementPdfService
{
    /**
     * Generate PDF for student addition endorsement
     */
    public function generateAdditionEndorsementPdf(PolicyEndorsement $endorsement, array $students, InsurancePolicy $policy): string
    {
        $data = [
            'endorsement' => $endorsement,
            'policy' => $policy,
            'students' => $students,
            'type' => 'ADDITION',
            'total_amount' => collect($students)->sum('premium'),
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('endorsements.pdf.student_endorsement', $data);
        
        $filename = 'endorsements/' . $endorsement->endorsement_number . '_addition_' . now()->format('YmdHis') . '.pdf';
        
        Storage::disk('public')->put($filename, $pdf->output());

        // Create document record
        Document::create([
            'documentable_type' => PolicyEndorsement::class,
            'documentable_id' => $endorsement->id,
            'name' => 'Student Addition Endorsement - ' . $endorsement->endorsement_number,
            'file_path' => $filename,
            'file_type' => 'application/pdf',
            'file_size' => Storage::disk('public')->size($filename),
        ]);

        return $filename;
    }

    /**
     * Generate PDF for student removal endorsement
     */
    public function generateRemovalEndorsementPdf(PolicyEndorsement $endorsement, array $students, InsurancePolicy $policy): string
    {
        $data = [
            'endorsement' => $endorsement,
            'policy' => $policy,
            'students' => $students,
            'type' => 'REMOVAL',
            'total_amount' => collect($students)->sum('refund'),
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('endorsements.pdf.student_endorsement', $data);
        
        $filename = 'endorsements/' . $endorsement->endorsement_number . '_removal_' . now()->format('YmdHis') . '.pdf';
        
        Storage::disk('public')->put($filename, $pdf->output());

        // Create document record
        Document::create([
            'documentable_type' => PolicyEndorsement::class,
            'documentable_id' => $endorsement->id,
            'name' => 'Student Removal Endorsement - ' . $endorsement->endorsement_number,
            'file_path' => $filename,
            'file_type' => 'application/pdf',
            'file_size' => Storage::disk('public')->size($filename),
        ]);

        return $filename;
    }
}
