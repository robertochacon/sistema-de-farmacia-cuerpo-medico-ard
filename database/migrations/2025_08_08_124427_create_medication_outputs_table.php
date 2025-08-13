<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medication_outputs', function (Blueprint $table) {
            $table->id();
            // multiple medications per output stored as JSON array of medication IDs
            $table->json('medication_ids')->nullable();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade'); // departamento destino
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // usuario que realiza la salida
            $table->integer('quantity'); // cantidad total que sale
            $table->text('reason')->nullable(); // motivo de la salida
            $table->string('prescription_image')->nullable(); // receta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_outputs');
    }
};
