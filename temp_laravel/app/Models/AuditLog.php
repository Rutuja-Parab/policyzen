<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'policy_id',
        'endorsement_id',
        'amount',
        'transaction_type',
        'balance_before',
        'balance_after',
        'metadata',
        'performed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'entity_id' => 'integer',
        'policy_id' => 'integer',
        'endorsement_id' => 'integer',
        'performed_by' => 'integer',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'policy_id');
    }

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(PolicyEndorsement::class, 'endorsement_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the entity (polymorphic relationship)
     */
    public function entity()
    {
        $modelClass = match($this->entity_type) {
            'Student' => Student::class,
            'Employee' => Employee::class,
            'Vehicle' => Vehicle::class,
            'Vessel' => Vessel::class,
            default => null,
        };

        if ($modelClass && $this->entity_id) {
            return $modelClass::find($this->entity_id);
        }

        return null;
    }
}
