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

            return;
        }

        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->string('estado')->default('BORRADOR')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notas_gastos MODIFY estado ENUM('BORRADOR', 'NOTA_GENERADA', 'FACTURADA') DEFAULT 'BORRADOR'");

            return;
        }

        Schema::table('notas_gastos', function (Blueprint $table) {
            $table->enum('estado', ['BORRADOR', 'NOTA_GENERADA', 'FACTURADA'])->default('BORRADOR')->change();
        });
    }
};
