<?php

namespace App\Filament\Widgets;

use App\Models\MedicationMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyMovementsChart extends ChartWidget
{
    protected static ?string $heading = 'Movimientos mensuales';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $months = collect(range(0, 11))->map(fn ($i) => $start->copy()->addMonths($i));

        $inCounts = [];
        $outCounts = [];
        $labels = [];

        foreach ($months as $m) {
            $labels[] = $m->format('M Y');
            $inCounts[] = MedicationMovement::where('type', 'in')
                ->whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->sum('quantity');
            $outCounts[] = MedicationMovement::where('type', 'out')
                ->whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->sum('quantity');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => $inCounts,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22,163,74,0.2)',
                ],
                [
                    'label' => 'Salidas',
                    'data' => $outCounts,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
} 