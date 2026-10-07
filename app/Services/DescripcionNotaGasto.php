<?php

namespace App\Services;

use App\Models\CartaPorte;
use App\Models\NotaGasto;
use Illuminate\Support\Collection;

class DescripcionNotaGasto
{
    public function guardada(NotaGasto $nota): string
    {
        // An intentionally empty description is different from an old NULL value.
        if ($nota->descripcion !== null) {
            return $nota->descripcion;
        }

        return $this->regenerar($nota);
    }

    public function regenerar(NotaGasto $nota): string
    {
        $cartas = $nota->cartasPorte()
            ->with(['procedencia', 'consignatario'])
            ->orderBy('cartas_porte.numero_correlativo')
            ->orderBy('cartas_porte.id')
            ->get();

        return $this->generar($cartas, $nota);
    }

    public function generar(Collection $cartas, ?NotaGasto $nota = null): string
    {
        $carta = $cartas->first();
        $cantidad = $cartas->isNotEmpty() ? $cartas->count() : ($nota?->cantidad_contenedores ?? 0);
        $procedencia = $cartas->first(fn (CartaPorte $item) => filled($item->procedencia_texto))?->procedencia_texto
            ?: ($nota?->procedencia_nombre ?: 'ORIGEN');
        $destino = $cartas->first(fn (CartaPorte $item) => filled($item->destino))?->destino
            ?: ($nota?->destino ?: 'DESTINO');
        $contenido = $cartas->first(fn (CartaPorte $item) => filled($item->contenido))?->contenido ?: 'CONTENIDO';
        $bl = $carta?->bl ?? $nota?->bl ?? '';
        $poliza = $carta?->poliza ?? $nota?->poliza ?? '';
        $contenedores = $cantidad === 1 ? 'contenedor' : 'contenedores';
        $numeros = $cartas->pluck('contenedor')
            ->map(fn ($numero) => trim((string) $numero))
            ->reject(fn (string $numero) => in_array(mb_strtoupper($numero), [
                '', 'N/A', 'NULL', 'SIN CONTENEDOR', '-', 'VACÍO', 'VACIO',
            ], true))
            ->unique(fn (string $numero) => mb_strtoupper($numero))
            ->implode(', ');
        $lista = $numeros !== '' ? ' No. '.$numeros : '';

        return "Valor flete {$procedencia} hacia {$destino} por {$cantidad} {$contenedores}{$lista} conteniendo {$contenido}, amparado con BL-{$bl} Póliza-{$poliza}.";
    }
}
