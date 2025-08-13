<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationOutputItem extends Model
{
    protected $fillable = [
        'output_id',
        'medication_id',
        'quantity',
    ];

    public function output(): BelongsTo
    {
        return $this->belongsTo(MedicationOutput::class, 'output_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
} 