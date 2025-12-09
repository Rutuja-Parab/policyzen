<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;
    
    protected $table = 'documents';
    
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'invoice_number',
        'file_name',
        'file_path',
        'file_type',
        'document_type',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'issue_date',
        'due_date',
        'paid_date',
        'notes',
        'uploaded_by',
        'uploaded_at',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'uploaded_at' => 'datetime',
    ];
    
    /**
     * Get the documentable model that owns the credit note.
     */
    public function documentable()
    {
        return $this->morphTo();
    }
    
    /**
     * Get the user who uploaded/created the credit note.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    /**
     * Get the associated policy (if documentable is a policy).
     */
    public function policy()
    {
        return $this->documentable_type === InsurancePolicy::class 
            ? $this->belongsTo(InsurancePolicy::class, 'documentable_id')
            : null;
    }
    
    /**
     * Get the parent invoice this credit note applies to.
     */
    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_number', 'invoice_number');
    }
    
    /**
     * Scope a query to only include credit notes.
     */
    public function scopeCreditNotes($query)
    {
        return $query->where('document_type', 'CREDIT_NOTE');
    }
    
    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }
    
    /**
     * Get unused credit notes (not fully applied).
     */
    public function scopeUnused($query)
    {
        return $query->whereIn('status', ['DRAFT', 'ISSUED']);
    }
    
    /**
     * Get applied credit notes.
     */
    public function scopeApplied($query)
    {
        return $query->where('status', 'APPLIED');
    }
    
    /**
     * Get the unused amount of this credit note.
     */
    public function getUnusedAmountAttribute()
    {
        // This would be calculated based on applications against invoices
        // For now, return total amount as unused
        return $this->total_amount;
    }
    
    /**
     * Generate a unique credit note number.
     */
    public static function generateCreditNoteNumber()
    {
        $prefix = 'CN-';
        $year = date('Y');
        $month = date('m');
        
        // Get the last credit note number for this month
        $lastCreditNote = self::where('invoice_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastCreditNote) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastCreditNote->invoice_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return $prefix . $year . $month . '-' . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Apply the credit note to an invoice.
     */
    public function applyToInvoice($invoiceId, $amount = null)
    {
        $applicationAmount = $amount ?? $this->total_amount;
        
        // Create a record of the application (this would typically be in a separate table)
        // For now, just update the status
        $this->update([
            'status' => 'APPLIED',
            'paid_date' => now()->toDateString(),
            'notes' => $this->notes . "\nApplied to Invoice ID: {$invoiceId}, Amount: ₹{$applicationAmount}"
        ]);
        
        // Update the parent invoice
        $parentInvoice = Invoice::find($invoiceId);
        if ($parentInvoice) {
            $parentInvoice->update([
                'amount' => $parentInvoice->amount + $applicationAmount
            ]);
            $parentInvoice->updateStatus();
        }
        
        return $this;
    }
    
    /**
     * Cancel the credit note.
     */
    public function cancel($reason = '')
    {
        $this->update([
            'status' => 'CANCELLED',
            'notes' => $this->notes . "\nCancelled: {$reason}"
        ]);
        
        return $this;
    }
}