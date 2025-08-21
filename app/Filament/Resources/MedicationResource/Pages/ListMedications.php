<?php

namespace App\Filament\Resources\MedicationResource\Pages;

use App\Filament\Resources\MedicationEntryResource;
use App\Filament\Resources\MedicationOutputResource;
use App\Filament\Resources\MedicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedications extends ListRecords
{
    protected static string $resource = MedicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('inventoryReportPdf')
                ->label('Exportar inventario (PDF)')
                ->url(route('reports.inventory.pdf'))
                ->icon('heroicon-o-arrow-down-tray')
                ->openUrlInNewTab(),
            Actions\Action::make('redirectToEntries')
                ->label('Entradas')
                ->url(MedicationEntryResource::getUrl('index'))
                ->icon('heroicon-o-arrow-up-right'),
            Actions\Action::make('redirectToOutputs')
                ->label('Salidas')
                ->url(MedicationOutputResource::getUrl('index'))
                ->icon('heroicon-o-arrow-down-right'),
            Actions\CreateAction::make(),
        ];
    }
}
