<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MedicationMovement extends Model
{
    protected $fillable = [
        'medication_id',
        'type',
        'quantity',
        'balance',
        'reference_type',
        'reference_id',
        'notes',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
} 