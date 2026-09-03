<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->dropUnique('notas_gastos_numero_correlativo_unique');
        });

        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->unsignedInteger('numero_correlativo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notas_gastos')
            ->whereNull('numero_correlativo')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($nota) {
                DB::table('notas_gastos')
                    ->where('id', $nota->id)
                    ->update(['numero_correlativo' => $nota->id]);
            });

        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->unsignedInteger('numero_correlativo')->nullable(false)->change();
        });

        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->unique('numero_correlativo', 'notas_gastos_numero_correlativo_unique');
        });
    }
};
