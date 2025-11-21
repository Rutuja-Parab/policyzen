<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Entity extends Model
{
    use HasFactory;


    protected $fillable = [
        'company_id',
        'type',
        'entity_id',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function entity()
    {
        return $this->morphTo(null, 'type', 'entity_id');
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(InsurancePolicy::class, 'policy_entities', 'entity_id', 'policy_id')
            ->withPivot('effective_date', 'termination_date', 'status')
            ->withTimestamps();
    }

    public function activePolicies(): BelongsToMany
    {
        return $this->belongsToMany(InsurancePolicy::class, 'policy_entities', 'entity_id', 'policy_id')
            ->wherePivot('status', 'ACTIVE')
            ->withPivot('effective_date', 'termination_date', 'status')
            ->withTimestamps();
    }
}
