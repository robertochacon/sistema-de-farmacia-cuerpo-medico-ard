<?php

namespace App\Observers;

use App\Models\MedicationOutputItem;
use App\Models\Medication;
use App\Models\MedicationMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicationOutputItemObserver
{
    public function creating(MedicationOutputItem $item): void
    {
        $quantity = (int) ($item->quantity ?? 0);
        $medication = Medication::find($item->medication_id);

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'items' => 'La cantidad debe ser mayor a 0.',
            ]);
        }

        if (! $medication) {
            throw ValidationException::withMessages([
                'items' => 'El medicamento seleccionado no existe.',
            ]);
        }

        $available = (int) ($medication->quantity ?? 0);
        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'items' => "Stock insuficiente para {$medication->name}. Disponible: {$available}",
            ]);
        }
    }

    public function created(MedicationOutputItem $item): void
    {
        $this->adjustInventoryAndLog($item, -1, $item->quantity);
    }

    public function updated(MedicationOutputItem $item): void
    {
        $originalMedicationId = (int) $item->getOriginal('medication_id');
        $originalQuantity = (int) $item->getOriginal('quantity');
        $newMedicationId = (int) $item->medication_id;
        $newQuantity = (int) $item->quantity;

        if ($originalMedicationId !== $newMedicationId) {
            // Validate new medication has enough stock for the new quantity
            $newMedication = Medication::find($newMedicationId);
            $available = (int) ($newMedication?->quantity ?? 0);
            if (! $newMedication || $available < $newQuantity) {
                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para {$newMedication?->name}. Disponible: {$available}",
                ]);
            }
            // Revert old medication quantity
            $this->adjustInventoryAndLog((clone $item)->forceFill(['medication_id' => $originalMedicationId]), +1, $originalQuantity);
            // Apply new medication quantity
            $this->adjustInventoryAndLog($item, -1, $newQuantity);
            return;
        }

        $delta = $newQuantity - $originalQuantity;
        if ($delta !== 0) {
            if ($delta > 0) {
                // Increasing output: ensure there is enough stock
                $medication = Medication::find($newMedicationId);
                $available = (int) ($medication?->quantity ?? 0);
                if (! $medication || $available < $delta) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$medication?->name}. Disponible: {$available}",
                    ]);
                }
                $this->adjustInventoryAndLog($item, -1, $delta);
            } else {
                // Decreasing output: return stock, no validation needed
                $this->adjustInventoryAndLog($item, +1, abs($delta));
            }
        }
    }

    public function deleted(MedicationOutputItem $item): void
    {
        $this->adjustInventoryAndLog($item, +1, $item->quantity);
    }

    private function adjustInventoryAndLog(MedicationOutputItem $item, int $direction, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $delta = (int) ($direction * $quantity);
        Medication::where('id', $item->medication_id)
            ->update([
                'quantity' => DB::raw('COALESCE(quantity, 0) + ' . $delta),
            ]);

        $medication = Medication::find($item->medication_id);
        $output = $item->output()->first();

        MedicationMovement::create([
            'medication_id' => $item->medication_id,
            'type' => $direction > 0 ? 'in' : 'out',
            'quantity' => $quantity,
            'balance' => (int) $medication->quantity,
            'reference_type' => $output ? get_class($output) : null,
            'reference_id' => $output?->id,
            'notes' => $output?->reason,
        ]);
    }
} 