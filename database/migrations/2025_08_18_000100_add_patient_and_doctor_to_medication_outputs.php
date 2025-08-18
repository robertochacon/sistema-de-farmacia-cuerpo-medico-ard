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
            if (! Schema::hasColumn('medication_outputs', 'patient_external_id')) {
                $table->string('patient_external_id')->nullable()->after('patient_type');
            }
            if (! Schema::hasColumn('medication_outputs', 'patient_name')) {
                $table->string('patient_name')->nullable()->after('patient_external_id');
            }
            if (! Schema::hasColumn('medication_outputs', 'doctor_external_id')) {
                $table->string('doctor_external_id')->nullable()->after('patient_name');
            }
            if (! Schema::hasColumn('medication_outputs', 'doctor_name')) {
                $table->string('doctor_name')->nullable()->after('doctor_external_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }

        Schema::table('medication_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('medication_outputs', 'doctor_name')) {
                $table->dropColumn('doctor_name');
            }
            if (Schema::hasColumn('medication_outputs', 'doctor_external_id')) {
                $table->dropColumn('doctor_external_id');
            }
            if (Schema::hasColumn('medication_outputs', 'patient_name')) {
                $table->dropColumn('patient_name');
            }
            if (Schema::hasColumn('medication_outputs', 'patient_external_id')) {
                $table->dropColumn('patient_external_id');
            }
        });
    }
};
