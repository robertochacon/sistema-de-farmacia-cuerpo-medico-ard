<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only proceed if the table exists
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }

        // If the new column already exists, nothing to do
        if (Schema::hasColumn('medication_outputs', 'medication_ids')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('medication_outputs_new', function (Blueprint $table) {
                $table->id();
                $table->json('medication_ids')->nullable();
                $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->integer('quantity');
                $table->string('patient_type')->enum('military', 'civilian')->nullable();
                $table->text('reason')->nullable();
                $table->string('prescription_number')->nullable();
                $table->string('phone_photo_path')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            // Copy data, converting medication_id -> medication_ids JSON array
            // Use text concatenation to avoid requiring JSON1 extension
            DB::statement('
                INSERT INTO medication_outputs_new (
                    id, medication_ids, department_id, user_id, quantity,
                    patient_name, patient_id, reason, prescription_number, phone_photo_path, notes,
                    created_at, updated_at
                )
                SELECT
                    id,
                    CASE WHEN medication_id IS NOT NULL THEN "[" || medication_id || "]" ELSE NULL END as medication_ids,
                    department_id, user_id, quantity,
                    patient_name, patient_id, reason, prescription_number, phone_photo_path, notes,
                    created_at, updated_at
                FROM medication_outputs
            ');

            Schema::drop('medication_outputs');
            Schema::rename('medication_outputs_new', 'medication_outputs');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            // For other drivers, we can drop and add safely
            Schema::table('medication_outputs', function (Blueprint $table) {
                if (! Schema::hasColumn('medication_outputs', 'medication_ids')) {
                    $table->json('medication_ids')->nullable()->after('id');
                }
                if (Schema::hasColumn('medication_outputs', 'medication_id')) {
                    $table->dropForeign(['medication_id']);
                    $table->dropColumn('medication_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('medication_outputs')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('medication_outputs_old', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('medication_id');
                $table->foreign('medication_id')->references('id')->on('medications')->onDelete('cascade');
                $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->integer('quantity');
                $table->string('patient_name')->nullable();
                $table->string('patient_id')->nullable();
                $table->text('reason')->nullable();
                $table->string('prescription_number')->nullable();
                $table->string('phone_photo_path')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            // Restore with first medication id if present
            DB::statement('
                INSERT INTO medication_outputs_old (
                    id, medication_id, department_id, user_id, quantity,
                    patient_name, patient_id, reason, prescription_number, phone_photo_path, notes,
                    created_at, updated_at
                )
                SELECT
                    id,
                    CAST(
                        CASE
                            WHEN medication_ids IS NULL THEN NULL
                            ELSE json_extract(medication_ids, "$[0]")
                        END AS INTEGER
                    ) as medication_id,
                    department_id, user_id, quantity,
                    patient_name, patient_id, reason, prescription_number, phone_photo_path, notes,
                    created_at, updated_at
                FROM medication_outputs
            ');

            Schema::drop('medication_outputs');
            Schema::rename('medication_outputs_old', 'medication_outputs');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('medication_outputs', function (Blueprint $table) {
                if (Schema::hasColumn('medication_outputs', 'medication_ids')) {
                    $table->dropColumn('medication_ids');
                }
                if (! Schema::hasColumn('medication_outputs', 'medication_id')) {
                    $table->unsignedBigInteger('medication_id')->nullable();
                }
            });
        }
    }
}; 