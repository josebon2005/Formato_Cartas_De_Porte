<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaGasto extends Model
{
    protected $table = 'notas_gastos';

    public const ESTADO_BORRADOR = 'BORRADOR';

    public const ESTADO_NOTA_GENERADA = 'NOTA_GENERADA';

    public const ESTADO_FACTURADA = 'FACTURADA';

    public const ESTADO_ANULADA = 'ANULADA';

    protected $fillable = [
        'fecha',
        'consignatario_id',
        'consignatario_nombre',
        'bl',
        'poliza',
        'procedencia_nombre',
        'destino',
        'cantidad_contenedores',
        'descripcion',
        'subtotal',
        'total',
        'estado',
        'fecha_anulacion',
        'motivo_anulacion',
        'fel_numero',
        'factura_fecha',
        'factura_serie',
        'factura_autorizacion',
        'factura_observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_anulacion' => 'datetime',
        'factura_fecha' => 'date',
        'cantidad_contenedores' => 'integer',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function consignatario()
    {
        return $this->belongsTo(Consignatario::class);
    }

    public function detalles()
    {
        return $this->hasMany(NotaGastoDetalle::class)->orderBy('orden')->orderBy('id');
    }

    public function cartasPorte()
    {
        return $this->belongsToMany(CartaPorte::class, 'carta_porte_nota_gasto')
            ->withPivot(['numero_correlativo', 'contenedor'])
            ->withTimestamps();
    }

    public function getEstaFacturadaAttribute(): bool
    {
        return $this->estado === self::ESTADO_FACTURADA;
    }

    public function getEstaAnuladaAttribute(): bool
    {
        return $this->estado === self::ESTADO_ANULADA;
    }
}
