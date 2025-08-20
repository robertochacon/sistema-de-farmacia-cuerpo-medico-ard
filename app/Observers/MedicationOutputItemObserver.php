<?php

namespace App\Observers;

use App\Models\MedicationOutputItem;
use App\Models\Medication;
use App\Models\MedicationMovement;
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

        if ($medication->quantity < $quantity) {
            throw ValidationException::withMessages([
                'items' => "Stock insuficiente para {$medication->name}. Disponible: {$medication->quantity}",
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
            if (! $newMedication || $newMedication->quantity < $newQuantity) {
                $available = $newMedication?->quantity ?? 0;
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
                if (! $medication || $medication->quantity < $delta) {
                    $available = $medication?->quantity ?? 0;
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

        Medication::where('id', $item->medication_id)
            ->increment('quantity', $direction * $quantity);

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