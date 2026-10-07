<?php

namespace App\Services;

use App\Models\CartaPorte;
use App\Models\PagoPiloto;
use App\Models\Piloto;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PagoPilotoService
{
    public function cartasDelPeriodo(Piloto $piloto, int $mes, int $anio): Builder
    {
        $inicio = CarbonImmutable::create($anio, $mes, 1)->startOfDay();

        return CartaPorte::query()
            ->where('fecha', '>=', $inicio->toDateString())
            ->where('fecha', '<', $inicio->addMonth()->toDateString())
            ->where(function ($query) use ($piloto) {
                $query->where('piloto_id', $piloto->id)
                    ->orWhere(fn ($legacy) => $legacy->whereNull('piloto_id')->where('piloto_nombre', $piloto->nombre));
            });
    }

    public function viajesIniciales(Piloto $piloto, int $mes, int $anio): array
    {
        return $this->cartasDelPeriodo($piloto, $mes, $anio)
            ->with('consignatario')->orderBy('fecha')->orderBy('id')->get()
            ->map(fn (CartaPorte $carta, int $orden) => [
                'carta_porte_id' => $carta->id,
                'fecha' => $carta->fecha->format('Y-m-d'),
                'referencia' => (string) $carta->numero_correlativo,
                'consignatario' => $carta->consignatario_texto,
                'destino' => $carta->destino,
                // tarifas_clientes son cobros al cliente, no remuneración del piloto.
                'valor' => '0.00', 'es_manual' => false, 'observacion' => null, 'orden' => $orden,
            ])->all();
    }

    public function movimientosIniciales(Piloto $piloto): array
    {
        return $piloto->conceptosPago->map(fn ($concepto) => [
            'concepto' => $concepto->concepto, 'tipo' => $concepto->tipo,
            'valor' => $concepto->valor_default, 'aplicar' => $concepto->activo,
            'observacion' => $concepto->observacion, 'orden' => $concepto->orden,
        ])->all();
    }

    public function existente(int $pilotoId, int $mes, int $anio): ?PagoPiloto
    {
        return PagoPiloto::where('piloto_id', $pilotoId)->where('mes', $mes)
            ->where('anio', $anio)->where('periodo_activo', 1)->first();
    }

    public function guardar(array $data, ?PagoPiloto $pago = null): PagoPiloto
    {
        return DB::transaction(function () use ($data, $pago) {
            if ($pago) {
                $pago = PagoPiloto::whereKey($pago->id)->lockForUpdate()->firstOrFail();
                $this->exigirBorrador($pago);
                $piloto = $pago->piloto;
            } else {
                $piloto = Piloto::with(['cabezalUsual', 'licencias'])->whereKey($data['piloto_id'])->lockForUpdate()->firstOrFail();
                if ($this->existente($piloto->id, (int) $data['mes'], (int) $data['anio'])) {
                    throw ValidationException::withMessages(['periodo' => 'Ya existe un pago para este piloto y período.']);
                }
                $pago = new PagoPiloto([
                    'piloto_id' => $piloto->id, 'piloto_nombre' => $piloto->nombre,
                    'cabezal_placa' => $piloto->cabezalUsual?->placa,
                    'licencia_numero' => $piloto->licencias->sortBy('id')->first()?->numero,
                    'mes' => $data['mes'], 'anio' => $data['anio'],
                    'estado' => PagoPiloto::ESTADO_BORRADOR, 'periodo_activo' => 1,
                ]);
            }

            $viajes = $this->validarOrigenViajes($data['viajes'] ?? [], $pago, $piloto);
            $movimientos = array_values($data['movimientos'] ?? []);
            $totales = $this->calcular($data['sueldo_base'], $viajes, $movimientos);
            $pago->fill($totales + [
                'sueldo_base' => self::decimal(self::centavos($data['sueldo_base'])),
                'observaciones' => $data['observaciones'] ?? null,
            ])->save();

            $pago->viajes()->delete();
            foreach ($viajes as $orden => $viaje) {
                unset($viaje['id']);
                $viaje['orden'] = $orden;
                $pago->viajes()->create($viaje);
            }
            $pago->movimientos()->delete();
            foreach ($movimientos as $orden => $movimiento) {
                $movimiento['orden'] = $orden;
                $pago->movimientos()->create($movimiento);
            }

            return $pago;
        });
    }

    private function validarOrigenViajes(array $viajes, PagoPiloto $pago, ?Piloto $piloto): array
    {
        $anteriores = $pago->exists ? $pago->viajes()->get()->keyBy('id') : collect();
        $idsPrevios = $anteriores->pluck('carta_porte_id')->filter()->all();
        $idsEnviados = collect($viajes)->pluck('carta_porte_id')->filter()->all();
        $idsValidos = $piloto && $idsEnviados
            ? $this->cartasDelPeriodo($piloto, $pago->mes, $pago->anio)->whereIn('id', $idsEnviados)->pluck('id')->all()
            : [];
        $permitidos = array_map('intval', array_merge($idsPrevios, $idsValidos));

        foreach ($viajes as $i => &$viaje) {
            $cartaId = empty($viaje['carta_porte_id']) ? null : (int) $viaje['carta_porte_id'];
            $anterior = $anteriores->get($viaje['id'] ?? null);
            if ($cartaId !== null && (! in_array($cartaId, $permitidos, true) || (bool) $viaje['es_manual'])) {
                throw ValidationException::withMessages(["viajes.$i.carta_porte_id" => 'La Carta de Porte no corresponde a este piloto y período.']);
            }
            if ($cartaId === null && ! (bool) $viaje['es_manual'] && ! ($anterior && ! $anterior->es_manual && $anterior->carta_porte_id === null)) {
                throw ValidationException::withMessages(["viajes.$i.carta_porte_id" => 'El viaje automático necesita una Carta de Porte válida.']);
            }
            $viaje['carta_porte_id'] = $cartaId;
            $viaje['es_manual'] = $cartaId === null && ! ($anterior && ! $anterior->es_manual);
        }
        unset($viaje);

        return array_values($viajes);
    }

    public function calcular(string|int|float $sueldo, array $viajes, array $movimientos): array
    {
        $totalViajes = 0;
        $ingresos = 0;
        $descuentos = 0;
        foreach ($viajes as $viaje) {
            $totalViajes += self::centavos($viaje['valor']);
        }
        foreach ($movimientos as $movimiento) {
            if (! ($movimiento['aplicar'] ?? false)) {
                continue;
            }
            if ($movimiento['tipo'] === 'SUMA') {
                $ingresos += self::centavos($movimiento['valor']);
            } else {
                $descuentos += self::centavos($movimiento['valor']);
            }
        }

        return [
            'total_viajes' => self::decimal($totalViajes),
            'total_ingresos' => self::decimal($ingresos),
            'total_descuentos' => self::decimal($descuentos),
            'total_pagar' => self::decimal($totalViajes + self::centavos($sueldo) + $ingresos - $descuentos),
        ];
    }

    public function exigirBorrador(PagoPiloto $pago): void
    {
        if ($pago->estado !== PagoPiloto::ESTADO_BORRADOR) {
            throw ValidationException::withMessages(['estado' => 'Solo los pagos en BORRADOR pueden editarse o marcarse como pagados.']);
        }
    }

    public static function centavos(string|int|float $valor): int
    {
        [$entero, $decimal] = array_pad(explode('.', (string) $valor, 2), 2, '');

        return ((int) $entero * 100) + (int) str_pad($decimal, 2, '0');
    }

    public static function decimal(int $centavos): string
    {
        $signo = $centavos < 0 ? '-' : '';
        $absoluto = abs($centavos);

        return $signo.intdiv($absoluto, 100).'.'.str_pad((string) ($absoluto % 100), 2, '0', STR_PAD_LEFT);
    }
}
