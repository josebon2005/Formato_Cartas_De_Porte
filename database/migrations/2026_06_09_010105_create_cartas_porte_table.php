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
        Schema::create('cartas_porte', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero_correlativo')->unique();
            $table->date('fecha');
            $table->foreignId('consignatario_id')->constrained('consignatarios')->restrictOnDelete();
            $table->foreignId('procedencia_id')->constrained('procedencias')->restrictOnDelete();
            $table->string('destino')->nullable();
            $table->string('poliza')->nullable();
            $table->string('id_documento')->nullable();
            $table->string('da')->nullable();
            $table->string('mi')->nullable();
            $table->string('contacto')->nullable();
            $table->string('telefono')->nullable();
            $table->string('contenedor')->nullable();
            $table->string('bultos')->nullable();
            $table->text('contenido')->nullable();
            $table->string('peso_kls')->nullable();
            $table->string('vapor')->nullable();
            $table->date('fecha_vapor')->nullable();
            $table->string('bl')->nullable();
            $table->foreignId('piloto_id')->constrained('pilotos')->restrictOnDelete();
            $table->foreignId('cabezal_id')->constrained('cabezales')->restrictOnDelete();
            $table->foreignId('licencia_id')->constrained('licencias')->restrictOnDelete();
            $table->timestamps();

            $table->index(['fecha', 'bl', 'poliza']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartas_porte');
    }
};
