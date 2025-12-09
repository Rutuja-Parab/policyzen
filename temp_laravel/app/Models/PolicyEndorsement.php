<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PolicyEndorsement extends Model
{
    use HasFactory;

    protected $table = 'endorsements';

    protected $fillable = [
        'policy_id',
        'endorsement_number',
        'description',
        'effective_date',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    /**
     * Get the policy that owns the endorsement.
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'policy_id');
    }

    /**
     * Get the user who created the endorsement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the entities associated with this endorsement.
     */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'endorsement_entities', 'endorsement_id', 'entity_id')
            ->withTimestamps();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
