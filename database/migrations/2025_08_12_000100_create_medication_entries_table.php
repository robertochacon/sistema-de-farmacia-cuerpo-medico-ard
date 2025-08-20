<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('entry_type', ['donation', 'order', 'purchase']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('document_number')->nullable();
            $table->date('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_entries');
    }
}; 