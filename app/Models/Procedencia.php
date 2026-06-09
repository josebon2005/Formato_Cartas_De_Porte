<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedencia extends Model
{
    protected $fillable = ['nombre'];

    public function cartasPorte()
    {
        return $this->hasMany(CartaPorte::class);
    }
}
