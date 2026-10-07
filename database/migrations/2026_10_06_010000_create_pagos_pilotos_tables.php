<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_pilotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piloto_id')->nullable()->constrained('pilotos')->nullOnDelete();
            $table->string('piloto_nombre');
            $table->string('cabezal_placa')->nullable();
            $table->string('licencia_numero')->nullable();
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->decimal('sueldo_base', 12, 2)->default(0);
            foreach (['total_viajes', 'total_ingresos', 'total_descuentos', 'total_pagar'] as $campo) {
                $table->decimal($campo, 16, 2)->default(0);
            }
            $table->enum('estado', ['BORRADOR', 'PAGADO', 'ANULADO'])->default('BORRADOR');
            // NULL libera el período al anular, sin eliminar su historial.
            $table->unsignedTinyInteger('periodo_activo')->nullable()->default(1);
            $table->dateTime('fecha_pago')->nullable();
            $table->dateTime('fecha_anulacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique(['piloto_id', 'mes', 'anio', 'periodo_activo'], 'pagos_pilotos_periodo_unico');
            $table->index(['anio', 'mes', 'estado']);
        });

        Schema::create('pago_piloto_viajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_piloto_id')->constrained('pagos_pilotos')->cascadeOnDelete();
            $table->foreignId('carta_porte_id')->nullable()->constrained('cartas_porte')->nullOnDelete();
            $table->date('fecha');
            $table->string('referencia')->nullable();
            $table->string('consignatario')->nullable();
            $table->string('destino')->nullable();
            $table->decimal('valor', 12, 2)->default(0);
            $table->boolean('es_manual')->default(true);
            $table->text('observacion')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('pago_piloto_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_piloto_id')->constrained('pagos_pilotos')->cascadeOnDelete();
            $table->string('concepto');
            $table->enum('tipo', ['SUMA', 'DESCUENTO']);
            $table->decimal('valor', 12, 2)->default(0);
            $table->boolean('aplicar')->default(true);
            $table->text('observacion')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('piloto_conceptos_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piloto_id')->constrained('pilotos')->cascadeOnDelete();
            $table->string('concepto');
            $table->enum('tipo', ['SUMA', 'DESCUENTO']);
            $table->decimal('valor_default', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->text('observacion')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piloto_conceptos_pago');
        Schema::dropIfExists('pago_piloto_movimientos');
        Schema::dropIfExists('pago_piloto_viajes');
        Schema::dropIfExists('pagos_pilotos');
    }
};
