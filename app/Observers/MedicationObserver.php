<?php

namespace App\Observers;

use App\Models\Medication;
use App\Models\MedicationMovement;

class MedicationObserver
{
    public function created(Medication $medication): void
    {
        $quantity = (int) ($medication->quantity ?? 0);
        if ($quantity > 0) {
            MedicationMovement::create([
                'medication_id' => $medication->id,
                'type' => 'in',
                'quantity' => $quantity,
                'balance' => $medication->quantity,
                'reference_type' => Medication::class,
                'reference_id' => $medication->id,
                'notes' => 'Creación de medicamento con stock inicial',
            ]);
        }
    }

    public function updated(Medication $medication): void
    {
        if (! $medication->isDirty('quantity')) {
            return;
        }

        $old = (int) $medication->getOriginal('quantity');
        $new = (int) $medication->quantity;
        $delta = $new - $old;

        if ($delta === 0) {
            return;
        }

        MedicationMovement::create([
            'medication_id' => $medication->id,
            'type' => $delta > 0 ? 'in' : 'out',
            'quantity' => abs($delta),
            'balance' => $medication->quantity,
            'reference_type' => Medication::class,
            'reference_id' => $medication->id,
            'notes' => 'Ajuste manual de inventario',
        ]);
    }

    public function deleting(Medication $medication): void
    {
        $remaining = (int) ($medication->quantity ?? 0);
        if ($remaining > 0) {
            MedicationMovement::create([
                'medication_id' => $medication->id,
                'type' => 'out',
                'quantity' => $remaining,
                'balance' => 0,
                'reference_type' => Medication::class,
                'reference_id' => $medication->id,
                'notes' => 'Eliminación de medicamento (ajuste de salida del saldo restante)',
            ]);
        }
    }
} 