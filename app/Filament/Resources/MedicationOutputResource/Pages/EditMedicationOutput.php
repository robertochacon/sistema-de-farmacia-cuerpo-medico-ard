<?php

namespace App\Filament\Resources\MedicationOutputResource\Pages;

use App\Filament\Resources\MedicationOutputResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditMedicationOutput extends EditRecord
{
    protected static string $resource = MedicationOutputResource::class;

    /** @var array<int, array{id?:int, medication_id:int, quantity:int}> */
    protected array $itemsBuffer = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record
            ->items()
            ->get(['id', 'medication_id', 'quantity'])
            ->map(fn ($i) => [
                'id' => (int) $i->id,
                'medication_id' => (int) $i->medication_id,
                'quantity' => (int) $i->quantity,
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        $this->itemsBuffer = $items
            ->map(fn ($i) => [
                'id' => $i['id'] ?? null,
                'medication_id' => (int) ($i['medication_id'] ?? 0),
                'quantity' => (int) ($i['quantity'] ?? 0),
            ])
            ->filter(fn ($i) => $i['medication_id'] > 0 && $i['quantity'] > 0)
            ->values()
            ->all();
        unset($data['items']);

        // Resolve names from external IDs for denormalized storage
        if (($data['patient_type'] ?? null) === 'military') {
            if (empty($data['patient_external_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'patient_external_id' => 'La cédula del militar es obligatoria.',
                ]);
            }
        }

        if (! empty($data['patient_external_id'])) {
            $digits = preg_replace('/\D+/', '', (string) $data['patient_external_id']);
            if (strlen($digits) !== 11) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'patient_external_id' => 'Cédula inválida. Debe tener 11 dígitos.',
                ]);
            }
            $armada = app(\App\Services\ArmadaApi::class);
            $ard = app(\App\Services\ARD::class);
            $dir = app(\App\Services\MilitaryDirectory::class);
            $name = null;
            // 1) Armada API
            $p = $armada->getPerson($digits);
            $name = is_array($p) ? $armada->formatName($p) : null;
            if ($ard->isConfigured()) {
                $p = $name ? null : $ard->getPerson($digits);
                $name = $name ?: (is_array($p) ? $ard->formatName($p) : null);
            }
            if (! $name) {
                $name = $dir->getDisplayName($digits);
            }
            if ($name) {
                $data['patient_name'] = $name.' ('.$digits.')';
                $data['patient_external_id'] = $digits;
            }
            if (empty($data['patient_name'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'patient_name' => 'Debe escribir el nombre del militar si no se puede obtener automáticamente.',
                ]);
            }
        }

        if (! empty($data['doctor_external_id'])) {
            $digits = preg_replace('/\D+/', '', (string) $data['doctor_external_id']);
            if (strlen($digits) !== 11) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'doctor_external_id' => 'Cédula inválida. Debe tener 11 dígitos.',
                ]);
            }
            $armada = app(\App\Services\ArmadaApi::class);
            $ard = app(\App\Services\ARD::class);
            $dir = app(\App\Services\MilitaryDirectory::class);
            $name = null;
            // 1) Armada API
            $p = $armada->getPerson($digits);
            $name = is_array($p) ? $armada->formatName($p) : null;
            if ($ard->isConfigured()) {
                $p = $name ? null : $ard->getPerson($digits);
                $name = $name ?: (is_array($p) ? $ard->formatName($p) : null);
            }
            if (! $name) {
                $name = $dir->getDisplayName($digits);
            }
            if ($name) {
                $data['doctor_name'] = $name.' ('.$digits.')';
                $data['doctor_external_id'] = $digits;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        DB::transaction(function () {
            $existing = $this->record->items()->get(['id'])->pluck('id')->all();
            $incomingIds = collect($this->itemsBuffer)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            // Delete removed items (ensure model events fire)
            $toDelete = array_diff($existing, $incomingIds);
            if (! empty($toDelete)) {
                $this->record->items()
                    ->whereIn('id', $toDelete)
                    ->get()
                    ->each(function ($model) {
                        $model->delete();
                    });
            }

            // Upsert current items (ensure model events fire)
            foreach ($this->itemsBuffer as $item) {
                if (! empty($item['id'])) {
                    $model = $this->record->items()->whereKey($item['id'])->first();
                    if ($model) {
                        $model->medication_id = $item['medication_id'];
                        $model->quantity = $item['quantity'];
                        $model->save();
                    }
                } else {
                    $this->record->items()->create([
                        'medication_id' => $item['medication_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
        });

        $items = $this->record->items()->get();
        $count = $items->count();
        $sum = (int) $items->sum('quantity');
        Notification::make()
            ->success()
            ->title('Salida actualizada')
            ->body("Se actualizaron los ítems de la salida. Total: $count ítems, $sum unidades.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar')
                ->submit('save')
                ->keyBindings([]),
            \Filament\Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url(static::getResource()::getUrl('index'))
                ->color('secondary')
                ->outlined(),
        ];
    }
} 