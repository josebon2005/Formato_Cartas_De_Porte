<?php

namespace App\Http\Controllers;

use App\Http\Requests\PagoPilotoRequest;
use App\Models\PagoPiloto;
use App\Models\Piloto;
use App\Services\PagoPilotoService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PagoPilotoController extends Controller
{
    public function __construct(private PagoPilotoService $pagos) {}

    public const MESES = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    public function index(Request $request)
    {
        $periodo = $this->periodo($request);
        $pilotos = Piloto::with([
            'cabezalUsual', 'licencias',
            'pagos' => fn ($query) => $query->where('mes', $periodo['mes'])->where('anio', $periodo['anio'])->where('periodo_activo', 1),
        ])->orderBy('nombre')->paginate(20)->withQueryString();

        return view('pagos_pilotos.index', $periodo + ['pilotos' => $pilotos, 'meses' => self::MESES]);
    }

    public function historial(Request $request)
    {
        $filtros = $request->validate([
            'piloto_id' => ['nullable', 'integer', 'min:1'],
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'anio' => ['nullable', 'integer', 'between:1900,9999'],
            'estado' => ['nullable', Rule::in(['BORRADOR', 'PAGADO', 'ANULADO'])],
        ]);
        $pagos = PagoPiloto::query();
        foreach ($filtros as $campo => $valor) {
            if ($valor !== null) {
                $pagos->where($campo, $valor);
            }
        }

        return view('pagos_pilotos.historial', [
            'pagos' => $pagos->orderByDesc('anio')->orderByDesc('mes')->orderByDesc('id')->paginate(20)->withQueryString(),
            'pilotos' => Piloto::orderBy('nombre')->get(['id', 'nombre']),
            'meses' => self::MESES,
            'estados' => ['BORRADOR' => 'Borrador', 'PAGADO' => 'Pagado', 'ANULADO' => 'Anulado'],
        ]);
    }

    public function create(Request $request)
    {
        $request->validate(['piloto_id' => ['required', 'integer', 'exists:pilotos,id']]);
        $periodo = $this->periodo($request);
        $piloto = Piloto::with(['cabezalUsual', 'licencias', 'conceptosPago'])->findOrFail($request->input('piloto_id'));
        $existente = $this->pagos->existente($piloto->id, $periodo['mes'], $periodo['anio']);
        if ($existente) {
            return $this->duplicado($existente);
        }
        $sueldo = $piloto->getAttribute('sueldo_base') ?? config('pagos_pilotos.sueldo_base_default', '3816.90');
        $viajes = $this->pagos->viajesIniciales($piloto, $periodo['mes'], $periodo['anio']);
        $movimientos = $this->pagos->movimientosIniciales($piloto);
        $pagoPiloto = new PagoPiloto($periodo + [
            'piloto_id' => $piloto->id, 'piloto_nombre' => $piloto->nombre,
            'cabezal_placa' => $piloto->cabezalUsual?->placa,
            'licencia_numero' => $piloto->licencias->sortBy('id')->first()?->numero,
            'sueldo_base' => $sueldo, 'estado' => PagoPiloto::ESTADO_BORRADOR,
        ] + $this->pagos->calcular($sueldo, $viajes, $movimientos));

        return view('pagos_pilotos.form', compact('pagoPiloto', 'piloto', 'viajes', 'movimientos') + ['meses' => self::MESES]);
    }

    public function store(PagoPilotoRequest $request)
    {
        $data = $request->validated();
        $existente = $this->pagos->existente((int) $data['piloto_id'], (int) $data['mes'], (int) $data['anio']);
        if ($existente) {
            return $this->duplicado($existente);
        }
        try {
            $pago = $this->pagos->guardar($data);
        } catch (UniqueConstraintViolationException|ValidationException $exception) {
            // También cubre dos formularios enviados simultáneamente.
            $existente = $this->pagos->existente((int) $data['piloto_id'], (int) $data['mes'], (int) $data['anio']);
            if ($existente) {
                return $this->duplicado($existente);
            }
            throw $exception;
        }

        return redirect()->route('pagos-pilotos.show', $pago)->with('status', 'Pago guardado como borrador.');
    }

    public function show(PagoPiloto $pagoPiloto)
    {
        $pagoPiloto->load(['viajes', 'movimientos']);

        return view('pagos_pilotos.show', ['pagoPiloto' => $pagoPiloto, 'meses' => self::MESES]);
    }

    public function edit(PagoPiloto $pagoPiloto)
    {
        if ($pagoPiloto->estado !== PagoPiloto::ESTADO_BORRADOR) {
            return redirect()->route('pagos-pilotos.show', $pagoPiloto)->with('error', 'Este pago se conserva como histórico y no puede editarse.');
        }
        $pagoPiloto->load(['viajes', 'movimientos', 'piloto']);

        return view('pagos_pilotos.form', [
            'pagoPiloto' => $pagoPiloto, 'piloto' => $pagoPiloto->piloto, 'meses' => self::MESES,
            'viajes' => $pagoPiloto->viajes->map(function ($viaje) {
                $datos = $viaje->toArray();
                $datos['fecha'] = $viaje->fecha->format('Y-m-d');

                return $datos;
            })->all(),
            'movimientos' => $pagoPiloto->movimientos->toArray(),
        ]);
    }

    public function update(PagoPilotoRequest $request, PagoPiloto $pagoPiloto)
    {
        $this->pagos->guardar($request->validated(), $pagoPiloto);

        return redirect()->route('pagos-pilotos.show', $pagoPiloto)->with('status', 'Pago actualizado correctamente.');
    }

    public function imprimir(PagoPiloto $pagoPiloto)
    {
        $pagoPiloto->load(['viajes', 'movimientos']);

        return view('pagos_pilotos.imprimir', ['pagoPiloto' => $pagoPiloto, 'meses' => self::MESES]);
    }

    public function pagar(PagoPiloto $pagoPiloto)
    {
        DB::transaction(function () use ($pagoPiloto) {
            $pago = PagoPiloto::whereKey($pagoPiloto->id)->lockForUpdate()->firstOrFail();
            $this->pagos->exigirBorrador($pago);
            $totales = $this->pagos->calcular($pago->sueldo_base, $pago->viajes->toArray(), $pago->movimientos->toArray());
            $pago->update($totales + ['estado' => PagoPiloto::ESTADO_PAGADO, 'fecha_pago' => now()]);
        });

        return redirect()->route('pagos-pilotos.show', $pagoPiloto)->with('status', 'Pago marcado como pagado y conservado como histórico.');
    }

    public function anular(PagoPiloto $pagoPiloto)
    {
        DB::transaction(function () use ($pagoPiloto) {
            $pago = PagoPiloto::whereKey($pagoPiloto->id)->lockForUpdate()->firstOrFail();
            if ($pago->estado !== PagoPiloto::ESTADO_ANULADO) {
                $pago->update(['estado' => PagoPiloto::ESTADO_ANULADO, 'periodo_activo' => null, 'fecha_anulacion' => now()]);
            }
        });

        return redirect()->route('pagos-pilotos.show', $pagoPiloto)->with('status', 'Pago anulado. Su información permanece en el historial.');
    }

    public function conceptos(Piloto $piloto)
    {
        return view('pagos_pilotos.conceptos', ['piloto' => $piloto, 'movimientos' => $this->pagos->movimientosIniciales($piloto)]);
    }

    public function guardarConceptos(Request $request, Piloto $piloto)
    {
        PagoPilotoRequest::normalizarColecciones($request, ['movimientos']);
        $data = $request->validate(PagoPilotoRequest::reglasMovimientos());
        DB::transaction(function () use ($piloto, $data) {
            Piloto::whereKey($piloto->id)->lockForUpdate()->firstOrFail();
            $piloto->conceptosPago()->delete();
            foreach (array_values($data['movimientos']) as $orden => $movimiento) {
                $piloto->conceptosPago()->create([
                    'concepto' => $movimiento['concepto'], 'tipo' => $movimiento['tipo'],
                    'valor_default' => $movimiento['valor'], 'activo' => $movimiento['aplicar'],
                    'observacion' => $movimiento['observacion'] ?? null, 'orden' => $orden,
                ]);
            }
        });

        return redirect()->route('pagos-pilotos.conceptos', $piloto)->with('status', 'Plantilla guardada. Se aplicará a los nuevos pagos de este piloto.');
    }

    private function periodo(Request $request): array
    {
        $data = $request->validate([
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'anio' => ['nullable', 'integer', 'between:1900,9999'],
        ]);

        $ahora = now(config('pagos_pilotos.timezone'));

        return ['mes' => (int) ($data['mes'] ?? $ahora->month), 'anio' => (int) ($data['anio'] ?? $ahora->year)];
    }

    private function duplicado(PagoPiloto $pago)
    {
        $mes = mb_strtolower(self::MESES[$pago->mes]);

        return redirect()->route('pagos-pilotos.show', $pago)
            ->with('error', "Ya existe un pago para este piloto correspondiente a {$mes} de {$pago->anio}.")
            ->with('pago_existente', $pago->id);
    }
}
