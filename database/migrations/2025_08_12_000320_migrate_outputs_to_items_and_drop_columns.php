<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }

        $legacy = DB::table('medication_outputs')
            ->select('id', 'medication_ids', 'quantity')
            ->whereNotNull('medication_ids')
            ->get();

        foreach ($legacy as $row) {
            $exists = DB::table('medication_output_items')->where('output_id', $row->id)->exists();
            if ($exists) {
                continue;
            }

            $decoded = [];
            if (is_string($row->medication_ids)) {
                $decoded = json_decode($row->medication_ids, true) ?: [];
            } elseif (is_array($row->medication_ids)) {
                $decoded = $row->medication_ids;
            }

            $qty = (int) ($row->quantity ?? 0);
            if ($qty <= 0) {
                $qty = 1;
            }

            foreach ($decoded as $medId) {
                DB::table('medication_output_items')->insert([
                    'output_id' => $row->id,
                    'medication_id' => (int) $medId,
                    'quantity' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('medication_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('medication_outputs', 'medication_ids')) {
                $table->dropColumn('medication_ids');
            }
            if (Schema::hasColumn('medication_outputs', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('medication_outputs', 'patient_name')) {
                $table->dropColumn('patient_name');
            }
            if (Schema::hasColumn('medication_outputs', 'patient_id')) {
                $table->dropColumn('patient_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }

        Schema::table('medication_outputs', function (Blueprint $table) {
            if (! Schema::hasColumn('medication_outputs', 'medication_ids')) {
                $table->json('medication_ids')->nullable();
            }
            if (! Schema::hasColumn('medication_outputs', 'quantity')) {
                $table->integer('quantity')->nullable();
            }
            if (! Schema::hasColumn('medication_outputs', 'patient_name')) {
                $table->string('patient_name')->nullable();
            }
            if (! Schema::hasColumn('medication_outputs', 'patient_id')) {
                $table->string('patient_id')->nullable();
            }
        });
    }
}; 