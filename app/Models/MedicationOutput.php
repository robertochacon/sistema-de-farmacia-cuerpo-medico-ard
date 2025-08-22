<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationOutput extends Model
{
    protected $fillable = [
        'department_id',
        'user_id',
        'reason',
        'prescription_image',
        'patient_type',
        'patient_external_id',
        'patient_name',
        'doctor_external_id',
        'doctor_name',
        'responsible_external_id',
        'responsible_name',
        'notes',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicationOutputItem::class, 'output_id');
    }
} 