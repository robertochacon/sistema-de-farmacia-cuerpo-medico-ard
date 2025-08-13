<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    protected $fillable = [
        'name',
        'generic_name',
        'presentation',
        'concentration',
        'manufacturer',
        'lot_number',
        'expiration_date',
        'quantity',
        'unit_price',
        'entry_type',
        'notes',
        'status',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'status' => 'boolean',
        'unit_price' => 'decimal:2',
    ];
} 