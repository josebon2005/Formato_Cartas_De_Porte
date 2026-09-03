<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consignatario extends Model
{
    protected $fillable = ['nombre'];

    public function cartasPorte()
    {
        return $this->hasMany(CartaPorte::class);
    }

    public function tarifasClientes()
    {
        return $this->hasMany(TarifaCliente::class);
    }

    public function notasGastos()
    {
        return $this->hasMany(NotaGasto::class);
    }
}
