<?php

namespace App\Http\Controllers;

use App\Models\CartaPorte;
use App\Models\ConceptoGasto;
use App\Models\NotaGasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NotaGastoController extends Controller
{
    public function index(Request $request)
    {
        $notas = NotaGasto::query()
            ->with('consignatario')
            ->when($request->filled('fecha_desde'), fn ($query) => $query->whereDate('fecha', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($query) => $query->whereDate('fecha', '<=', $request->fecha_hasta))
            ->when($request->filled('cliente'), function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $subQuery
                        ->where('consignatario_nombre', 'like', '%'.$request->cliente.'%')
                        ->orWhereHas('consignatario', fn ($catalogQuery) => $catalogQuery->where('nombre', 'like', '%'.$request->cliente.'%'));
                });
            })
            ->when($request->filled('bl'), fn ($query) => $query->where('bl', 'like', '%'.$request->bl.'%'))
            ->when($request->filled('poliza'), fn ($query) => $query->where('poliza', 'like', '%'.$request->poliza.'%'))
            ->when($request->filled('fel_numero'), fn ($query) => $query->where('fel_numero', 'like', '%'.$request->fel_numero.'%'))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->latest('fecha')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('facturacion.notas_gastos.index', [
            'notas' => $notas,
            'estados' => $this->estados(),
        ]);
    }

    public function desdeCarta(CartaPorte $cartaPorte)
    {
        if (! $this->hasOperacionCompleta($cartaPorte)) {
            return redirect()
                ->route('cartas-porte.show', $cartaPorte)
                ->with('error', 'La carta debe tener BL y poliza para generar una Nota de Gastos.');
        }

        $existente = $this->findNotaByOperacion($cartaPorte);

        if ($existente) {
            return redirect()
                ->route('facturacion.notas-gastos.show', $existente)
                ->with('error', 'Ya existe una Nota de Gastos para este BL y esta poliza.');
        }

        $cartas = $this->cartasOperacion($cartaPorte)->get();
        $detalles = $this->detallesDesdeCobros($cartas->count());

        return view('facturacion.notas_gastos.preview', [
            'cartaPorte' => $cartaPorte,
            'cartas' => $cartas,
            'detalles' => $detalles,
            'descripcion' => $this->descripcionOperacion($cartas),
        ]);
    }

    public function storeDesdeCarta(Request $request, CartaPorte $cartaPorte)
    {
        if (! $this->hasOperacionCompleta($cartaPorte)) {
            return redirect()
                ->route('cartas-porte.show', $cartaPorte)
                ->with('error', 'La carta debe tener BL y poliza para generar una Nota de Gastos.');
        }

        $existente = $this->findNotaByOperacion($cartaPorte);

        if ($existente) {
            return redirect()
                ->route('facturacion.notas-gastos.show', $existente)
                ->with('error', 'Ya existe una Nota de Gastos para este BL y esta poliza.');
        }

        $validated = $this->validatedNotaData($request);
        $cartas = $this->cartasOperacion($cartaPorte)->get();
        $totales = $this->calcularTotales($validated['detalles']);

        $nota = DB::transaction(function () use ($cartaPorte, $cartas, $validated, $totales) {
            $nota = NotaGasto::create([
                'fecha' => now()->toDateString(),
                'consignatario_id' => $cartaPorte->consignatario_id,
                'consignatario_nombre' => $cartaPorte->consignatario_texto,
                'bl' => $cartaPorte->bl,
                'poliza' => $cartaPorte->poliza,
                'procedencia_nombre' => $cartaPorte->procedencia_texto,
                'destino' => $cartaPorte->destino,
                'cantidad_contenedores' => $cartas->count(),
                'descripcion' => $this->descripcionOperacion($cartas),
                'subtotal' => $totales['subtotal'],
                'total' => $totales['total'],
                'estado' => NotaGasto::ESTADO_NOTA_GENERADA,
            ]);

            $this->guardarDetalles($nota, $validated['detalles']);
            $this->vincularCartas($nota, $cartas);

            return $nota;
        });

        return redirect()
            ->route('facturacion.notas-gastos.show', $nota)
            ->with('status', 'Nota de Gastos generada correctamente.');
    }

    public function show(NotaGasto $notaGasto)
    {
        $notaGasto->load(['detalles', 'cartasPorte', 'consignatario']);

        return view('facturacion.notas_gastos.show', compact('notaGasto'));
    }

    public function edit(NotaGasto $notaGasto)
    {
        $notaGasto->load(['detalles', 'cartasPorte', 'consignatario']);

        return view('facturacion.notas_gastos.edit', compact('notaGasto'));
    }

    public function update(Request $request, NotaGasto $notaGasto)
    {
        $validated = $this->validatedNotaData($request);
        $totales = $this->calcularTotales($validated['detalles']);

        DB::transaction(function () use ($notaGasto, $validated, $totales) {
            $notaGasto->update([
                'descripcion' => $validated['descripcion'] ?? null,
                'subtotal' => $totales['subtotal'],
                'total' => $totales['total'],
            ]);

            $notaGasto->detalles()->delete();
            $this->guardarDetalles($notaGasto, $validated['detalles']);
        });

        return redirect()
            ->route('facturacion.notas-gastos.show', $notaGasto)
            ->with('status', 'Nota de Gastos actualizada correctamente.');
    }

    public function destroy(NotaGasto $notaGasto)
    {
        if ($notaGasto->esta_facturada) {
            return redirect()
                ->route('facturacion.notas-gastos.index')
                ->with('error', 'La Nota de Gastos esta FACTURADA. Use la opcion Anular Nota si la factura correspondiente fue anulada.');
        }

        $status = $notaGasto->esta_anulada
            ? 'Nota de Gastos anulada eliminada permanentemente.'
            : 'Nota de Gastos eliminada correctamente.';

        DB::transaction(function () use ($notaGasto) {
            $notaGasto->cartasPorte()->detach();
            $notaGasto->detalles()->delete();
            $notaGasto->delete();
        });

        return redirect()
            ->route('facturacion.notas-gastos.index')
            ->with('status', $status);
    }

    public function anular(Request $request, NotaGasto $notaGasto)
    {
        if (! $notaGasto->esta_facturada) {
            return redirect()
                ->route('facturacion.notas-gastos.show', $notaGasto)
                ->with('error', 'Solo se pueden anular Notas de Gastos que esten en estado FACTURADA.');
        }

        $validated = $request->validate([
            'motivo_anulacion' => ['nullable', 'string'],
        ]);

        $notaGasto->update([
            'estado' => NotaGasto::ESTADO_ANULADA,
            'fecha_anulacion' => now(),
            'motivo_anulacion' => $validated['motivo_anulacion'] ?? null,
        ]);

        return redirect()
            ->route('facturacion.notas-gastos.show', $notaGasto)
            ->with('status', 'Nota de Gastos anulada correctamente.');
    }

    public function imprimir(Request $request, NotaGasto $notaGasto)
    {
        if (! $notaGasto->esta_facturada || blank($notaGasto->fel_numero)) {
            return redirect()
                ->route('facturacion.notas-gastos.show', $notaGasto)
                ->with('error', 'Debe agregar el numero de factura SAT antes de imprimir la Nota de Gastos.');
        }

        $validated = $request->validate([
            'copias' => ['nullable', 'integer', Rule::in([1, 2])],
        ]);
        $copias = (int) ($validated['copias'] ?? 1);

        $notaGasto->load(['detalles', 'cartasPorte', 'consignatario']);

        return view('facturacion.notas_gastos.print', compact('notaGasto', 'copias'));
    }

    public function editFacturacion(NotaGasto $notaGasto)
    {
        if ($notaGasto->esta_anulada) {
            return redirect()
                ->route('facturacion.notas-gastos.show', $notaGasto)
                ->with('error', 'La Nota de Gastos esta ANULADA y se conserva como historial.');
        }

        return view('facturacion.notas_gastos.facturar', compact('notaGasto'));
    }

    public function updateFacturacion(Request $request, NotaGasto $notaGasto)
    {
        $validated = $request->validate([
            'fel_numero' => ['required', 'string', 'max:255'],
        ]);

        $notaGasto->update([
            'fel_numero' => $validated['fel_numero'],
            'factura_fecha' => $notaGasto->factura_fecha ?: now()->toDateString(),
            'estado' => NotaGasto::ESTADO_FACTURADA,
        ]);

        return redirect()
            ->route('facturacion.notas-gastos.show', $notaGasto)
            ->with('status', 'Numero de factura SAT guardado correctamente.');
    }

    private function hasOperacionCompleta(CartaPorte $cartaPorte): bool
    {
        return filled($cartaPorte->bl) && filled($cartaPorte->poliza);
    }

    private function findNotaByOperacion(CartaPorte $cartaPorte): ?NotaGasto
    {
        return NotaGasto::where('bl', $cartaPorte->bl)
            ->where('poliza', $cartaPorte->poliza)
            ->where('estado', '<>', NotaGasto::ESTADO_ANULADA)
            ->first();
    }

    private function cartasOperacion(CartaPorte $cartaPorte)
    {
        return CartaPorte::query()
            ->with(['consignatario', 'procedencia'])
            ->where('bl', $cartaPorte->bl)
            ->where('poliza', $cartaPorte->poliza)
            ->orderBy('numero_correlativo');
    }

    private function detallesDesdeCobros(int $cantidadContenedores): array
    {
        return ConceptoGasto::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(function ($concepto) use ($cantidadContenedores) {
                $cantidad = $concepto->tipo_calculo === 'por_contenedor'
                    ? $cantidadContenedores
                    : 1;

                return [
                    'concepto_gasto_id' => $concepto->id,
                    'concepto_nombre' => $concepto->nombre,
                    'numero_factura' => null,
                    'precio_unitario' => 0,
                    'cantidad' => (float) $cantidad,
                    'grupo' => $concepto->grupo,
                    'incluido' => false,
                    'orden' => $concepto->orden,
                ];
            })
            ->all();
    }

    private function descripcionOperacion($cartas): string
    {
        $cantidadContenedores = $cartas->count();
        $cartaPorte = $cartas->first();
        $procedencia = $cartas->first(fn (CartaPorte $carta) => filled($carta->procedencia_texto))?->procedencia_texto ?: 'ORIGEN';
        $destino = $cartas->first(fn (CartaPorte $carta) => filled($carta->destino))?->destino ?: 'DESTINO';
        $contenido = $cartas->first(fn (CartaPorte $carta) => filled($carta->contenido))?->contenido ?: 'CONTENIDO';
        $contenedores = $cantidadContenedores === 1 ? 'contenedor' : 'contenedores';

        return "Valor flete {$procedencia} hacia {$destino} por {$cantidadContenedores} {$contenedores} conteniendo {$contenido}, amparado con BL-{$cartaPorte->bl} Póliza-{$cartaPorte->poliza}.";
    }

    private function validatedNotaData(Request $request): array
    {
        return $request->validate([
            'descripcion' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.concepto_gasto_id' => ['nullable', 'exists:conceptos_gastos,id'],
            'detalles.*.concepto_nombre' => ['required', 'string', 'max:255'],
            'detalles.*.numero_factura' => ['nullable', 'string', 'max:255'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0'],
            'detalles.*.grupo' => ['required', Rule::in(['subtotal', 'adicional'])],
            'detalles.*.incluido' => ['nullable', 'boolean'],
            'detalles.*.orden' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function calcularTotales(array $detalles): array
    {
        $subtotal = 0;
        $adicional = 0;

        foreach ($detalles as $detalle) {
            if (! (bool) ($detalle['incluido'] ?? false)) {
                continue;
            }

            $total = round(((float) $detalle['precio_unitario']) * ((float) $detalle['cantidad']), 2);

            if ($detalle['grupo'] === 'subtotal') {
                $subtotal += $total;
            } else {
                $adicional += $total;
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal + $adicional, 2),
        ];
    }

    private function guardarDetalles(NotaGasto $nota, array $detalles): void
    {
        foreach ($detalles as $index => $detalle) {
            $incluido = (bool) ($detalle['incluido'] ?? false);
            $total = $incluido
                ? round(((float) $detalle['precio_unitario']) * ((float) $detalle['cantidad']), 2)
                : 0;

            $nota->detalles()->create([
                'concepto_gasto_id' => $detalle['concepto_gasto_id'] ?? null,
                'concepto_nombre' => $detalle['concepto_nombre'],
                'numero_factura' => $this->cleanText($detalle['numero_factura'] ?? null),
                'precio_unitario' => $detalle['precio_unitario'],
                'cantidad' => $detalle['cantidad'],
                'total' => $total,
                'grupo' => $detalle['grupo'],
                'incluido' => $incluido,
                'orden' => $detalle['orden'] ?? $index,
            ]);
        }
    }

    private function vincularCartas(NotaGasto $nota, $cartas): void
    {
        $syncData = $cartas->mapWithKeys(function (CartaPorte $carta) {
            return [
                $carta->id => [
                    'numero_correlativo' => $carta->numero_correlativo,
                    'contenedor' => $carta->contenedor,
                ],
            ];
        })->all();

        $nota->cartasPorte()->sync($syncData);
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function estados(): array
    {
        return [
            NotaGasto::ESTADO_BORRADOR => 'BORRADOR',
            NotaGasto::ESTADO_NOTA_GENERADA => 'NOTA GENERADA',
            NotaGasto::ESTADO_FACTURADA => 'FACTURADA',
            NotaGasto::ESTADO_ANULADA => 'ANULADA',
        ];
    }
}
