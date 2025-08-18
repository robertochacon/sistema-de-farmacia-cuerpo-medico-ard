<?php

namespace App\Filament\Exports;

use App\Models\MedicationMovement;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class MedicationMovementExporter extends Exporter
{
    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')->label('Fecha'),
            ExportColumn::make('medication.name')->label('Medicamento'),
            ExportColumn::make('type')
                ->label('Tipo')
                ->formatStateUsing(fn (string $state) => $state === 'in' ? 'Entrada' : 'Salida'),
            ExportColumn::make('quantity')->label('Cantidad'),
            ExportColumn::make('balance')->label('Saldo'),
            ExportColumn::make('notes')->label('Notas'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Tu exportación de movimientos ha finalizado.';
    }
}
