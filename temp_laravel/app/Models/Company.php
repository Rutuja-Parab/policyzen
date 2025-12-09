<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'parent_company_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => 'string',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'company_id', 'id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'company_id', 'id');
    }

    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'company_id', 'id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'company_id', 'id');
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class, 'company_id', 'id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'company_id', 'id');
    }

    public function entity(): HasOne
    {
        return $this->hasOne(Entity::class, 'entity_id', 'id')->where('type', 'COMPANY');
    }

    public function parentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_company_id', 'id');
    }

    public function childCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_company_id', 'id');
    }
}
