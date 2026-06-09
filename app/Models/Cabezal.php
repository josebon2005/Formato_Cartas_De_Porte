<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabezal extends Model
{
    protected $table = 'cabezales';

    protected $fillable = ['placa', 'descripcion'];

    public function cartasPorte()
    {
        return $this->hasMany(CartaPorte::class);
    }
}
