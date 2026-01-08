<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class InsurancePolicy extends Model
{
    use HasFactory;
    protected $table = 'policies';

    protected $fillable = [
        'company_id',
        'policy_number',
        'insurance_type',
        'provider',
        'start_date',
        'end_date',
        'starting_coverage_pool',
        'available_coverage_pool',
        'utilized_coverage_pool',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'starting_coverage_pool' => 'decimal:2',
        'available_coverage_pool' => 'decimal:2',
        'utilized_coverage_pool' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'policy_entities', 'policy_id', 'entity_id')
            ->withPivot('effective_date', 'termination_date', 'status')
            ->withTimestamps();
    }

    public function activeEntities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'policy_entities', 'policy_id', 'entity_id')
            ->wherePivot('status', 'ACTIVE')
            ->withPivot('effective_date', 'termination_date', 'status')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function endorsements()
    {
        return $this->hasMany(PolicyEndorsement::class, 'policy_id', 'id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
