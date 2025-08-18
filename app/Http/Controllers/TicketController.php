<?php

namespace App\Http\Controllers;

use App\Models\MedicationOutput;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function showOutput(MedicationOutput $output)
    {
        $output->load(['items.medication:id,name', 'department:id,name,code', 'user:id,name,email']);
        return view('tickets.output', [
            'output' => $output,
        ]);
    }
}
