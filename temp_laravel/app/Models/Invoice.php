<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
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
     * Get the documentable model that owns the invoice.
     */
    public function documentable()
    {
        return $this->morphTo();
    }
    
    /**
     * Get the user who uploaded/created the invoice.
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
     * Scope a query to only include invoices.
     */
    public function scopeInvoices($query)
    {
        return $query->where('document_type', 'INVOICE');
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
     * Get unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', ['PAID', 'CANCELLED']);
    }
    
    /**
     * Get overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
                    ->whereNotIn('status', ['PAID', 'CANCELLED']);
    }
    
    /**
     * Get the balance amount (total - paid).
     */
    public function getBalanceAttribute()
    {
        return $this->total_amount - ($this->amount ?? 0);
    }
    
    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $lastInvoice = self::where('invoice_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return $prefix . $year . $month . '-' . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Update status based on payment.
     */
    public function updateStatus()
    {
        if ($this->total_amount && $this->amount >= $this->total_amount) {
            $this->update([
                'status' => 'PAID',
                'paid_date' => now()->toDateString()
            ]);
        } elseif ($this->amount > 0) {
            $this->update(['status' => 'PARTIALLY_PAID']);
        } elseif ($this->due_date && $this->due_date < now()->toDateString()) {
            $this->update(['status' => 'OVERDUE']);
        }
    }
}