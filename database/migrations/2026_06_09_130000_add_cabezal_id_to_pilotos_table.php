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
        Schema::table('pilotos', function (Blueprint $table) {
            $table->foreignId('cabezal_id')
                ->nullable()
                ->after('nombre')
                ->constrained('cabezales')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilotos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cabezal_id');
        });
    }
};
