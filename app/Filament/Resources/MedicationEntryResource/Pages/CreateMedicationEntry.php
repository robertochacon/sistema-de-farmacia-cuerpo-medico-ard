<?php

namespace App\Filament\Resources\MedicationEntryResource\Pages;

use App\Filament\Resources\MedicationEntryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateMedicationEntry extends CreateRecord
{
    protected static string $resource = MedicationEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = collect($data['items'] ?? []);

        // Consolidate duplicate medications by summing quantities
        $grouped = $items
            ->filter(fn ($i) => ! empty($i['medication_id']))
            ->groupBy('medication_id')
            ->map(function ($rows, $medicationId) {
                $quantity = (int) $rows->sum(fn ($r) => (int) ($r['quantity'] ?? 0));
                $first = $rows->first();
                return [
                    'medication_id' => (int) $medicationId,
                    'quantity' => $quantity,
                    'unit_price' => $first['unit_price'] ?? null,
                    'expiration_date' => $first['expiration_date'] ?? null,
                    'lot_number' => $first['lot_number'] ?? null,
                ];
            })->values();

        $data['items'] = $grouped->all();
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $items = $this->record->items()->get();
        $count = $items->count();
        $sum = (int) $items->sum('quantity');
        Notification::make()
            ->success()
            ->title('Entrada registrada')
            ->body("Se registró la entrada con $count ítems y $sum unidades en total.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
} 