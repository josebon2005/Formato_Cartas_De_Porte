<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartaPorte extends Model
{
    protected $table = 'cartas_porte';

    protected $fillable = [
        'numero_correlativo',
        'fecha',
        'consignatario_id',
        'procedencia_id',
        'destino',
        'poliza',
        'id_documento',
        'da',
        'mi',
        'contacto',
        'telefono',
        'contenedor',
        'bultos',
        'contenido',
        'peso_kls',
        'vapor',
        'fecha_vapor',
        'bl',
        'piloto_id',
        'cabezal_id',
        'licencia_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_vapor' => 'date',
    ];

    public function consignatario()
    {
        return $this->belongsTo(Consignatario::class);
    }

    public function procedencia()
    {
        return $this->belongsTo(Procedencia::class);
    }

    public function piloto()
    {
        return $this->belongsTo(Piloto::class);
    }

    public function cabezal()
    {
        return $this->belongsTo(Cabezal::class);
    }

    public function licencia()
    {
        return $this->belongsTo(Licencia::class);
    }
}
