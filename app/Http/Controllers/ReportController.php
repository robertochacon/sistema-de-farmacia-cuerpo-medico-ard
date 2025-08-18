<?php

namespace App\Http\Controllers;

use App\Models\MedicationOutput;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
}
