<?php

namespace App\Filament\Widgets;

use App\Models\MedicationOutput;
use App\Models\Department;
use Filament\Widgets\ChartWidget;

class DailyOutputsByPatientTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Salidas diarias (Militares vs Civiles)';

    protected static ?int $sort = 1;
    
    protected function getFilters(): array
    {
        $filters = [
            '7|all' => '7 días - Todos',
            '30|all' => '30 días - Todos',
        ];

        Department::orderBy('name')->pluck('name', 'id')->each(function ($name, $id) use (&$filters) {
            $filters["7|{$id}"] = "7 días - {$name}";
            $filters["30|{$id}"] = "30 días - {$name}";
        });

        return $filters;
    }

    protected function getData(): array
    {
        [$range, $dept] = $this->parseFilter();

        $start = now()->startOfDay()->subDays($range - 1);
        $days = collect(range(0, $range - 1))->map(fn ($i) => $start->copy()->addDays($i));

        $military = [];
        $civilian = [];
        $labels = [];

        foreach ($days as $d) {
            $labels[] = $d->format('d/M');
            $military[] = MedicationOutput::when($dept !== 'all', fn ($q) => $q->where('department_id', $dept))
                ->whereDate('created_at', $d->toDateString())
                ->where('patient_type', 'military')
                ->sum('quantity');
            $civilian[] = MedicationOutput::when($dept !== 'all', fn ($q) => $q->where('department_id', $dept))
                ->whereDate('created_at', $d->toDateString())
                ->where('patient_type', 'civilian')
                ->sum('quantity');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Militares',
                    'data' => $military,
                    'backgroundColor' => 'rgba(59,130,246,0.5)',
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Civiles',
                    'data' => $civilian,
                    'backgroundColor' => 'rgba(107,114,128,0.5)',
                    'borderColor' => '#6b7280',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function parseFilter(): array
    {
        $filter = $this->filter ?? '7|all';
        if (! str_contains($filter, '|')) {
            return [7, 'all'];
        }
        [$range, $dept] = explode('|', $filter, 2);
        $range = in_array((int) $range, [7, 30], true) ? (int) $range : 7;
        return [$range, $dept ?: 'all'];
    }

    protected function getType(): string
    {
        return 'bar';
    }
} 