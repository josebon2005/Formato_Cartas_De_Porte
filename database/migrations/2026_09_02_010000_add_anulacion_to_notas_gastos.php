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
            }

            if (! Schema::hasColumn('notas_gastos', 'motivo_anulacion')) {
                $table->text('motivo_anulacion')->nullable()->after('fecha_anulacion');
            }
        });

        if (! $this->indexExists('notas_gastos', 'notas_gastos_bl_poliza_index')) {
            Schema::table('notas_gastos', function (Blueprint $table) {
                $table->index(['bl', 'poliza'], 'notas_gastos_bl_poliza_index');
            });
        }

        if ($this->indexExists('notas_gastos', 'notas_gastos_bl_poliza_unique')) {
            Schema::table('notas_gastos', function (Blueprint $table) {
                $table->dropUnique('notas_gastos_bl_poliza_unique');
            });
        }

        if (! $this->indexExists('carta_porte_nota_gasto', 'carta_porte_nota_gasto_carta_porte_id_index')) {
            Schema::table('carta_porte_nota_gasto', function (Blueprint $table) {
                $table->index('carta_porte_id', 'carta_porte_nota_gasto_carta_porte_id_index');
            });
        }

        if ($this->indexExists('carta_porte_nota_gasto', 'carta_porte_nota_gasto_carta_porte_id_unique')) {
            Schema::table('carta_porte_nota_gasto', function (Blueprint $table) {
                $table->dropUnique('carta_porte_nota_gasto_carta_porte_id_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->indexExists('notas_gastos', 'notas_gastos_bl_poliza_unique')) {
            Schema::table('notas_gastos', function (Blueprint $table) {
                $table->unique(['bl', 'poliza'], 'notas_gastos_bl_poliza_unique');
            });
        }

        if ($this->indexExists('notas_gastos', 'notas_gastos_bl_poliza_index')) {
            Schema::table('notas_gastos', function (Blueprint $table) {
                $table->dropIndex('notas_gastos_bl_poliza_index');
            });
        }

        Schema::table('notas_gastos', function (Blueprint $table) {
            $columns = collect(['fecha_anulacion', 'motivo_anulacion'])
                ->filter(fn (string $column) => Schema::hasColumn('notas_gastos', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        if (! $this->indexExists('carta_porte_nota_gasto', 'carta_porte_nota_gasto_carta_porte_id_unique')) {
            Schema::table('carta_porte_nota_gasto', function (Blueprint $table) {
                $table->unique('carta_porte_id', 'carta_porte_nota_gasto_carta_porte_id_unique');
            });
        }

        if ($this->indexExists('carta_porte_nota_gasto', 'carta_porte_nota_gasto_carta_porte_id_index')) {
            Schema::table('carta_porte_nota_gasto', function (Blueprint $table) {
                $table->dropIndex('carta_porte_nota_gasto_carta_porte_id_index');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notas_gastos MODIFY estado ENUM('BORRADOR', 'NOTA_GENERADA', 'FACTURADA') DEFAULT 'BORRADOR'");
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        if (DB::getDriverName() === 'mysql') {
            return DB::select(
                'SHOW INDEX FROM '.$this->wrapMysqlIdentifier($table).' WHERE Key_name = ?',
                [$index]
            ) !== [];
        }

        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('".$table."')"))
                ->contains(fn (object $row) => $row->name === $index);
        }

        if (method_exists(Schema::getFacadeRoot(), 'hasIndex')) {
            return Schema::hasIndex($table, $index);
        }

        return false;
    }

    private function wrapMysqlIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
};
