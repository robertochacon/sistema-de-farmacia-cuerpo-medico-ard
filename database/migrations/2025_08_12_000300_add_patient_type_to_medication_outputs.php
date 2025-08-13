<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }

        Schema::table('medication_outputs', function (Blueprint $table) {
            if (! Schema::hasColumn('medication_outputs', 'patient_type')) {
                $table->string('patient_type')->nullable()->after('patient_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }

        Schema::table('medication_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('medication_outputs', 'patient_type')) {
                $table->dropColumn('patient_type');
            }
        });
    }
}; 