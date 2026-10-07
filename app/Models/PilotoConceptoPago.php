<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PilotoConceptoPago extends Model
{
    protected $table = 'piloto_conceptos_pago';

    protected $guarded = ['id'];

    protected $casts = ['valor_default' => 'decimal:2', 'activo' => 'boolean'];

    public function piloto()
    {
        return $this->belongsTo(Piloto::class);
    }
}
