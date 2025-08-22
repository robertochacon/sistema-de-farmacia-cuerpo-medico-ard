<?php

namespace App\Observers;

use App\Models\MedicationEntryItem;
use App\Models\Medication;
use App\Models\MedicationMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicationEntryItemObserver
{
    public function creating(MedicationEntryItem $item): void
    {
        $quantity = (int) ($item->quantity ?? 0);
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'items' => 'La cantidad del ítem debe ser mayor a 0.',
            ]);
        }

        if (! Medication::find($item->medication_id)) {
            throw ValidationException::withMessages([
                'items' => 'El medicamento seleccionado no existe.',
            ]);
        }
    }

    public function created(MedicationEntryItem $item): void
    {
        $this->adjustInventoryAndUpdateProps($item, +1, $item->quantity);
    }

    public function updated(MedicationEntryItem $item): void
    {
        $originalMedicationId = (int) $item->getOriginal('medication_id');
        $originalQuantity = (int) $item->getOriginal('quantity');
        $newMedicationId = (int) $item->medication_id;
        $newQuantity = (int) $item->quantity;

        if ($originalMedicationId !== $newMedicationId) {
            // Validate we can revert stock from the original medication
            $originalMedication = Medication::find($originalMedicationId);
            $available = (int) ($originalMedication?->quantity ?? 0);
            if (! $originalMedication || $available < $originalQuantity) {
                throw ValidationException::withMessages([
                    'items' => "No se puede cambiar el medicamento porque el original no tiene saldo suficiente para revertir. Disponible: {$available}",
                ]);
            }
            // Revert old medication quantity
            $this->adjustInventoryAndUpdateProps((clone $item)->forceFill(['medication_id' => $originalMedicationId]), -1, $originalQuantity);
            // Apply new medication quantity
            $this->adjustInventoryAndUpdateProps($item, +1, $newQuantity);
            return;
        }

        $delta = $newQuantity - $originalQuantity;
        if ($delta !== 0) {
            if ($delta > 0) {
                // Increasing entry: just add stock
                $this->adjustInventoryAndUpdateProps($item, +1, $delta);
            } else {
                // Decreasing entry: ensure enough balance to remove
                $medication = Medication::find($newMedicationId);
                $absDelta = abs($delta);
                $available = (int) ($medication?->quantity ?? 0);
                if (! $medication || $available < $absDelta) {
                    throw ValidationException::withMessages([
                        'items' => "No se puede reducir la entrada porque el stock actual ({$available}) es menor que la reducción solicitada ({$absDelta}).",
                    ]);
                }
                $this->adjustInventoryAndUpdateProps($item, -1, $absDelta);
            }
        }
    }

    public function deleted(MedicationEntryItem $item): void
    {
        // Ensure enough balance to remove the entry effect
        $medication = Medication::find($item->medication_id);
        $qty = (int) $item->quantity;
        $available = (int) ($medication?->quantity ?? 0);
        if (! $medication || $available < $qty) {
            throw ValidationException::withMessages([
                'items' => "No se puede eliminar el ítem de entrada porque el stock actual ({$available}) es menor que lo que se debe revertir ({$qty}).",
            ]);
        }
        $this->adjustInventoryAndUpdateProps($item, -1, $qty);
    }

    private function adjustInventoryAndUpdateProps(MedicationEntryItem $item, int $direction, int $quantity): void
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
        if ($item->expiration_date !== null) {
            $medication->expiration_date = $item->expiration_date;
        }
        if ($item->lot_number !== null) {
            $medication->lot_number = $item->lot_number;
        }
        if ($item->unit_price !== null) {
            $medication->unit_price = $item->unit_price;
        }
        $medication->save();

        $entry = $item->entry()->first();
        MedicationMovement::create([
            'medication_id' => $item->medication_id,
            'type' => $direction > 0 ? 'in' : 'out',
            'quantity' => $quantity,
            'balance' => (int) $medication->quantity,
            'reference_type' => $entry ? get_class($entry) : null,
            'reference_id' => $entry?->id,
            'notes' => $entry?->notes,
        ]);
    }
} 