<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPolicyPremium extends Model
{
    use HasFactory;

    protected $table = 'student_policy_premiums';

    protected $fillable = [
        'student_id',
        'policy_id',
        'endorsement_id',
        'sum_insured',
        'rate',
        'annual_premium',
        'date_of_joining',
        'date_of_exit',
        'pro_rata_days',
        'prorata_premium',
        'gst_rate',
        'gst_amount',
        'final_premium',
        'premium_type',
    ];

    protected $casts = [
        'sum_insured' => 'decimal:2',
        'rate' => 'decimal:4',
        'annual_premium' => 'decimal:2',
        'date_of_joining' => 'date',
        'date_of_exit' => 'date',
        'pro_rata_days' => 'integer',
        'prorata_premium' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'final_premium' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'policy_id', 'id');
    }

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(PolicyEndorsement::class, 'endorsement_id', 'id');
    }
}
