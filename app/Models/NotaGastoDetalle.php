<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaGastoDetalle extends Model
{
    protected $table = 'nota_gasto_detalles';

    protected $fillable = [
        'nota_gasto_id',
        'concepto_gasto_id',
        'concepto_nombre',
        'numero_factura',
        'precio_unitario',
        'cantidad',
        'total',
        'grupo',
        'incluido',
        'orden',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'cantidad' => 'decimal:2',
        'total' => 'decimal:2',
        'incluido' => 'boolean',
        'orden' => 'integer',
    ];

    public function notaGasto()
    {
        return $this->belongsTo(NotaGasto::class);
    }

    public function conceptoGasto()
    {
        return $this->belongsTo(ConceptoGasto::class);
    }
}
