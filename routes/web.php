<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

// Alias para que el middleware 'auth' pueda redirigir a 'login'
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

// Rutas públicas (solo 'web') para poder imprimir en nueva pestaña sin exigir login
Route::middleware(['web'])->group(function () {
    Route::get('/tickets/outputs/{output}', [TicketController::class, 'showOutput'])->name('tickets.outputs.show');
    Route::get('/reports/outputs/pdf', [ReportController::class, 'outputsPdf'])->name('reports.outputs.pdf');
    Route::get('/reports/entries/pdf', [ReportController::class, 'entriesPdf'])->name('reports.entries.pdf');
});