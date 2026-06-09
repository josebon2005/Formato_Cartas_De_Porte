<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piloto extends Model
{
    protected $fillable = ['nombre', 'cabezal_id'];

    public function cartasPorte()
    {
        return $this->hasMany(CartaPorte::class);
    }

    public function licencias()
    {
        return $this->hasMany(Licencia::class);
    }

    public function cabezalUsual()
    {
        return $this->belongsTo(Cabezal::class, 'cabezal_id');
    }
}
