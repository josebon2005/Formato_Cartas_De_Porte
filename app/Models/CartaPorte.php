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
        'consignatario_nombre',
        'procedencia_id',
        'procedencia_nombre',
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
        'piloto_nombre',
        'cabezal_id',
        'cabezal_placa',
        'licencia_id',
        'licencia_numero',
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

    public function notasGastos()
    {
        return $this->belongsToMany(NotaGasto::class, 'carta_porte_nota_gasto')
            ->withPivot(['numero_correlativo', 'contenedor'])
            ->withTimestamps();
    }

    public function getConsignatarioTextoAttribute(): ?string
    {
        return $this->consignatario_nombre ?: $this->consignatario?->nombre;
    }

    public function getProcedenciaTextoAttribute(): ?string
    {
        return $this->procedencia_nombre ?: $this->procedencia?->nombre;
    }

    public function getPilotoTextoAttribute(): ?string
    {
        return $this->piloto_nombre ?: $this->piloto?->nombre;
    }

    public function getCabezalTextoAttribute(): ?string
    {
        return $this->cabezal_placa ?: $this->cabezal?->placa;
    }

    public function getLicenciaTextoAttribute(): ?string
    {
        return $this->licencia_numero ?: $this->licencia?->numero;
    }
}
