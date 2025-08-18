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

        // If it's a person (military/civilian), ensure department_id has a default value (e.g., Farmacia)
        if (in_array($data['patient_type'] ?? null, ['military', 'civilian'], true)) {
            if (empty($data['department_id'])) {
                $deptId = 
                    \App\Models\Department::where('code', 'FAR')->value('id') ??
                    \App\Models\Department::where('name', 'Farmacia')->value('id') ??
                    \App\Models\Department::orderBy('id')->value('id');
                if (! $deptId) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'department_id' => 'No hay departamento por defecto. Cree uno o seleccione manualmente.',
                    ]);
                }
                $data['department_id'] = (int) $deptId;
            }
        }
        if (($data['patient_type'] ?? null) === 'department') {
            $data['patient_external_id'] = null;
            $data['patient_name'] = null;
            $data['doctor_external_id'] = null;
            $data['doctor_name'] = null;
        }

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

    protected function getFormActions(): array
    {
        // Only show explicit save buttons; prevent implicit Enter submits
        return [
            \Filament\Actions\Action::make('create')
                ->label('Guardar')
                ->submit('create')
                ->keyBindings([]), // disable default Enter keybinding on action
            \Filament\Actions\Action::make('createAnother')
                ->label('Guardar y crear otro')
                ->submit('createAnother')
                ->color('gray')
                ->keyBindings([]),
            \Filament\Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url(static::getResource()::getUrl('index'))
                ->color('secondary')
                ->outlined(),
        ];
    }
} 