<?php

namespace App\Http\Controllers;

use App\Models\MedicationOutput;
use App\Models\MedicationEntry;
use App\Models\Medication;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function outputsPdf(Request $request)
    {
        $query = MedicationOutput::query();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('until')) {
            $query->whereDate('created_at', '<=', $request->date('until'));
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }
        if ($request->filled('patient_type')) {
            $query->where('patient_type', $request->string('patient_type'));
        }

        $outputs = $query->with(['department:id,name', 'items.medication:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $pdf = Pdf::loadView('reports.outputs', [
            'outputs' => $outputs,
            'filters' => $request->only(['from','until','department_id','patient_type']),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('reporte_salidas_'.now()->format('Ymd_His').'.pdf');
    }

    public function entriesPdf(Request $request)
    {
        $query = MedicationEntry::query();

        if ($request->filled('from')) {
            $query->whereDate('received_at', '>=', $request->date('from'));
        }
        if ($request->filled('until')) {
            $query->whereDate('received_at', '<=', $request->date('until'));
        }
        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->string('entry_type'));
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->integer('organization_id'));
        }

        $entries = $query->with(['organization:id,name', 'items.medication:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $pdf = Pdf::loadView('reports.entries', [
            'entries' => $entries,
            'filters' => $request->only(['from','until','entry_type','organization_id']),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('reporte_entradas_'.now()->format('Ymd_His').'.pdf');
    }

    public function inventoryPdf(Request $request)
    {
        // Build base query with simple optional filters
        $query = Medication::query();
        if ($request->boolean('only_with_stock')) {
            $query->where('quantity', '>', 0);
        }
        if ($request->has('status')) {
            $query->where('status', $request->boolean('status'));
        }

        // Fetch only the required columns and pre-format data for the view/PDF
        $rows = $query
            ->orderBy('name')
            ->get(['id', 'name', 'quantity', 'expiration_date'])
            ->map(function (Medication $m): array {
                $expiration = '';
                try {
                    if ($m->expiration_date instanceof \Carbon\CarbonInterface) {
                        $expiration = $m->expiration_date->format('d/m/Y');
                    } elseif (! empty($m->expiration_date)) {
                        $expiration = \Illuminate\Support\Carbon::parse($m->expiration_date)->format('d/m/Y');
                    }
                } catch (\Throwable $e) {
                    $expiration = '';
                }
                return [
                    'id' => (int) $m->id,
                    'name' => (string) $m->name,
                    'quantity' => (int) ($m->quantity ?? 0),
                    'expiration' => $expiration,
                ];
            })
            ->all();

        $data = [
            'rows' => $rows,
            'generatedAt' => now(),
        ];

        try {
            $pdf = Pdf::loadView('reports.inventory', $data)->setPaper('a4', 'portrait');
            return $pdf->stream('reporte_inventario_'.now()->format('Ymd_His').'.pdf');
        } catch (\Throwable $e) {
            Log::error('Inventory PDF generation failed', ['message' => $e->getMessage()]);
            if ($request->boolean('debug') || config('app.debug')) {
                $data['errorMessage'] = $e->getMessage();
                $data['errorTrace'] = $e->getTraceAsString();
            }
            // Fallback to HTML view to avoid HTTP 500
            return response()->view('reports.inventory', $data, 200);
        }
    }
}
