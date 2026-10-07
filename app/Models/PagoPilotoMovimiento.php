<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPilotoMovimiento extends Model
{
    protected $table = 'pago_piloto_movimientos';

    protected $guarded = ['id'];

    protected $casts = ['valor' => 'decimal:2', 'aplicar' => 'boolean'];

    public function pago()
    {
        return $this->belongsTo(PagoPiloto::class, 'pago_piloto_id');
    }
}
