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
        Schema::create('conceptos_gastos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('codigo')->nullable()->unique();
            $table->enum('tipo_calculo', ['fijo', 'por_contenedor'])->default('fijo');
            $table->enum('grupo', ['subtotal', 'adicional'])->default('subtotal');
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('tarifas_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignatario_id')->constrained('consignatarios')->cascadeOnDelete();
            $table->foreignId('concepto_gasto_id')->constrained('conceptos_gastos')->cascadeOnDelete();
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('cantidad_default', 10, 2)->nullable();
            $table->boolean('incluir_por_defecto')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['consignatario_id', 'concepto_gasto_id']);
        });

        Schema::create('notas_gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero_correlativo')->unique();
            $table->date('fecha');
            $table->foreignId('consignatario_id')->nullable()->constrained('consignatarios')->nullOnDelete();
            $table->string('consignatario_nombre')->nullable();
            $table->string('bl');
            $table->string('poliza');
            $table->string('procedencia_nombre')->nullable();
            $table->string('destino')->nullable();
            $table->unsignedInteger('cantidad_contenedores')->default(0);
            $table->text('descripcion')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado', ['BORRADOR', 'NOTA_GENERADA', 'FACTURADA'])->default('BORRADOR');
            $table->string('fel_numero')->nullable();
            $table->date('factura_fecha')->nullable();
            $table->string('factura_serie')->nullable();
            $table->string('factura_autorizacion')->nullable();
            $table->text('factura_observaciones')->nullable();
            $table->timestamps();

            $table->unique(['bl', 'poliza']);
            $table->index(['fecha', 'estado']);
            $table->index('fel_numero');
        });

        Schema::create('nota_gasto_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_gasto_id')->constrained('notas_gastos')->cascadeOnDelete();
            $table->foreignId('concepto_gasto_id')->nullable()->constrained('conceptos_gastos')->nullOnDelete();
            $table->string('concepto_nombre');
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('grupo', ['subtotal', 'adicional'])->default('subtotal');
            $table->boolean('incluido')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('carta_porte_nota_gasto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_gasto_id')->constrained('notas_gastos')->cascadeOnDelete();
            $table->foreignId('carta_porte_id')->constrained('cartas_porte')->restrictOnDelete();
            $table->unsignedInteger('numero_correlativo')->nullable();
            $table->string('contenedor')->nullable();
            $table->timestamps();

            $table->unique(['nota_gasto_id', 'carta_porte_id']);
            $table->unique('carta_porte_id');
        });

        DB::table('conceptos_gastos')->insert([
            [
                'nombre' => 'Flete',
                'codigo' => 'flete',
                'tipo_calculo' => 'por_contenedor',
                'grupo' => 'subtotal',
                'activo' => true,
                'orden' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Lavado',
                'codigo' => 'lavado',
                'tipo_calculo' => 'fijo',
                'grupo' => 'subtotal',
                'activo' => true,
                'orden' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Seguro',
                'codigo' => 'seguro',
                'tipo_calculo' => 'fijo',
                'grupo' => 'subtotal',
                'activo' => true,
                'orden' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Marchamo',
                'codigo' => 'marchamo',
                'tipo_calculo' => 'fijo',
                'grupo' => 'subtotal',
                'activo' => true,
                'orden' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Acomodamiento de contenedor',
                'codigo' => 'acomodamiento',
                'tipo_calculo' => 'fijo',
                'grupo' => 'subtotal',
                'activo' => true,
                'orden' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Revision',
                'codigo' => 'revision',
                'tipo_calculo' => 'fijo',
                'grupo' => 'subtotal',
                'activo' => true,
                'orden' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Montacarga',
                'codigo' => 'montacarga',
                'tipo_calculo' => 'fijo',
                'grupo' => 'adicional',
                'activo' => true,
                'orden' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carta_porte_nota_gasto');
        Schema::dropIfExists('nota_gasto_detalles');
        Schema::dropIfExists('notas_gastos');
        Schema::dropIfExists('tarifas_clientes');
        Schema::dropIfExists('conceptos_gastos');
    }
};
