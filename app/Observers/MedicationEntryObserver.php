<?php

namespace App\Observers;

use App\Models\MedicationEntry;
use App\Models\MedicationEntryItem;
use App\Models\Medication;
use App\Models\MedicationMovement;
use Illuminate\Support\Facades\DB;

class MedicationEntryObserver
{
    // Ajustes de inventario ahora se manejan por ítem en MedicationEntryItemObserver
} 