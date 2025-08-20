<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medication_entries', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('medication_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
