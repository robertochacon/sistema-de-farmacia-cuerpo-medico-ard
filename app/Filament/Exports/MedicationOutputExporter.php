<?php

namespace App\Filament\Exports;

use App\Models\MedicationOutput;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class MedicationOutputExporter extends Exporter
{
    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('#'),
            ExportColumn::make('created_at')->label('Fecha'),
            ExportColumn::make('department.name')->label('Departamento'),
            ExportColumn::make('patient_type')
                ->label('Tipo de paciente')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'military' => 'Militar',
                    'civilian' => 'Civil',
                    'department' => 'Departamento',
                    default => $state,
                }),
            ExportColumn::make('patient_name')->label('Nombre paciente'),
            ExportColumn::make('doctor_name')->label('Médico'),
            ExportColumn::make('reason')->label('Motivo'),
            ExportColumn::make('items_total')
                ->label('Cantidad total')
                ->state(fn (MedicationOutput $record): int => (int) $record->items()->sum('quantity')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Tu exportación de salidas ha finalizado.';
    }
}
