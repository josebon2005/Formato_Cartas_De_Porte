<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaCliente extends Model
{
    protected $table = 'tarifas_clientes';

    protected $fillable = [
        'consignatario_id',
        'concepto_gasto_id',
        'precio_unitario',
        'cantidad_default',
        'incluir_por_defecto',
        'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'cantidad_default' => 'decimal:2',
        'incluir_por_defecto' => 'boolean',
        'activo' => 'boolean',
    ];

    public function consignatario()
    {
        return $this->belongsTo(Consignatario::class);
    }

    public function conceptoGasto()
    {
        return $this->belongsTo(ConceptoGasto::class);
    }
}
