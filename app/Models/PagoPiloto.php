<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPiloto extends Model
{
    protected $table = 'pagos_pilotos';

    public const ESTADO_BORRADOR = 'BORRADOR';

    public const ESTADO_PAGADO = 'PAGADO';

    public const ESTADO_ANULADO = 'ANULADO';

    protected $guarded = ['id'];

    protected $casts = [
        'mes' => 'integer', 'anio' => 'integer', 'periodo_activo' => 'integer',
        'sueldo_base' => 'decimal:2', 'total_viajes' => 'decimal:2',
        'total_ingresos' => 'decimal:2', 'total_descuentos' => 'decimal:2',
        'total_pagar' => 'decimal:2', 'fecha_pago' => 'datetime', 'fecha_anulacion' => 'datetime',
    ];

    public function piloto()
    {
        return $this->belongsTo(Piloto::class);
    }

    public function viajes()
    {
        return $this->hasMany(PagoPilotoViaje::class)->orderBy('orden')->orderBy('id');
    }

    public function movimientos()
    {
        return $this->hasMany(PagoPilotoMovimiento::class)->orderBy('orden')->orderBy('id');
    }

    public function getFechaPagoTextoAttribute(): ?string
    {
        return $this->fecha_pago?->copy()->timezone(config('pagos_pilotos.timezone'))->format('d-m-Y');
    }
}
