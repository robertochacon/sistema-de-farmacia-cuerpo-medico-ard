<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'type', // company | institution
        'rnc',
        'address',
        'phone',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(MedicationEntry::class);
    }
}
