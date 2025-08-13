<?php

namespace App\Filament\Resources\MedicationOutputResource\Pages;

use App\Filament\Resources\MedicationOutputResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateMedicationOutput extends CreateRecord
{
    protected static string $resource = MedicationOutputResource::class;

    /** @var array<int, array{medication_id:int, quantity:int}> */
    protected array $itemsBuffer = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = collect($data['items'] ?? []);
        $sum = (int) $items->sum(fn ($i) => (int) ($i['quantity'] ?? 0));
        if ($sum <= 0) {
            throw ValidationException::withMessages([
                'items' => 'La cantidad total debe ser mayor a 0.',
            ]);
        }

        $dupes = $items->pluck('medication_id')->filter()->duplicates();
        if ($dupes->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Hay medicamentos duplicados en la lista. Cada medicamento debe aparecer una sola vez.',
            ]);
        }

        // Buffer items and remove from payload so Filament doesn't try to auto-save the relationship
        $this->itemsBuffer = $items
            ->map(fn ($i) => [
                'medication_id' => (int) ($i['medication_id'] ?? 0),
                'quantity' => (int) ($i['quantity'] ?? 0),
            ])
            ->filter(fn ($i) => $i['medication_id'] > 0 && $i['quantity'] > 0)
            ->values()
            ->all();
        unset($data['items']);

        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            foreach ($this->itemsBuffer as $item) {
                $this->record->items()->create($item);
            }
        });

        $items = $this->record->items()->get();
        $count = $items->count();
        $sum = (int) $items->sum('quantity');
        Notification::make()
            ->success()
            ->title('Salida registrada')
            ->body("Se registró la salida con $count ítems y $sum unidades en total.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
} 