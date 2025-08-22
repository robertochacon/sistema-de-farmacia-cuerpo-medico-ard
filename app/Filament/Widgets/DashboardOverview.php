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
            ->withSum('items as total', 'quantity')
            ->get()
            ->sum('total');
        $todayCivilian = MedicationOutput::whereDate('created_at', today())
            ->where('patient_type', 'civilian')
            ->withSum('items as total', 'quantity')
            ->get()
            ->sum('total');
        $todayDepartmental = MedicationOutput::whereDate('created_at', today())
            ->where('patient_type', 'department')
            ->withSum('items as total', 'quantity')
            ->get()
            ->sum('total');

        return [
            Stat::make('Departamentos', $departments)
                ->description('Total de departamentos registrados')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success')
                ->url(route('filament.admin.resources.departments.index'))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Usuarios', $users)
                ->description('Total de usuarios registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->url(route('filament.admin.resources.users.index'))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Medicamentos', $medications)
                ->description('Total de medicamentos registrados')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success')
                ->url(route('filament.admin.resources.medications.index'))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Entradas', $entries)
                ->description('Registros de entrada')
                ->descriptionIcon('heroicon-m-arrow-up-right')
                ->color('primary')
                ->url(route('filament.admin.resources.medication-entries.index'))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Salidas', $outputs)
                ->description('Registros de salida')
                ->descriptionIcon('heroicon-m-arrow-down-left')
                ->color('warning')
                ->url(route('filament.admin.resources.medication-outputs.index'))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Bajo stock (<=5)', $lowStock)
                ->description('Medicamentos por agotarse')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->url(route('filament.admin.resources.medications.index'))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Salidas hoy (Militares)', $todayMilitary)
                ->description('Cantidad entregada a militares hoy')
                ->descriptionIcon('heroicon-m-arrow-down-left')
                ->color('info')
                ->url(route('filament.admin.resources.medication-outputs.index', ['table[filters][patient_type]' => 'military']))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Salidas hoy (Civiles)', $todayCivilian)
                ->description('Cantidad entregada a civiles hoy')
                ->descriptionIcon('heroicon-m-arrow-down-left')
                ->color('secondary')
                ->url(route('filament.admin.resources.medication-outputs.index', ['table[filters][patient_type]' => 'civilian']))
                ->extraAttributes(['class' => 'cursor-pointer']),
            Stat::make('Salidas hoy (Deptos.)', $todayDepartmental)
                ->description('Cantidad entregada a departamentos hoy')
                ->descriptionIcon('heroicon-m-arrow-down-left')
                ->color('success')
                ->url(route('filament.admin.resources.medication-outputs.index', ['table[filters][patient_type]' => 'department']))
                ->extraAttributes(['class' => 'cursor-pointer']),
        ];
    }

    // public static function canView(): bool
    // {
    //     return auth()->user()->isAdmin();
    // }
}
