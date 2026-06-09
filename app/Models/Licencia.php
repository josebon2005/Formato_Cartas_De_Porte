<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Licencia extends Model
{
    protected $fillable = ['numero', 'piloto_id'];

    public function cartasPorte()
    {
        return $this->hasMany(CartaPorte::class);
    }

    public function piloto()
    {
        return $this->belongsTo(Piloto::class);
    }
}
