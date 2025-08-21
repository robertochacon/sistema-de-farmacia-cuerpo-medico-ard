<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medication;
use Illuminate\Support\Facades\DB;

class MedicationSeeder extends Seeder
{
    public function run(): void
    {
        // Eliminar medicamentos existentes (compatible con SQLite)
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            Medication::query()->delete();
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Medication::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Cargar dataset JSON limpio
        $jsonPath = database_path('seeders/medications_data.json');
        if (! is_file($jsonPath)) {
            $this->command?->error('No se encontró el archivo JSON: '.$jsonPath);
            return;
        }

        $dataset = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($dataset)) {
            $this->command?->error('El JSON no tiene un formato de arreglo válido.');
            return;
        }

        $created = 0;
        foreach ($dataset as $item) {
            $name = (string) ($item['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $quantity = $item['quantity'] ?? null;
            $data = [
                'name' => $name,
                'generic_name' => $item['generic_name'] ?? null,
                'presentation' => $item['presentation'] ?? 'Otro',
                'concentration' => $item['concentration'] ?? null,
                'manufacturer' => $item['manufacturer'] ?? null,
                'lot_number' => $item['lot_number'] ?? null,
                'expiration_date' => $quantity !== null ? ($item['expiration_date'] ?? null) : null,
                'quantity' => $quantity,
                'unit_price' => 0, // precios en 0 por requerimiento
                'entry_type' => $quantity === null ? 'order' : 'purchase',
                'notes' => $item['notes'] ?? null,
                'status' => array_key_exists('status', $item) ? (bool) $item['status'] : true,
            ];

            Medication::create($data);
            $created++;
        }
    }
}
