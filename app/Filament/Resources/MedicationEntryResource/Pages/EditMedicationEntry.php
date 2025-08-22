<?php

namespace App\Filament\Resources\MedicationEntryResource\Pages;

use App\Filament\Resources\MedicationEntryResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditMedicationEntry extends EditRecord
{
    protected static string $resource = MedicationEntryResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(Gate::allows('update', $this->getRecord()), 403);
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