<?php

namespace App\Observers;

use App\Models\MedicationEntry;
use App\Models\MedicationEntryItem;
use App\Models\Medication;
use App\Models\MedicationMovement;
use Illuminate\Support\Facades\DB;

class MedicationEntryObserver
{
    public function created(MedicationEntry $entry): void
    {
        $this->applyInventoryDelta($entry, +1);
    }

    public function deleted(MedicationEntry $entry): void
    {
        $this->applyInventoryDelta($entry, -1);
    }

    protected function applyInventoryDelta(MedicationEntry $entry, int $direction): void
    {
        $items = $entry->items()->get();

        foreach ($items as $item) {
            // Update inventory
            Medication::where('id', $item->medication_id)
                ->increment('quantity', $direction * $item->quantity);

            // Read updated balance
            $medication = Medication::find($item->medication_id);

            // Optionally update lot/expiration if provided
            if ($item->expiration_date !== null) {
                $medication->update(['expiration_date' => $item->expiration_date]);
            }
            if ($item->lot_number !== null) {
                $medication->update(['lot_number' => $item->lot_number]);
            }
            if ($item->unit_price !== null) {
                $medication->update(['unit_price' => $item->unit_price]);
            }

            // Log movement
            MedicationMovement::create([
                'medication_id' => $item->medication_id,
                'type' => $direction > 0 ? 'in' : 'out',
                'quantity' => $item->quantity,
                'balance' => $medication->quantity,
                'reference_type' => MedicationEntry::class,
                'reference_id' => $entry->id,
                'notes' => trim(collect([
                    $entry->notes,
                    $entry->document_number ? 'Doc: '.$entry->document_number : null,
                    $entry->received_at ? 'Recibido: '.\Illuminate\Support\Carbon::parse($entry->received_at)->format('d/m/Y') : null,
                ])->filter()->implode(' | ')),
            ]);
        }
    }
} 