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
        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->index(['bl', 'poliza', 'estado', 'id'], 'notas_gastos_operacion_estado_id_index');
        });

        Schema::table('cartas_porte', function (Blueprint $table) {
            $table->index(['bl', 'poliza', 'fecha', 'id'], 'cartas_porte_operacion_fecha_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cartas_porte', function (Blueprint $table) {
            $table->dropIndex('cartas_porte_operacion_fecha_id_index');
        });

        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->dropIndex('notas_gastos_operacion_estado_id_index');
        });
    }
};
