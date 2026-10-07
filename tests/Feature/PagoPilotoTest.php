<?php

namespace Tests\Feature;

use App\Models\CartaPorte;
use App\Models\PagoPiloto;
use App\Models\PagoPilotoMovimiento;
use App\Models\PagoPilotoViaje;
use App\Models\Piloto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class PagoPilotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_module_requires_authentication(): void
    {
        $this->get(route('pagos-pilotos.index'))->assertRedirect(route('login'));
        $this->post(route('pagos-pilotos.store'), [])->assertRedirect(route('login'));
    }

    public function test_default_period_and_payment_date_follow_guatemala_at_utc_month_boundary(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->travelTo(Carbon::parse('2026-10-01 01:00:00', 'UTC'));
        try {
            $this->get(route('pagos-pilotos.index'))->assertOk()
                ->assertViewHas('mes', 9)->assertViewHas('anio', 2026);
            $this->get(route('pagos-pilotos.create', ['piloto_id' => $piloto->id]))
                ->assertOk()->assertViewHas('pagoPiloto', fn ($pago) => $pago->mes === 9 && $pago->anio === 2026);
            $this->post(route('pagos-pilotos.store'), $this->payload($piloto))->assertRedirect();
            $pago = PagoPiloto::firstOrFail();
            $this->put(route('pagos-pilotos.pagar', $pago))->assertRedirect();
            $this->assertSame('30-09-2026', $pago->fresh()->fecha_pago_texto);
            $this->get(route('pagos-pilotos.imprimir', $pago))->assertOk()->assertSee('30-09-2026');
        } finally {
            $this->travelBack();
        }
    }

    public function test_generation_selects_only_driver_trips_in_selected_month_and_does_not_invent_rates(): void
    {
        $piloto = $this->loginWithPiloto();
        $otro = Piloto::create(['nombre' => 'OTRO PILOTO']);
        $primero = $this->carta($piloto, ['fecha' => '2026-09-01']);
        $ultimo = $this->carta($piloto, ['fecha' => '2026-09-30']);
        $historico = $this->carta($piloto, ['piloto_id' => null, 'fecha' => '2026-09-15']);
        $this->carta($piloto, ['fecha' => '2026-08-31']);
        $this->carta($piloto, ['fecha' => '2026-10-01']);
        $this->carta($piloto, ['fecha' => '2025-09-15']);
        $this->carta($otro, ['fecha' => '2026-09-15']);
        $this->carta($otro, ['piloto_nombre' => $piloto->nombre, 'fecha' => '2026-09-16']);

        $response = $this->get(route('pagos-pilotos.create', [
            'piloto_id' => $piloto->id,
            'mes' => 9,
            'anio' => 2026,
        ]))->assertOk();

        $viajes = collect($response->viewData('viajes'));
        $this->assertSame([$primero->id, $historico->id, $ultimo->id], $viajes->pluck('carta_porte_id')->all());
        $this->assertTrue($viajes->every(fn ($viaje) => (float) data_get($viaje, 'valor') === 0.0));
        $this->assertDatabaseCount('pagos_pilotos', 0);
    }

    public function test_server_recalculates_example_totals_and_preserves_disabled_movements(): void
    {
        $piloto = $this->loginWithPiloto();
        $payload = $this->payload($piloto, [
            'sueldo_base' => '3816.90',
            'viajes' => [
                $this->viaje(['valor' => '3000.00']),
                $this->viaje(['referencia' => 'SEGUNDO MANUAL', 'valor' => '300.00']),
            ],
            'movimientos' => [
                $this->movimiento('Bonificación', 'SUMA', '250.00'),
                $this->movimiento('IGSS', 'DESCUENTO', '184.36'),
                $this->movimiento('Seguro piloto', 'DESCUENTO', '83.15'),
                $this->movimiento('Adelanto sobre sueldo', 'DESCUENTO', '4566.90'),
                $this->movimiento('Préstamo inactivo', 'DESCUENTO', '1000.00', false),
                $this->movimiento('Bono inactivo', 'SUMA', '9000.00', false),
            ],
            'total_viajes' => '1.00',
            'total_ingresos' => '1.00',
            'total_descuentos' => '1.00',
            'total_pagar' => '999999.00',
            'estado' => 'PAGADO',
        ]);

        $this->post(route('pagos-pilotos.store'), $payload)->assertRedirect();

        $pago = PagoPiloto::firstOrFail();
        $this->assertSame('3300.00', $pago->total_viajes);
        $this->assertSame('3816.90', $pago->sueldo_base);
        $this->assertSame('250.00', $pago->total_ingresos);
        $this->assertSame('4834.41', $pago->total_descuentos);
        $this->assertSame('2532.49', $pago->total_pagar);
        $this->assertSame('BORRADOR', $pago->estado);
        $this->assertNull($pago->fecha_pago);
        $this->assertSame(6, $pago->movimientos()->count());
        $this->assertSame(2, $pago->movimientos()->where('aplicar', false)->count());
        $this->assertDatabaseCount('cartas_porte', 0);

        $this->get(route('pagos-pilotos.imprimir', $pago))
            ->assertOk()
            ->assertSee('2,532.49')
            ->assertSee('IGSS')
            ->assertDontSee('Préstamo inactivo')
            ->assertDontSee('Bono inactivo');
    }

    public function test_money_is_calculated_in_exact_cents(): void
    {
        $piloto = $this->loginWithPiloto();

        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'sueldo_base' => '0.10',
            'viajes' => [$this->viaje(['valor' => '0.20'])],
            'movimientos' => [$this->movimiento('Ajuste', 'DESCUENTO', '0.30')],
        ]))->assertRedirect();

        $this->assertSame('0.00', PagoPiloto::firstOrFail()->total_pagar);
    }

    public function test_compact_browser_payload_preserves_more_than_one_thousand_form_fields(): void
    {
        $piloto = $this->loginWithPiloto();
        $viajes = array_map(fn (int $indice) => $this->viaje([
            'id' => '', 'carta_porte_id' => '', 'es_manual' => '1', 'orden' => (string) $indice,
            'referencia' => 'MANUAL-'.$indice, 'valor' => '0.10', 'observacion' => '',
        ]), range(0, 124));
        $movimientos = array_map(fn (int $indice) => [
            'concepto' => 'Bono '.$indice, 'tipo' => 'SUMA', 'valor' => '2.00',
            'aplicar' => $indice === 29 ? '0' : '1', 'observacion' => '', 'orden' => (string) $indice,
        ], range(0, 29));
        $this->assertGreaterThan(1000, count($viajes) * count($viajes[0]) + count($movimientos) * count($movimientos[0]));

        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'viajes' => '', 'movimientos' => '',
            'viajes_json' => json_encode($viajes, JSON_THROW_ON_ERROR),
            'movimientos_json' => json_encode($movimientos, JSON_THROW_ON_ERROR),
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $pago = PagoPiloto::firstOrFail();
        $this->assertSame(125, $pago->viajes()->count());
        $this->assertSame(30, $pago->movimientos()->count());
        $this->assertSame('12.50', $pago->total_viajes);
        $this->assertSame('58.00', $pago->total_ingresos);
        $this->assertSame('170.50', $pago->total_pagar);
        $ultimo = $pago->viajes()->where('orden', 124)->firstOrFail();
        $this->assertSame('MANUAL-124', $ultimo->referencia);
        $this->assertNull($ultimo->carta_porte_id);
        $this->assertFalse($pago->movimientos()->where('orden', 29)->firstOrFail()->aplicar);
        $this->assertDatabaseCount('cartas_porte', 0);

        $this->put(route('pagos-pilotos.update', $pago), [
            'sueldo_base' => '100.00', 'viajes_json' => '[]', 'movimientos_json' => '[]',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('100.00', $pago->fresh()->total_pagar);
        $this->assertDatabaseCount('pago_piloto_viajes', 0);
        $this->assertDatabaseCount('pago_piloto_movimientos', 0);
    }

    #[DataProvider('invalidCompactCollections')]
    public function test_invalid_compact_collection_cannot_create_or_replace_payment_rows(string $field, string $json): void
    {
        $piloto = $this->loginWithPiloto();
        $invalid = $this->payload($piloto, [$field.'_json' => $json]);
        $this->post(route('pagos-pilotos.store'), $invalid)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('pagos_pilotos', 0);
        $this->assertDatabaseCount('pago_piloto_viajes', 0);
        $this->assertDatabaseCount('pago_piloto_movimientos', 0);

        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'movimientos' => [$this->movimiento('Bono original', 'SUMA', '50.00')],
        ]))->assertRedirect();
        $pago = PagoPiloto::firstOrFail();
        $original = [$pago->getAttributes(), $pago->viajes()->firstOrFail()->getAttributes(), $pago->movimientos()->firstOrFail()->getAttributes()];

        $this->put(route('pagos-pilotos.update', $pago), $invalid)->assertSessionHasErrors($field);
        $this->assertSame($original, [$pago->fresh()->getAttributes(), $pago->viajes()->firstOrFail()->getAttributes(), $pago->movimientos()->firstOrFail()->getAttributes()]);
    }

    public static function invalidCompactCollections(): array
    {
        return [
            'malformed trips' => ['viajes', '[{"fecha":'],
            'object resembling a list' => ['viajes', '{"0":{"fecha":"2026-09-15"}}'],
            'null movements' => ['movimientos', 'null'],
            'empty object movements' => ['movimientos', '{}'],
        ];
    }

    public function test_templates_accept_compact_rows_and_reject_invalid_json_without_erasing_existing_concepts(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->put(route('pagos-pilotos.guardar-conceptos', $piloto), [
            'movimientos' => '',
            'movimientos_json' => json_encode([
                $this->movimiento('Bono habitual', 'SUMA', '125.50'),
                $this->movimiento('Seguro desactivado', 'DESCUENTO', '83.15', false),
            ], JSON_THROW_ON_ERROR),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $original = $piloto->conceptosPago()->get()->toArray();
        $this->assertCount(2, $original);
        $this->assertFalse((bool) $original[1]['activo']);
        $this->assertSame('125.50', (string) $original[0]['valor_default']);

        foreach (['[', '{}'] as $json) {
            $this->put(route('pagos-pilotos.guardar-conceptos', $piloto), ['movimientos_json' => $json])
                ->assertSessionHasErrors('movimientos');
            $this->assertSame($original, $piloto->conceptosPago()->get()->toArray());
        }
    }

    public function test_payment_trip_is_an_editable_snapshot_and_manual_trip_does_not_create_a_carta(): void
    {
        $piloto = $this->loginWithPiloto();
        $carta = $this->carta($piloto);
        $original = $carta->fresh()->getAttributes();
        $payload = $this->payload($piloto, [
            'viajes' => [
                $this->viaje([
                    'carta_porte_id' => $carta->id,
                    'es_manual' => false,
                    'fecha' => '2026-09-20',
                    'referencia' => 'REF EDITADA',
                    'consignatario' => 'Cliente corregido',
                    'destino' => 'Destino corregido',
                ]),
                $this->viaje(['referencia' => 'VIAJE SIN CARTA']),
            ],
        ]);

        $this->post(route('pagos-pilotos.store'), $payload)->assertRedirect();
        $pago = PagoPiloto::firstOrFail();
        $this->assertSame($original, $carta->fresh()->getAttributes());
        $this->assertDatabaseCount('cartas_porte', 1);
        $this->assertSame(2, $pago->viajes()->count());
        $this->assertDatabaseHas('pago_piloto_viajes', [
            'pago_piloto_id' => $pago->id,
            'carta_porte_id' => $carta->id,
            'referencia' => 'REF EDITADA',
            'consignatario' => 'Cliente corregido',
            'destino' => 'Destino corregido',
            'es_manual' => false,
        ]);

        $carta->update(['destino' => 'Cambio posterior en carta', 'consignatario_nombre' => 'Nuevo cliente original']);
        $piloto->update(['nombre' => 'NOMBRE POSTERIOR']);
        $this->assertSame('PILOTO DE PRUEBA', $pago->fresh()->piloto_nombre);
        $this->assertSame('Destino corregido', $pago->viajes()->where('carta_porte_id', $carta->id)->firstOrFail()->destino);

        $this->delete(route('cartas-porte.destroy', $carta))->assertRedirect();
        $this->delete(route('catalogos.destroy', ['pilotos', $piloto]))->assertRedirect();
        $pago->refresh();
        $this->assertNull($pago->piloto_id);
        $this->assertSame('PILOTO DE PRUEBA', $pago->piloto_nombre);
        $this->assertSame(2, $pago->viajes()->count());
        $this->assertSame(0, $pago->viajes()->whereNotNull('carta_porte_id')->count());
        $this->get(route('pagos-pilotos.show', $pago))->assertOk()->assertSee('REF EDITADA');
        $this->get(route('pagos-pilotos.imprimir', $pago))->assertOk()->assertSee('PILOTO DE PRUEBA');
    }

    public function test_draft_can_replace_rows_and_toggle_movements_without_changing_payment_identity(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto))->assertRedirect();
        $pago = PagoPiloto::firstOrFail();
        $otro = Piloto::create(['nombre' => 'OTRO PILOTO']);

        $this->put(route('pagos-pilotos.update', $pago), [
            'piloto_id' => $otro->id,
            'mes' => 10,
            'anio' => 2030,
            'sueldo_base' => '200.00',
            'viajes' => [$this->viaje(['referencia' => 'Reemplazo', 'valor' => '300.00'])],
            'movimientos' => [$this->movimiento('Descuento retenido', 'DESCUENTO', '50.00', false)],
            'observaciones' => 'Revisado',
            'total_pagar' => '999.00',
        ])->assertRedirect();

        $pago->refresh();
        $this->assertSame($piloto->id, $pago->piloto_id);
        $this->assertSame(9, (int) $pago->mes);
        $this->assertSame(2026, (int) $pago->anio);
        $this->assertSame('500.00', $pago->total_pagar);
        $this->assertSame('Revisado', $pago->observaciones);
        $this->assertSame(1, $pago->viajes()->count());
        $this->assertSame('Reemplazo', $pago->viajes()->firstOrFail()->referencia);
        $this->assertSame(1, $pago->movimientos()->count());

        $this->put(route('pagos-pilotos.update', $pago), [
            'sueldo_base' => '200.00',
            'viajes' => [],
            'movimientos' => [$this->movimiento('Descuento retenido', 'DESCUENTO', '50.00', true)],
        ])->assertRedirect();
        $this->assertSame('150.00', $pago->fresh()->total_pagar);
        $this->assertSame(0, $pago->viajes()->count());
    }

    public function test_duplicate_active_period_is_rejected_and_annulled_payment_can_be_replaced(): void
    {
        $piloto = $this->loginWithPiloto();
        $payload = $this->payload($piloto);
        $this->post(route('pagos-pilotos.store'), $payload)->assertRedirect();
        $pago = PagoPiloto::firstOrFail();

        $this->post(route('pagos-pilotos.store'), $payload)->assertRedirect();
        $this->assertDatabaseCount('pagos_pilotos', 1);
        $this->assertDatabaseCount('pago_piloto_viajes', 1);

        $this->get(route('pagos-pilotos.create', ['piloto_id' => $piloto->id, 'mes' => 9, 'anio' => 2026]))
            ->assertRedirect(route('pagos-pilotos.show', $pago));
        $this->put(route('pagos-pilotos.anular', $pago))->assertRedirect();
        $this->assertSame('ANULADO', $pago->fresh()->estado);
        $this->assertNull($pago->fresh()->periodo_activo);

        $this->post(route('pagos-pilotos.store'), $payload)->assertRedirect();
        $this->assertDatabaseCount('pagos_pilotos', 2);
        $this->assertDatabaseCount('pago_piloto_viajes', 2);
        $this->assertSame(1, PagoPiloto::where('estado', 'BORRADOR')->count());
        $this->assertSame('ANULADO', $pago->fresh()->estado);
    }

    public function test_database_constraint_blocks_an_active_duplicate_even_without_controller_check(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto))->assertRedirect();
        $pago = PagoPiloto::firstOrFail();

        try {
            DB::transaction(fn () => $pago->replicate()->save());
            $this->fail('La base de datos debió rechazar el período duplicado.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('unique', strtolower($exception->getMessage()));
        }

        $this->assertDatabaseCount('pagos_pilotos', 1);
    }

    public function test_draft_keeps_automatic_origin_when_its_original_carta_has_been_deleted(): void
    {
        $piloto = $this->loginWithPiloto();
        $carta = $this->carta($piloto);
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'viajes' => [$this->viaje(['carta_porte_id' => $carta->id, 'es_manual' => false])],
        ]))->assertRedirect();
        $pago = PagoPiloto::firstOrFail();
        $viaje = $pago->viajes()->firstOrFail();
        $carta->delete();

        $this->put(route('pagos-pilotos.update', $pago), $this->payload($piloto, [
            'viajes' => [$this->viaje([
                'id' => $viaje->id, 'carta_porte_id' => null, 'es_manual' => false, 'valor' => '700.00',
            ])],
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $actualizado = $pago->viajes()->firstOrFail();
        $this->assertFalse($actualizado->es_manual);
        $this->assertNull($actualizado->carta_porte_id);
        $this->assertSame('700.00', $actualizado->valor);
        $this->assertSame('800.00', $pago->fresh()->total_pagar);
        $this->assertDatabaseCount('cartas_porte', 0);
    }

    public function test_paid_and_annulled_payments_preserve_history_and_cannot_be_edited_or_repaid(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto))->assertRedirect();
        $pago = PagoPiloto::firstOrFail();
        $this->put(route('pagos-pilotos.pagar', $pago))->assertRedirect();
        $pago->refresh();
        $this->assertSame('PAGADO', $pago->estado);
        $this->assertNotNull($pago->fecha_pago);
        $total = $pago->total_pagar;
        $fechaPago = (string) $pago->fecha_pago;

        $this->get(route('pagos-pilotos.edit', $pago))->assertRedirect();
        $this->put(route('pagos-pilotos.update', $pago), $this->payload($piloto, [
            'sueldo_base' => '999.00',
        ]))->assertRedirect();
        $this->assertSame($total, $pago->fresh()->total_pagar);
        $this->assertSame('PAGADO', $pago->fresh()->estado);

        $this->put(route('pagos-pilotos.anular', $pago))->assertRedirect();
        $this->assertSame('ANULADO', $pago->fresh()->estado);
        $this->assertSame($fechaPago, (string) $pago->fresh()->fecha_pago);
        $this->get(route('pagos-pilotos.edit', $pago))->assertRedirect();
        $this->put(route('pagos-pilotos.update', $pago), $this->payload($piloto, ['sueldo_base' => '999.00']))->assertRedirect();
        $this->put(route('pagos-pilotos.pagar', $pago))->assertRedirect();
        $this->assertSame('ANULADO', $pago->fresh()->estado);
        $this->assertSame($total, $pago->fresh()->total_pagar);
        $this->assertDatabaseCount('pago_piloto_viajes', 1);
        $this->get(route('pagos-pilotos.imprimir', $pago))->assertOk()->assertSee('ANULADO');
    }

    public function test_driver_templates_are_copied_and_monthly_edits_do_not_modify_them(): void
    {
        $piloto = $this->loginWithPiloto();
        $movimientos = [
            $this->movimiento('IGSS', 'DESCUENTO', '184.36', false),
            $this->movimiento('Bonificación habitual', 'SUMA', '250.00'),
        ];
        $this->put(route('pagos-pilotos.guardar-conceptos', $piloto), ['movimientos' => $movimientos])->assertRedirect();
        $this->assertDatabaseHas('piloto_conceptos_pago', [
            'piloto_id' => $piloto->id, 'concepto' => 'IGSS', 'activo' => false, 'valor_default' => 184.36,
        ]);

        $response = $this->get(route('pagos-pilotos.create', ['piloto_id' => $piloto->id, 'mes' => 9, 'anio' => 2026]))->assertOk();
        $copiados = collect($response->viewData('movimientos'));
        $this->assertSame(['IGSS', 'Bonificación habitual'], $copiados->pluck('concepto')->all());
        $this->assertFalse((bool) data_get($copiados->first(), 'aplicar'));

        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'movimientos' => [$this->movimiento('Bonificación solo este mes', 'SUMA', '500.00')],
        ]))->assertRedirect();
        $this->assertDatabaseCount('piloto_conceptos_pago', 2);
        $this->assertDatabaseMissing('piloto_conceptos_pago', ['concepto' => 'Bonificación solo este mes']);

        $this->put(route('pagos-pilotos.guardar-conceptos', $piloto), ['movimientos' => []])->assertRedirect();
        $this->assertDatabaseCount('piloto_conceptos_pago', 0);
        $this->assertSame('Bonificación solo este mes', PagoPiloto::firstOrFail()->movimientos()->firstOrFail()->concepto);
    }

    #[DataProvider('invalidMoney')]
    public function test_invalid_monetary_values_do_not_create_partial_payments(string $amount): void
    {
        $piloto = $this->loginWithPiloto();
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'sueldo_base' => $amount,
            'viajes' => [$this->viaje(['valor' => $amount])],
            'movimientos' => [$this->movimiento('Bono', 'SUMA', $amount)],
        ]))->assertSessionHasErrors(['sueldo_base', 'viajes.0.valor', 'movimientos.0.valor']);

        $this->assertDatabaseCount('pagos_pilotos', 0);
        $this->assertDatabaseCount('pago_piloto_viajes', 0);
        $this->assertDatabaseCount('pago_piloto_movimientos', 0);
    }

    public static function invalidMoney(): array
    {
        return [
            'negative' => ['-0.01'],
            'too many decimal places' => ['1.234'],
            'thousands separator' => ['1,000.00'],
            'scientific notation' => ['1e3'],
            'too large' => ['10000000000.00'],
            'not a number' => ['gratis'],
        ];
    }

    public function test_invalid_period_and_nested_rows_are_rejected(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'piloto_id' => 999999,
            'mes' => 13,
            'anio' => 0,
            'viajes' => [$this->viaje(['fecha' => 'fecha inválida'])],
            'movimientos' => [$this->movimiento('Bono', 'MULTIPLICAR', '10.00')],
        ]))->assertSessionHasErrors(['piloto_id', 'mes', 'anio', 'viajes.0.fecha', 'movimientos.0.tipo']);

        $this->assertDatabaseCount('pagos_pilotos', 0);
    }

    public function test_cannot_attach_another_drivers_carta_to_payment(): void
    {
        $piloto = $this->loginWithPiloto();
        $otro = Piloto::create(['nombre' => 'OTRO PILOTO']);
        $carta = $this->carta($otro);

        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'viajes' => [$this->viaje(['carta_porte_id' => $carta->id, 'es_manual' => false])],
        ]))->assertSessionHasErrors();

        $this->assertDatabaseCount('pagos_pilotos', 0);
        $this->assertDatabaseCount('cartas_porte', 1);
    }

    public function test_new_payment_rejects_out_of_period_duplicate_or_missing_source_cartas(): void
    {
        $piloto = $this->loginWithPiloto();
        $fueraDelMes = $this->carta($piloto, ['fecha' => '2026-08-31']);
        $delMes = $this->carta($piloto);
        $automatico = $this->viaje(['carta_porte_id' => $delMes->id, 'es_manual' => false]);

        foreach ([
            [$this->viaje(['carta_porte_id' => $fueraDelMes->id, 'es_manual' => false])],
            [$automatico, $automatico],
            [$this->viaje(['carta_porte_id' => 999999, 'es_manual' => false])],
            [$this->viaje(['carta_porte_id' => null, 'es_manual' => false])],
            [$this->viaje(['carta_porte_id' => $delMes->id, 'es_manual' => true])],
        ] as $viajes) {
            $this->post(route('pagos-pilotos.store'), $this->payload($piloto, ['viajes' => $viajes]))
                ->assertSessionHasErrors();
            $this->assertDatabaseCount('pagos_pilotos', 0);
            $this->assertDatabaseCount('pago_piloto_viajes', 0);
        }
    }

    public function test_failure_saving_trip_rolls_back_payment_and_children(): void
    {
        $piloto = $this->loginWithPiloto();
        $event = 'eloquent.creating: '.PagoPilotoViaje::class;
        Event::listen($event, function (): void {
            throw new RuntimeException('Fallo simulado al guardar viaje');
        });
        $this->withoutExceptionHandling();

        try {
            $this->post(route('pagos-pilotos.store'), $this->payload($piloto));
            $this->fail('La inserción del viaje debió fallar.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo simulado al guardar viaje', $exception->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertDatabaseCount('pagos_pilotos', 0);
        $this->assertDatabaseCount('pago_piloto_viajes', 0);
        $this->assertDatabaseCount('pago_piloto_movimientos', 0);
    }

    public function test_failure_updating_movements_restores_original_payment_and_all_deleted_rows(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto, [
            'movimientos' => [$this->movimiento('Bono original', 'SUMA', '150.00')],
        ]))->assertRedirect();
        $pago = PagoPiloto::firstOrFail();
        $originalPago = $pago->getAttributes();
        $originalViaje = $pago->viajes()->firstOrFail()->getAttributes();
        $originalMovimiento = $pago->movimientos()->firstOrFail()->getAttributes();
        $event = 'eloquent.creating: '.PagoPilotoMovimiento::class;
        Event::listen($event, function (): void {
            throw new RuntimeException('Fallo simulado al actualizar movimientos');
        });
        $this->withoutExceptionHandling();

        try {
            $this->put(route('pagos-pilotos.update', $pago), $this->payload($piloto, [
                'sueldo_base' => '900.00',
                'viajes' => [$this->viaje(['referencia' => 'Viaje reemplazado', 'valor' => '10.00'])],
                'movimientos' => [$this->movimiento('Movimiento reemplazado', 'DESCUENTO', '500.00')],
            ]));
            $this->fail('La actualización del movimiento debió fallar.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo simulado al actualizar movimientos', $exception->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertSame($originalPago, $pago->fresh()->getAttributes());
        $this->assertSame($originalViaje, $pago->viajes()->firstOrFail()->getAttributes());
        $this->assertSame($originalMovimiento, $pago->movimientos()->firstOrFail()->getAttributes());
        $this->assertDatabaseCount('pago_piloto_viajes', 1);
        $this->assertDatabaseCount('pago_piloto_movimientos', 1);
    }

    public function test_payment_pages_render_and_history_filters_do_not_mix_pilots_or_periods(): void
    {
        $piloto = $this->loginWithPiloto();
        $otro = Piloto::create(['nombre' => 'PILOTO AJENO']);
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto))->assertRedirect();
        $pago = PagoPiloto::firstOrFail();
        $this->post(route('pagos-pilotos.store'), $this->payload($otro, ['mes' => 10]))->assertRedirect();

        $this->get(route('pagos-pilotos.index'))->assertOk()->assertSee($piloto->nombre)->assertSee($otro->nombre);
        $this->get(route('pagos-pilotos.show', $pago))->assertOk()->assertSee($piloto->nombre);
        $this->get(route('pagos-pilotos.edit', $pago))->assertOk();
        $this->get(route('pagos-pilotos.conceptos', $piloto))->assertOk();
        $this->get(route('pagos-pilotos.imprimir', $pago))->assertOk()->assertSee($piloto->nombre);

        $response = $this->get(route('pagos-pilotos.historial', [
            'piloto_id' => $piloto->id, 'mes' => 9, 'anio' => 2026, 'estado' => 'BORRADOR',
        ]))->assertOk();
        $this->assertSame([$pago->id], $response->viewData('pagos')->pluck('id')->all());
        $response = $this->get(route('pagos-pilotos.historial', ['estado' => 'PAGADO']))->assertOk();
        $this->assertCount(0, $response->viewData('pagos'));
    }

    public function test_driver_index_query_count_does_not_grow_with_each_driver(): void
    {
        $piloto = $this->loginWithPiloto();
        $this->post(route('pagos-pilotos.store'), $this->payload($piloto))->assertRedirect();
        $queryCounts = [];
        DB::enableQueryLog();

        try {
            foreach ([1, 16] as $size) {
                if ($size === 16) {
                    foreach (range(2, 16) as $number) {
                        Piloto::create(['nombre' => 'PILOTO '.$number, 'activo' => true]);
                    }
                }
                DB::flushQueryLog();
                $response = $this->get(route('pagos-pilotos.index', ['mes' => 9, 'anio' => 2026]))->assertOk();
                $queryCounts[] = count(DB::getQueryLog());
                $this->assertCount($size, $response->viewData('pilotos'));
                $response->assertSee('Continuar pago');
            }
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        $this->assertSame($queryCounts[0], $queryCounts[1], 'El listado debe cargar las relaciones en bloque.');
    }

    private function loginWithPiloto(): Piloto
    {
        $this->actingAs(User::factory()->create());

        return Piloto::create(['nombre' => 'PILOTO DE PRUEBA', 'activo' => true]);
    }

    private function carta(Piloto $piloto, array $overrides = []): CartaPorte
    {
        return CartaPorte::create(array_merge([
            'numero_correlativo' => ((int) CartaPorte::max('numero_correlativo')) + 1,
            'fecha' => '2026-09-15',
            'piloto_id' => $piloto->id,
            'piloto_nombre' => $piloto->nombre,
            'consignatario_nombre' => 'Cliente original',
            'destino' => 'Destino original',
            'procedencia_nombre' => 'Santo Tomás',
            'contenedor' => 'ABCD1234567',
            'bl' => 'BL-PAGOS',
            'poliza' => 'POL-PAGOS',
        ], $overrides));
    }

    private function payload(Piloto $piloto, array $overrides = []): array
    {
        return array_merge([
            'piloto_id' => $piloto->id,
            'mes' => 9,
            'anio' => 2026,
            'sueldo_base' => '100.00',
            'viajes' => [$this->viaje()],
            'movimientos' => [],
            'observaciones' => 'Pago de prueba',
        ], $overrides);
    }

    private function viaje(array $overrides = []): array
    {
        return array_merge([
            'carta_porte_id' => null,
            'es_manual' => true,
            'fecha' => '2026-09-15',
            'referencia' => 'MANUAL-001',
            'consignatario' => 'Cliente del viaje',
            'destino' => 'La Democracia',
            'valor' => '550.00',
            'observacion' => 'Observación del viaje',
        ], $overrides);
    }

    private function movimiento(string $concepto, string $tipo, string $valor, bool $aplicar = true): array
    {
        return compact('concepto', 'tipo', 'valor', 'aplicar');
    }
}
