<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;


    protected $fillable = [
        'policy_id',
        'endorsement_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'document_type',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class, 'policy_id', 'id');
    }

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(PolicyEndorsement::class, 'endorsement_id', 'id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }
}
