<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'course_code',
        'course_name',
        'description',
        'department',
        'duration_months',
        'status',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function entity(): HasOne
    {
        return $this->hasOne(Entity::class, 'entity_id', 'id')->where('type', 'COURSE');
    }
}
