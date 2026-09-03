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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notas_gastos MODIFY estado ENUM('BORRADOR', 'NOTA_GENERADA', 'FACTURADA', 'ANULADA') DEFAULT 'BORRADOR'");
        }

        Schema::table('notas_gastos', function (Blueprint $table) {
            if (! Schema::hasColumn('notas_gastos', 'fecha_anulacion')) {
                $table->timestamp('fecha_anulacion')->nullable()->after('estado');
                $table->text('motivo_anulacion')->nullable()->after('fecha_anulacion');
            }
        });

        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->dropUnique('notas_gastos_bl_poliza_unique');
            $table->index(['bl', 'poliza']);
        });

        Schema::table('carta_porte_nota_gasto', function (Blueprint $table) {
            $table->dropUnique('carta_porte_nota_gasto_carta_porte_id_unique');
            $table->index('carta_porte_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->dropIndex('notas_gastos_bl_poliza_index');
            $table->unique(['bl', 'poliza']);
            $table->dropColumn(['fecha_anulacion', 'motivo_anulacion']);
        });

        Schema::table('carta_porte_nota_gasto', function (Blueprint $table) {
            $table->dropIndex('carta_porte_nota_gasto_carta_porte_id_index');
            $table->unique('carta_porte_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notas_gastos MODIFY estado ENUM('BORRADOR', 'NOTA_GENERADA', 'FACTURADA') DEFAULT 'BORRADOR'");
        }
    }
};
