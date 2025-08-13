<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Department;
use App\Models\Medication;
use App\Models\MedicationEntry;
use App\Models\MedicationOutput;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $users = User::count();
        $departments = Department::count();
        $medications = Medication::count();
        $entries = MedicationEntry::count();
        $outputs = MedicationOutput::count();
        $lowStock = Medication::where('quantity', '<=', 5)->count();

        $todayMilitary = MedicationOutput::whereDate('created_at', today())
            ->where('patient_type', 'military')
            ->sum('quantity');
        $todayCivilian = MedicationOutput::whereDate('created_at', today())
            ->where('patient_type', 'civilian')
            ->sum('quantity');

        return [
            Stat::make('Departamentos', $departments)
                ->description('Total de departamentos registrados')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
            Stat::make('Usuarios', $users)
                ->description('Total de usuarios registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Medicamentos', $medications)
                ->description('Total de medicamentos registrados')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),
            Stat::make('Entradas', $entries)
                ->description('Registros de entrada')
                ->descriptionIcon('heroicon-m-arrow-up-right')
                ->color('primary'),
            Stat::make('Salidas', $outputs)
                ->description('Registros de salida')
                ->descriptionIcon('heroicon-m-arrow-down-left')
                ->color('warning'),
            Stat::make('Bajo stock (<=5)', $lowStock)
                ->description('Medicamentos por agotarse')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make('Salidas hoy (Militares)', $todayMilitary)
                ->description('Cantidad entregada a militares hoy')
                ->descriptionIcon('heroicon-m-arrow-down-left')
                ->color('info'),
            Stat::make('Salidas hoy (Civiles)', $todayCivilian)
                ->description('Cantidad entregada a civiles hoy')
                ->descriptionIcon('heroicon-m-arrow-down-left')
                ->color('secondary'),
        ];
    }

    // public static function canView(): bool
    // {
    //     return auth()->user()->isAdmin();
    // }
}
