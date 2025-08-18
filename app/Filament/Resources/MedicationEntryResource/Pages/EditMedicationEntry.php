<?php

namespace App\Filament\Resources\MedicationEntryResource\Pages;

use App\Filament\Resources\MedicationEntryResource;
use Filament\Resources\Pages\EditRecord;

class EditMedicationEntry extends EditRecord
{
    protected static string $resource = MedicationEntryResource::class;

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