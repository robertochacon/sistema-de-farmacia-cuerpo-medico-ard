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
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nombre del medicamento
            $table->string('generic_name')->nullable(); // nombre genérico
            $table->string('presentation'); // presentación (tabletas, jarabe, etc.)
            $table->string('concentration')->nullable(); // concentración (500mg, 10ml, etc.)
            $table->string('manufacturer')->nullable(); // fabricante
            $table->string('lot_number')->nullable(); // número de lote
            $table->date('expiration_date')->nullable(); // fecha de vencimiento
            $table->integer('quantity')->nullable(); // cantidad disponible
            $table->decimal('unit_price', 10, 2)->nullable(); // precio unitario
            $table->enum('entry_type', ['donation', 'order', 'purchase']); // tipo de entrada: donación, pedido, compra
            $table->text('notes')->nullable(); // notas adicionales
            $table->boolean('status')->default(true); // si está activo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
