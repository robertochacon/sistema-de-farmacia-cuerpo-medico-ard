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
            if (! Schema::hasColumn('medication_outputs', 'responsible_external_id')) {
                $table->string('responsible_external_id')->nullable()->after('doctor_name');
            }
            if (! Schema::hasColumn('medication_outputs', 'responsible_name')) {
                $table->string('responsible_name')->nullable()->after('responsible_external_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }
        Schema::table('medication_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('medication_outputs', 'responsible_name')) {
                $table->dropColumn('responsible_name');
            }
            if (Schema::hasColumn('medication_outputs', 'responsible_external_id')) {
                $table->dropColumn('responsible_external_id');
            }
        });
    }
};
