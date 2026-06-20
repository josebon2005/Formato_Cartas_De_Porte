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
        if (! Schema::hasColumn('cartas_porte', 'consignatario_nombre')) {
            Schema::table('cartas_porte', function (Blueprint $table) {
                $table->string('consignatario_nombre')->nullable()->after('consignatario_id');
                $table->string('procedencia_nombre')->nullable()->after('procedencia_id');
                $table->string('piloto_nombre')->nullable()->after('piloto_id');
                $table->string('cabezal_placa')->nullable()->after('cabezal_id');
                $table->string('licencia_numero')->nullable()->after('licencia_id');
            });
        }

        DB::table('cartas_porte')
            ->update([
                'consignatario_nombre' => DB::raw('(select nombre from consignatarios where consignatarios.id = cartas_porte.consignatario_id)'),
                'procedencia_nombre' => DB::raw('(select nombre from procedencias where procedencias.id = cartas_porte.procedencia_id)'),
                'piloto_nombre' => DB::raw('(select nombre from pilotos where pilotos.id = cartas_porte.piloto_id)'),
                'cabezal_placa' => DB::raw('(select placa from cabezales where cabezales.id = cartas_porte.cabezal_id)'),
                'licencia_numero' => DB::raw('(select numero from licencias where licencias.id = cartas_porte.licencia_id)'),
            ]);

        Schema::table('cartas_porte', function (Blueprint $table) {
            $table->dropForeign(['consignatario_id']);
            $table->dropForeign(['procedencia_id']);
            $table->dropForeign(['piloto_id']);
            $table->dropForeign(['cabezal_id']);
            $table->dropForeign(['licencia_id']);
        });

        Schema::table('cartas_porte', function (Blueprint $table) {
            $table->foreignId('consignatario_id')->nullable()->change();
            $table->foreignId('procedencia_id')->nullable()->change();
            $table->foreignId('piloto_id')->nullable()->change();
            $table->foreignId('cabezal_id')->nullable()->change();
            $table->foreignId('licencia_id')->nullable()->change();
        });

        Schema::table('cartas_porte', function (Blueprint $table) {
            $table->foreign('consignatario_id')->references('id')->on('consignatarios')->nullOnDelete();
            $table->foreign('procedencia_id')->references('id')->on('procedencias')->nullOnDelete();
            $table->foreign('piloto_id')->references('id')->on('pilotos')->nullOnDelete();
            $table->foreign('cabezal_id')->references('id')->on('cabezales')->nullOnDelete();
            $table->foreign('licencia_id')->references('id')->on('licencias')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cartas_porte', function (Blueprint $table) {
            $table->dropForeign(['consignatario_id']);
            $table->dropForeign(['procedencia_id']);
            $table->dropForeign(['piloto_id']);
            $table->dropForeign(['cabezal_id']);
            $table->dropForeign(['licencia_id']);
        });

        Schema::table('cartas_porte', function (Blueprint $table) {
            $table->foreign('consignatario_id')->references('id')->on('consignatarios')->restrictOnDelete();
            $table->foreign('procedencia_id')->references('id')->on('procedencias')->restrictOnDelete();
            $table->foreign('piloto_id')->references('id')->on('pilotos')->restrictOnDelete();
            $table->foreign('cabezal_id')->references('id')->on('cabezales')->restrictOnDelete();
            $table->foreign('licencia_id')->references('id')->on('licencias')->restrictOnDelete();
        });

        if (Schema::hasColumn('cartas_porte', 'consignatario_nombre')) {
            Schema::table('cartas_porte', function (Blueprint $table) {
                $table->dropColumn([
                    'consignatario_nombre',
                    'procedencia_nombre',
                    'piloto_nombre',
                    'cabezal_placa',
                    'licencia_numero',
                ]);
            });
        }
    }
};
