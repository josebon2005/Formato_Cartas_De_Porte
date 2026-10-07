<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPilotoViaje extends Model
{
    protected $table = 'pago_piloto_viajes';

    protected $guarded = ['id'];

    protected $casts = ['fecha' => 'date', 'valor' => 'decimal:2', 'es_manual' => 'boolean'];

    public function pago()
    {
        return $this->belongsTo(PagoPiloto::class, 'pago_piloto_id');
    }

    public function cartaPorte()
    {
        return $this->belongsTo(CartaPorte::class);
    }
}
