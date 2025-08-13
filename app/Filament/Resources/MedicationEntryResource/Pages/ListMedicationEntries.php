<?php

namespace App\Filament\Resources\MedicationEntryResource\Pages;

use App\Filament\Resources\MedicationEntryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMedicationEntries extends ListRecords
{
    protected static string $resource = MedicationEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 