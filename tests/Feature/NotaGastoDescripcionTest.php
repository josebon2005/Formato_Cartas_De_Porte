<?php

namespace Tests\Feature;

use App\Models\CartaPorte;
use App\Models\NotaGasto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaGastoDescripcionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_all_container_numbers_are_ordered_deduplicated_and_empty_placeholders_are_ignored(): void
    {
        $expected = [];
        for ($number = 25; $number >= 1; $number--) {
            $container = 'ABCD'.str_pad((string) $number, 7, '0', STR_PAD_LEFT);
            $this->carta(['numero_correlativo' => $number, 'contenedor' => $container]);
            array_unshift($expected, $container);
        }
        foreach ([' abcd0000001 ', null, '', '   ', 'N/A', 'NULL', 'SIN CONTENEDOR', '-', 'VACÍO'] as $container) {
            $this->carta(['contenedor' => $container]);
        }
        $this->carta(['poliza' => 'OTRA', 'contenedor' => 'NO-INCLUIR']);
        $carta = CartaPorte::where('numero_correlativo', 1)->firstOrFail();

        $description = $this->getJson(route('facturacion.notas-gastos.descripcion-desde-carta', $carta))
            ->assertOk()->json('descripcion');
        $this->assertSame(
            'Valor flete Puerto Quetzal hacia La Democracia por 34 contenedores No. '.implode(', ', $expected)
                .' conteniendo Producto X, amparado con BL-BL-123 Póliza-POL-456.',
            $description,
        );

        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload())->assertRedirect();
        $nota = NotaGasto::firstOrFail();
        $this->assertSame($description, $nota->descripcion);
        $this->assertCount(34, $nota->cartasPorte);
    }

    public function test_singular_description_omits_number_section_when_no_container_number_exists(): void
    {
        $carta = $this->carta(['contenedor' => null]);
        $this->getJson(route('facturacion.notas-gastos.descripcion-desde-carta', $carta))
            ->assertOk()->assertJsonPath('descripcion',
                'Valor flete Puerto Quetzal hacia La Democracia por 1 contenedor conteniendo Producto X, amparado con BL-BL-123 Póliza-POL-456.');

        $this->carta(['contenedor' => 'N/A']);
        $description = $this->getJson(route('facturacion.notas-gastos.descripcion-desde-carta', $carta))
            ->assertOk()->json('descripcion');
        $this->assertStringContainsString('por 2 contenedores conteniendo', $description);
        $this->assertStringNotContainsString(' No.', $description);
    }

    public function test_manual_description_preserves_case_spaces_line_breaks_and_markup_across_billing_and_printing(): void
    {
        $carta = $this->carta();
        $manual = "  Servicio <especial> & carga\nSegunda línea Mixta  ";
        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload(['descripcion' => $manual]))
            ->assertRedirect();
        $nota = NotaGasto::firstOrFail();
        $this->assertSame($manual, $nota->descripcion);

        foreach (['show', 'edit', 'facturar'] as $action) {
            $this->get(route('facturacion.notas-gastos.'.$action, $nota))
                ->assertOk()->assertSee($manual)->assertDontSee('<especial>', false);
        }
        $this->put(route('facturacion.notas-gastos.facturar.update', $nota), ['fel_numero' => 'SAT-123'])->assertRedirect();
        $this->get(route('facturacion.notas-gastos.imprimir', $nota))->assertOk()->assertSee($manual);
        $this->assertSame($manual, $nota->fresh()->descripcion);

        $edited = "\n  Descripción corregida\nFIN  ";
        $this->put(route('facturacion.notas-gastos.update', $nota), $this->payload(['descripcion' => $edited]))->assertRedirect();
        $this->assertSame($edited, $nota->fresh()->descripcion);
        $this->put(route('facturacion.notas-gastos.update', $nota), $this->payload())->assertRedirect();
        $this->assertSame($edited, $nota->fresh()->descripcion);
    }

    public function test_explicitly_clearing_description_is_preserved_and_does_not_trigger_legacy_fallback(): void
    {
        $carta = $this->carta();
        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload(['descripcion' => '']))->assertRedirect();
        $nota = NotaGasto::firstOrFail();
        $this->assertSame('', $nota->descripcion);
        $this->get(route('facturacion.notas-gastos.show', $nota))->assertOk()->assertDontSee('Valor flete');
        $this->put(route('facturacion.notas-gastos.update', $nota), $this->payload(['descripcion' => 'Temporal']))->assertRedirect();
        $this->put(route('facturacion.notas-gastos.update', $nota), $this->payload(['descripcion' => '']))->assertRedirect();
        $this->put(route('facturacion.notas-gastos.update', $nota), $this->payload())->assertRedirect();
        $this->assertSame('', $nota->fresh()->descripcion);
    }

    public function test_failed_validation_retains_manual_old_input_including_a_leading_newline(): void
    {
        $carta = $this->carta();
        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload(['descripcion' => 'Guardada']))->assertRedirect();
        $nota = NotaGasto::firstOrFail();
        $draft = "\n  Descripción sin guardar\nFinal  ";
        $this->from(route('facturacion.notas-gastos.edit', $nota))
            ->put(route('facturacion.notas-gastos.update', $nota), ['descripcion' => $draft, 'detalles' => []])
            ->assertSessionHasErrors('detalles')->assertSessionHas('_old_input.descripcion', $draft);
        $this->get(route('facturacion.notas-gastos.edit', $nota))
            ->assertOk()->assertSee('rows="5">'."\n".e($draft).'</textarea>', false);
        $this->assertSame('Guardada', $nota->fresh()->descripcion);
    }

    public function test_regeneration_reads_current_linked_letters_and_only_persists_when_note_is_saved(): void
    {
        $carta = $this->carta();
        $second = $this->carta(['contenedor' => 'EFGH7654321']);
        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload(['descripcion' => 'Mi descripción']))
            ->assertRedirect();
        $nota = NotaGasto::firstOrFail();
        $this->carta(['contenedor' => 'NO-VINCULADO']);
        $carta->update([
            'contenedor' => 'NUEVO1234567', 'procedencia_nombre' => 'Puerto nuevo',
            'destino' => 'Destino nuevo', 'contenido' => 'Carga nueva',
            'bl' => 'BL-NUEVO', 'poliza' => 'POL-NUEVA',
        ]);

        $description = $this->getJson(route('facturacion.notas-gastos.descripcion', $nota))
            ->assertOk()->assertJsonPath('descripcion',
                'Valor flete Puerto nuevo hacia Destino nuevo por 2 contenedores No. NUEVO1234567, EFGH7654321 conteniendo Carga nueva, amparado con BL-BL-NUEVO Póliza-POL-NUEVA.')
            ->json('descripcion');
        $this->assertSame('Mi descripción', $nota->fresh()->descripcion);
        $this->get(route('facturacion.notas-gastos.edit', $nota))
            ->assertOk()->assertSee('Cancelar')->assertSee('Regenerar')
            ->assertSee('Se reemplazarán los cambios realizados manualmente.');
        $this->put(route('facturacion.notas-gastos.update', $nota), $this->payload(['descripcion' => $description]))->assertRedirect();
        $this->assertSame($description, $nota->fresh()->descripcion);
        $this->assertSame('EFGH7654321', $second->fresh()->contenedor);
        $this->assertDatabaseCount('cartas_porte', 3);
    }

    public function test_preview_regeneration_queries_new_letters_for_the_same_operation(): void
    {
        $carta = $this->carta();
        $this->get(route('facturacion.notas-gastos.desde-carta', $carta))->assertOk();
        $this->carta(['contenedor' => 'NUEVO7654321']);
        $description = $this->getJson(route('facturacion.notas-gastos.descripcion-desde-carta', $carta))
            ->assertOk()->json('descripcion');
        $this->assertStringContainsString('por 2 contenedores No. ABCD1234567, NUEVO7654321', $description);
        $this->assertDatabaseCount('notas_gastos', 0);
    }

    public function test_legacy_null_description_has_consistent_read_only_fallback_until_saved(): void
    {
        $carta = $this->carta();
        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload())->assertRedirect();
        $nota = NotaGasto::firstOrFail();
        $description = $nota->descripcion;
        $nota->update(['descripcion' => null, 'estado' => NotaGasto::ESTADO_FACTURADA, 'fel_numero' => 'SAT-LEGACY']);

        foreach (['show', 'edit', 'facturar', 'imprimir'] as $action) {
            $this->get(route('facturacion.notas-gastos.'.$action, $nota))->assertOk()->assertSee($description);
            $this->assertNull($nota->fresh()->descripcion);
        }
        $this->put(route('facturacion.notas-gastos.update', $nota), $this->payload())->assertRedirect();
        $this->assertSame($description, $nota->fresh()->descripcion);
    }

    public function test_invalid_description_is_rejected_without_creating_a_note(): void
    {
        $carta = $this->carta();
        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload(['descripcion' => ['invalid']]))
            ->assertSessionHasErrors('descripcion');
        $this->assertDatabaseCount('notas_gastos', 0);
        $this->assertDatabaseCount('carta_porte_nota_gasto', 0);
    }

    public function test_catalog_description_keeps_its_existing_whitespace_normalization(): void
    {
        $carta = $this->carta();
        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), $this->payload(['descripcion' => '  Texto libre  ']))
            ->assertRedirect();
        $this->assertSame('  Texto libre  ', NotaGasto::firstOrFail()->descripcion);

        $this->post(route('catalogos.store', 'cabezales'), [
            'placa' => 'C-123ABC', 'descripcion' => '  Cabezal de prueba  ',
        ])->assertRedirect();
        $this->assertDatabaseHas('cabezales', ['placa' => 'C-123ABC', 'descripcion' => 'Cabezal de prueba']);
    }

    private function carta(array $overrides = []): CartaPorte
    {
        return CartaPorte::create(array_merge([
            'numero_correlativo' => (CartaPorte::max('numero_correlativo') ?? 0) + 1,
            'fecha' => '2026-09-05',
            'consignatario_nombre' => 'Cliente de prueba',
            'procedencia_nombre' => 'Puerto Quetzal',
            'destino' => 'La Democracia',
            'contenido' => 'Producto X',
            'bl' => 'BL-123',
            'poliza' => 'POL-456',
            'contenedor' => 'ABCD1234567',
        ], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['detalles' => [[
            'concepto_nombre' => 'Flete',
            'precio_unitario' => 100,
            'cantidad' => 1,
            'grupo' => 'subtotal',
            'incluido' => 1,
        ]]], $overrides);
    }
}
