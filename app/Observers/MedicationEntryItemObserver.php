<?php

namespace App\Observers;

use App\Models\MedicationEntryItem;
use App\Models\Medication;
use App\Models\MedicationMovement;
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
            // Revert old medication quantity
            $this->adjustInventoryAndUpdateProps((clone $item)->forceFill(['medication_id' => $originalMedicationId]), -1, $originalQuantity);
            // Apply new medication quantity
            $this->adjustInventoryAndUpdateProps($item, +1, $newQuantity);
            return;
        }

        $delta = $newQuantity - $originalQuantity;
        if ($delta !== 0) {
            $this->adjustInventoryAndUpdateProps($item, $delta > 0 ? +1 : -1, abs($delta));
        }
    }

    public function deleted(MedicationEntryItem $item): void
    {
        $this->adjustInventoryAndUpdateProps($item, -1, $item->quantity);
    }

    private function adjustInventoryAndUpdateProps(MedicationEntryItem $item, int $direction, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        Medication::where('id', $item->medication_id)
            ->increment('quantity', $direction * $quantity);

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