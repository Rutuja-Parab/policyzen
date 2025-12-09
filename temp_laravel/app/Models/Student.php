<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'student_id',
        'name',
        'course',
        'email',
        'phone',
        'dob',
        'gender',
        'rank',
        'age',
        'status',
    ];

    protected $casts = [
        'age' => 'integer',
        'dob' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function entity(): HasOne
    {
        return $this->hasOne(Entity::class, 'entity_id', 'id')->where('type', 'STUDENT');
    }
}
