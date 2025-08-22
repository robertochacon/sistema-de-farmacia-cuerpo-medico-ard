<?php

namespace App\Filament\Resources\MedicationOutputResource\Pages;

use App\Filament\Resources\MedicationOutputResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListMedicationOutputs extends ListRecords
{
    protected static string $resource = MedicationOutputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('pdf_report')
                ->label('PDF')
                ->icon('heroicon-o-document')
                ->url(function () {
                    $filters = request()->input('table.filters', []);
                    $params = [];
                    if (!empty($filters['date']['from'] ?? null)) $params['from'] = $filters['date']['from'];
                    if (!empty($filters['date']['until'] ?? null)) $params['until'] = $filters['date']['until'];
                    if (!empty($filters['department']['department_id'] ?? null)) $params['department_id'] = $filters['department']['department_id'];
                    if (!empty($filters['patient_type'] ?? null)) $params['patient_type'] = $filters['patient_type'];
                    return route('reports.outputs.pdf', $params);
                })
                ->openUrlInNewTab()
                ->color('danger'),
            Actions\Action::make('daily_report')
                ->label('Reporte diario')
                ->icon('heroicon-o-calendar')
                ->url(fn () => static::getResource()::getUrl('index', ['table[filters][date][from]' => now()->toDateString(), 'table[filters][date][until]' => now()->toDateString()]))
                ->color('success'),
            Actions\Action::make('monthly_report')
                ->label('Reporte mensual')
                ->icon('heroicon-o-calendar-days')
                ->url(fn () => static::getResource()::getUrl('index', ['table[filters][date][from]' => now()->startOfMonth()->toDateString(), 'table[filters][date][until]' => now()->endOfMonth()->toDateString()]))
                ->color('info'),
            Actions\Action::make('annual_report')
                ->label('Reporte anual')
                ->icon('heroicon-o-chart-bar')
                ->url(fn () => static::getResource()::getUrl('index', ['table[filters][date][from]' => now()->startOfYear()->toDateString(), 'table[filters][date][until]' => now()->endOfYear()->toDateString()]))
                ->color('warning'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        return $query->where('user_id', Auth::id());
    }
} 