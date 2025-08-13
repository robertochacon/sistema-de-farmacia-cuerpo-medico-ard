<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationEntryItem extends Model
{
    protected $fillable = [
        'entry_id',
        'medication_id',
        'quantity',
        'unit_price',
        'expiration_date',
        'lot_number',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(MedicationEntry::class, 'entry_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
} 