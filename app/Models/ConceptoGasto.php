<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConceptoGasto extends Model
{
    protected $table = 'conceptos_gastos';

    protected $fillable = [
        'nombre',
        'codigo',
        'tipo_calculo',
        'grupo',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function tarifasClientes()
    {
        return $this->hasMany(TarifaCliente::class);
    }
}
