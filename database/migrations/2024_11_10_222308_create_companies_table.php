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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->string('full_name', 150)->nullable();
            $table->string('short_name', 60)->nullable();
            $table->string('rnc', 11)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address', 50)->nullable();
            $table->text('logo')->nullable();
            $table->string('color', 50)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
