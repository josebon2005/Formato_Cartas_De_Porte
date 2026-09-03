<?php

namespace Tests\Feature;

use App\Models\CartaPorte;
use App\Models\ConceptoGasto;
use App\Models\Consignatario;
use App\Models\NotaGasto;
use App\Models\Piloto;
use App\Models\TarifaCliente;
use App\Models\User;
use Database\Seeders\PilotosPropiosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('TRANSPORTES W. ORELLANA');
    }

    public function test_carta_porte_can_be_created_and_prints_three_copies_without_duplicate_record(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('cartas-porte.store'), $this->cartaPayload());

        $carta = CartaPorte::firstOrFail();

        $response->assertRedirect(route('cartas-porte.imprimir', [$carta, 'autoprint' => 1]));
        $this->assertDatabaseCount('cartas_porte', 1);
        $this->assertDatabaseMissing('consignatarios', ['nombre' => 'Cliente de Prueba']);
        $this->assertDatabaseMissing('pilotos', ['nombre' => 'Piloto de Prueba']);
        $this->assertSame('Cliente de Prueba', $carta->consignatario_nombre);
        $this->assertSame('Piloto de Prueba', $carta->piloto_nombre);

        $print = $this->get(route('cartas-porte.imprimir', $carta));

        $print->assertOk();
        $print->assertSee('Cliente de Prueba');
        $print->assertSee('Piloto de Prueba');
        $this->assertSame(3, substr_count($print->getContent(), '"CARTA DE PORTE"'));
        $this->assertDatabaseCount('cartas_porte', 1);
    }

    public function test_create_is_empty_when_there_are_no_previous_cartas(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('cartas-porte.create'));

        $response->assertOk();
        $response->assertDontSee('Formulario cargado con datos de la ultima carta');
        $response->assertSee('value="1"', false);
    }

    public function test_create_prefills_copyable_fields_from_last_carta_and_stores_new_record(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('cartas-porte.store'), $this->cartaPayload());
        $original = CartaPorte::firstOrFail();

        $form = $this->get(route('cartas-porte.create'));

        $form->assertOk();
        $form->assertSee('Formulario cargado con datos de la ultima carta');
        $form->assertSee('value="2"', false);
        $form->assertSee('value="'.now()->toDateString().'"', false);
        $form->assertSee('value="POL-001"', false);
        $form->assertSee('value="BL-001"', false);
        $form->assertSee('Mercaderia general');
        $form->assertSee('value="1500"', false);
        $form->assertSee('value="Piloto de Prueba"', false);
        $form->assertSee('value="P-123ABC"', false);
        $form->assertSee('value="LIC-001"', false);
        $form->assertDontSee('value="CONT-001"', false);

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'numero_correlativo' => 2,
            'consignatario_nombre' => 'Cliente Nuevo',
            'contenedor' => 'CONT-002',
        ]))->assertRedirect();

        $this->assertDatabaseCount('cartas_porte', 2);
        $this->assertDatabaseHas('cartas_porte', [
            'id' => $original->id,
            'numero_correlativo' => 1,
            'consignatario_nombre' => 'Cliente de Prueba',
            'contenedor' => 'CONT-001',
        ]);
        $this->assertDatabaseHas('cartas_porte', [
            'numero_correlativo' => 2,
            'consignatario_nombre' => 'Cliente Nuevo',
            'poliza' => 'POL-001',
            'bl' => 'BL-001',
            'contenido' => 'Mercaderia general',
            'peso_kls' => '1500',
            'contenedor' => 'CONT-002',
        ]);
        $this->assertDatabaseMissing('consignatarios', ['nombre' => 'Cliente Nuevo']);
    }

    public function test_pilotos_propios_seeder_loads_license_and_usual_plate(): void
    {
        $this->seed(PilotosPropiosSeeder::class);

        $piloto = Piloto::where('nombre', 'ENRIQUE MANOLO REYES ORELLANA')->firstOrFail();

        $this->assertSame('3918 86266 1801', $piloto->licencias()->first()?->numero);
        $this->assertSame('C-491BXM', $piloto->cabezalUsual?->placa);

        $victor = Piloto::where('nombre', 'VÍCTOR FRANCISCO GUTIÉRREZ RODRÍGUEZ')->firstOrFail();

        $this->assertSame('2672 44584 1801', $victor->licencias()->first()?->numero);
        $this->assertSame('C-110BPM', $victor->cabezalUsual?->placa);

        $candido = Piloto::where('nombre', 'CÁNDIDO JOSSEPTH MARÍN GAMBOA')->firstOrFail();

        $this->assertSame('2534 57181 1801', $candido->licencias()->first()?->numero);
        $this->assertSame('C-864BYK', $candido->cabezalUsual?->placa);

        $this->seed(PilotosPropiosSeeder::class);

        $this->assertDatabaseCount('pilotos', 15);
        $this->assertDatabaseCount('licencias', 15);
    }

    public function test_known_driver_allows_manual_license_and_plate_changes_when_saving(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(PilotosPropiosSeeder::class);
        $piloto = Piloto::where('nombre', 'ENRIQUE MANOLO REYES ORELLANA')->firstOrFail();

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'piloto_id' => $piloto->id,
            'piloto_nombre' => 'ENRIQUE MANOLO REYES ORELLANA',
            'cabezal_placa' => 'C-999ZZZ',
            'licencia_numero' => 'LIC-MANUAL',
        ]))->assertRedirect();

        $carta = CartaPorte::firstOrFail();

        $this->assertSame('ENRIQUE MANOLO REYES ORELLANA', $carta->piloto->nombre);
        $this->assertNull($carta->cabezal);
        $this->assertNull($carta->licencia);
        $this->assertSame('C-999ZZZ', $carta->cabezal_placa);
        $this->assertSame('LIC-MANUAL', $carta->licencia_numero);
        $this->assertDatabaseHas('cabezales', ['placa' => 'C-491BXM']);
        $this->assertDatabaseHas('licencias', ['numero' => '3918 86266 1801']);
        $this->assertDatabaseMissing('cabezales', ['placa' => 'C-999ZZZ']);
        $this->assertDatabaseMissing('licencias', ['numero' => 'LIC-MANUAL']);
    }

    public function test_piloto_catalog_updates_new_carta_autocomplete_and_can_be_deactivated(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('catalogos.store', 'pilotos'), [
            'nombre' => 'JUAN PEREZ',
            'licencia_numero' => '123456789',
            'cabezal_placa' => 'C-123BCD',
            'activo' => '1',
        ])->assertRedirect(route('catalogos.index'));

        $piloto = Piloto::where('nombre', 'JUAN PEREZ')->firstOrFail();

        $this->assertTrue($piloto->activo);
        $this->assertSame('123456789', $piloto->licencias()->first()?->numero);
        $this->assertSame('C-123BCD', $piloto->cabezalUsual?->placa);

        $form = $this->get(route('cartas-porte.create'));
        $form->assertOk()
            ->assertSee('"value":"JUAN PEREZ"', false)
            ->assertSee('"value":"123456789"', false)
            ->assertSee('"value":"C-123BCD"', false);

        $this->put(route('catalogos.update', ['pilotos', $piloto]), [
            'nombre' => 'JUAN PEREZ',
            'licencia_numero' => '987654321',
            'cabezal_placa' => 'C-987ZYX',
            'activo' => '1',
        ])->assertRedirect(route('catalogos.index'));

        $piloto->refresh();

        $this->assertSame('987654321', $piloto->licencias()->first()?->numero);
        $this->assertSame('C-987ZYX', $piloto->cabezalUsual?->placa);

        $form = $this->get(route('cartas-porte.create'));
        $form->assertOk()
            ->assertSee('"value":"JUAN PEREZ"', false)
            ->assertSee('"value":"987654321"', false)
            ->assertSee('"value":"C-987ZYX"', false);

        $this->put(route('catalogos.update', ['pilotos', $piloto]), [
            'nombre' => 'JUAN PEREZ',
            'licencia_numero' => '987654321',
            'cabezal_placa' => 'C-987ZYX',
            'activo' => '0',
        ])->assertRedirect(route('catalogos.index'));

        $piloto->refresh();

        $this->assertFalse($piloto->activo);

        $this->get(route('catalogos.index'))
            ->assertOk()
            ->assertSee('JUAN PEREZ')
            ->assertSee('Inactivo');

        $this->get(route('cartas-porte.create'))
            ->assertOk()
            ->assertDontSee('"value":"JUAN PEREZ"', false);
    }

    public function test_create_form_includes_victor_in_driver_suggestions(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(PilotosPropiosSeeder::class);

        $response = $this->get(route('cartas-porte.create'));

        $response->assertOk();
        $response->assertSee('VÍCTOR FRANCISCO GUTIÉRREZ RODRÍGUEZ');
        $response->assertSee('2672 44584 1801');
        $response->assertSee('C-110BPM');
    }

    public function test_catalog_entries_can_be_edited_and_unused_entries_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('catalogos.store', 'procedencias'), [
            'nombre' => 'Procedencia nueva',
        ])->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseHas('procedencias', ['nombre' => 'Procedencia nueva']);

        $this->postJson(route('catalogos.quick-store', 'consignatarios'), [
            'nombre' => 'Cliente de Prueba',
        ])->assertOk()->assertJsonPath('value', 'Cliente de Prueba');

        $consignatario = Consignatario::where('nombre', 'Cliente de Prueba')->firstOrFail();

        $this->put(route('catalogos.update', ['consignatarios', $consignatario]), [
            'nombre' => 'Cliente Actualizado',
        ])->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseHas('consignatarios', ['nombre' => 'Cliente Actualizado']);

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'consignatario_id' => $consignatario->id,
            'consignatario_nombre' => 'Cliente Actualizado',
        ]))->assertRedirect();

        $this->delete(route('catalogos.destroy', ['consignatarios', $consignatario]))
            ->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseMissing('consignatarios', ['nombre' => 'Cliente Actualizado']);
        $carta = CartaPorte::firstOrFail()->fresh();
        $this->assertNull($carta->consignatario_id);
        $this->assertSame('Cliente Actualizado', $carta->consignatario_nombre);

        $pilotoLibre = Piloto::create(['nombre' => 'Piloto Libre']);

        $this->delete(route('catalogos.destroy', ['pilotos', $pilotoLibre]))
            ->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseMissing('pilotos', ['nombre' => 'Piloto Libre']);
    }

    public function test_nota_gasto_groups_cartas_by_same_bl_and_poliza(): void
    {
        $this->actingAs(User::factory()->create());

        $cliente = Consignatario::create(['nombre' => 'Cliente Facturacion']);
        $flete = ConceptoGasto::where('codigo', 'flete')->firstOrFail();
        $lavado = ConceptoGasto::where('codigo', 'lavado')->firstOrFail();
        $montacarga = ConceptoGasto::where('codigo', 'montacarga')->firstOrFail();

        TarifaCliente::create([
            'consignatario_id' => $cliente->id,
            'concepto_gasto_id' => $flete->id,
            'precio_unitario' => 3139,
            'incluir_por_defecto' => true,
            'activo' => true,
        ]);
        TarifaCliente::create([
            'consignatario_id' => $cliente->id,
            'concepto_gasto_id' => $lavado->id,
            'precio_unitario' => 280,
            'incluir_por_defecto' => true,
            'activo' => true,
        ]);

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'consignatario_id' => $cliente->id,
            'consignatario_nombre' => $cliente->nombre,
            'contenedor' => 'CONT-A',
            'bl' => 'BL-GRUPO',
            'poliza' => 'POL-GRUPO',
        ]))->assertRedirect();

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'numero_correlativo' => 2,
            'consignatario_id' => $cliente->id,
            'consignatario_nombre' => $cliente->nombre,
            'contenedor' => 'CONT-B',
            'bl' => 'BL-GRUPO',
            'poliza' => 'POL-GRUPO',
        ]))->assertRedirect();

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'numero_correlativo' => 3,
            'consignatario_id' => $cliente->id,
            'consignatario_nombre' => $cliente->nombre,
            'contenedor' => 'CONT-C',
            'bl' => 'BL-GRUPO',
            'poliza' => 'POL-DIFERENTE',
        ]))->assertRedirect();

        $carta = CartaPorte::where('contenedor', 'CONT-A')->firstOrFail();

        $preview = $this->get(route('facturacion.notas-gastos.desde-carta', $carta));
        $preview->assertOk();
        $preview->assertSee('CONT-A');
        $preview->assertSee('CONT-B');
        $preview->assertDontSee('CONT-C');
        $preview->assertSee('Valor flete Santo Tomas de Castilla hacia Guatemala por 2 contenedores conteniendo Mercaderia general, amparado con BL-BL-GRUPO Póliza-POL-GRUPO.');

        $response = $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), [
            'descripcion' => 'Texto escrito manualmente que no debe guardarse',
            'detalles' => [
                [
                    'concepto_gasto_id' => $flete->id,
                    'concepto_nombre' => 'Flete',
                    'precio_unitario' => 3139,
                    'cantidad' => 2,
                    'grupo' => 'subtotal',
                    'incluido' => 1,
                    'orden' => 10,
                ],
                [
                    'concepto_gasto_id' => $lavado->id,
                    'concepto_nombre' => 'Lavado',
                    'precio_unitario' => 280,
                    'cantidad' => 1,
                    'grupo' => 'subtotal',
                    'incluido' => 1,
                    'orden' => 20,
                ],
                [
                    'concepto_gasto_id' => $montacarga->id,
                    'concepto_nombre' => 'Montacarga',
                    'numero_factura' => 'FAC-2007124125',
                    'precio_unitario' => 1375,
                    'cantidad' => 1,
                    'grupo' => 'adicional',
                    'incluido' => 1,
                    'orden' => 70,
                ],
            ],
        ]);

        $nota = NotaGasto::firstOrFail();

        $response->assertRedirect(route('facturacion.notas-gastos.show', $nota));
        $this->assertSame(2, $nota->cantidad_contenedores);
        $this->assertSame('Valor flete Santo Tomas de Castilla hacia Guatemala por 2 contenedores conteniendo Mercaderia general, amparado con BL-BL-GRUPO Póliza-POL-GRUPO.', $nota->descripcion);
        $this->assertSame('6558.00', $nota->subtotal);
        $this->assertSame('7933.00', $nota->total);
        $this->assertCount(2, $nota->cartasPorte);
        $this->assertDatabaseHas('nota_gasto_detalles', [
            'nota_gasto_id' => $nota->id,
            'concepto_nombre' => 'Montacarga',
            'numero_factura' => 'FAC-2007124125',
        ]);
        $this->assertDatabaseHas('nota_gasto_detalles', [
            'nota_gasto_id' => $nota->id,
            'concepto_nombre' => 'Flete',
            'numero_factura' => null,
        ]);

        $this->get(route('facturacion.notas-gastos.show', $nota))
            ->assertOk()
            ->assertSeeInOrder([
                'Flete',
                'Lavado',
                'Q6,558.00',
                'FAC-2007124125',
                'Montacarga',
                'Q1,375.00',
                'Q7,933.00',
            ])
            ->assertDontSee('N/F')
            ->assertDontSee('Sin factura')
            ->assertDontSee('N/A');

        $this->get(route('facturacion.notas-gastos.edit', $nota))
            ->assertOk()
            ->assertSee('value="FAC-2007124125"', false);

        $this->get(route('facturacion.notas-gastos.imprimir', $nota))
            ->assertRedirect(route('facturacion.notas-gastos.show', $nota))
            ->assertSessionHas('error', 'Debe agregar el numero de factura SAT antes de imprimir la Nota de Gastos.');

        $this->get(route('facturacion.notas-gastos.desde-carta', $carta))
            ->assertRedirect(route('facturacion.notas-gastos.show', $nota));

        $this->put(route('facturacion.notas-gastos.facturar.update', $nota), [
            'fel_numero' => 'FEL-123',
        ])->assertRedirect(route('facturacion.notas-gastos.show', $nota))
            ->assertSessionHas('status', 'Numero de factura SAT guardado correctamente.');

        $nota->refresh();
        $this->assertSame(NotaGasto::ESTADO_FACTURADA, $nota->estado);
        $this->assertSame('FEL-123', $nota->fel_numero);

        $print = $this->get(route('facturacion.notas-gastos.imprimir', [$nota, 'copias' => 2]));
        $print->assertOk();
        $print->assertSee('<strong>Fecha</strong>', false);
        $print->assertSee('<strong>Cliente</strong>', false);
        $print->assertDontSee('<strong>B/L</strong>', false);
        $print->assertDontSee('<strong>Poliza</strong>', false);
        $print->assertDontSee('<strong>Contenedores</strong>', false);
        $print->assertDontSee('<strong>Estado</strong>', false);
        $print->assertSee('Factura SAT FEL-123');
        $print->assertSee('FAC-2007124125 &mdash;', false);
        $print->assertSee('Montacarga');
        $print->assertSee('Q1,375.00');
        $print->assertSee('Q6,558.00');
        $print->assertSee('Q7,933.00');
        $print->assertDontSee('Firma autorizada');
        $this->assertSame(2, substr_count($print->getContent(), 'Factura SAT FEL-123'));

        $this->get(route('facturacion.notas-gastos.imprimir', [$nota, 'copias' => 3]))
            ->assertRedirect();
    }

    public function test_nota_gasto_description_uses_singular_container_text(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'bl' => 'BL-UNICO',
            'poliza' => 'POL-UNICA',
        ]))->assertRedirect();

        $carta = CartaPorte::firstOrFail();

        $this->get(route('facturacion.notas-gastos.desde-carta', $carta))
            ->assertOk()
            ->assertSee('Valor flete Santo Tomas de Castilla hacia Guatemala por 1 contenedor conteniendo Mercaderia general, amparado con BL-BL-UNICO Póliza-POL-UNICA.');
    }

    public function test_cartas_porte_list_shows_nota_gasto_state_by_bl_and_poliza(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'bl' => 'BL-BOTON',
            'poliza' => 'POL-BOTON',
            'contenedor' => 'CONT-BOTON-1',
        ]))->assertRedirect();
        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'numero_correlativo' => 2,
            'bl' => 'BL-BOTON',
            'poliza' => 'POL-BOTON',
            'contenedor' => 'CONT-BOTON-2',
        ]))->assertRedirect();

        $response = $this->get(route('cartas-porte.index'));
        $response->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), 'Generar Nota'));
        $response->assertDontSee('Pendiente Factura SAT')
            ->assertDontSee('Facturada');

        $nota = NotaGasto::create([
            'fecha' => '2026-09-02',
            'consignatario_nombre' => 'Cliente de Prueba',
            'bl' => 'BL-BOTON',
            'poliza' => 'POL-BOTON',
            'cantidad_contenedores' => 2,
            'descripcion' => 'Nota pendiente de factura',
            'subtotal' => 100,
            'total' => 100,
            'estado' => NotaGasto::ESTADO_NOTA_GENERADA,
        ]);

        $response = $this->get(route('cartas-porte.index'));
        $response->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), 'Pendiente Factura SAT'));
        $response->assertDontSee('Facturada')
            ->assertDontSee('Generar Nota');

        $nota->update([
            'estado' => NotaGasto::ESTADO_FACTURADA,
            'fel_numero' => 'FEL-BOTON',
            'factura_fecha' => '2026-09-02',
        ]);

        $response = $this->get(route('cartas-porte.index'));
        $response->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), 'Facturada'));
        $response->assertDontSee('Pendiente Factura SAT')
            ->assertDontSee('Generar Nota');

        $nota->update([
            'estado' => NotaGasto::ESTADO_ANULADA,
            'fecha_anulacion' => now(),
            'motivo_anulacion' => 'Factura SAT anulada',
        ]);

        $response = $this->get(route('cartas-porte.index'));
        $response->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), 'Generar Nota'));
        $response->assertDontSee('Pendiente Factura SAT')
            ->assertDontSee('Facturada');
    }

    public function test_cartas_porte_index_batches_nota_gasto_status_queries(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (range(1, 12) as $numero) {
            CartaPorte::create($this->cartaPayload([
                'numero_correlativo' => $numero,
                'bl' => "BL-LOTE-$numero",
                'poliza' => "POL-LOTE-$numero",
                'contenedor' => "CONT-LOTE-$numero",
            ]));

            NotaGasto::create([
                'fecha' => '2026-09-02',
                'consignatario_nombre' => 'Cliente de Prueba',
                'bl' => "BL-LOTE-$numero",
                'poliza' => "POL-LOTE-$numero",
                'cantidad_contenedores' => 1,
                'descripcion' => "Nota lote $numero",
                'subtotal' => 100,
                'total' => 100,
                'estado' => $numero % 2 === 0 ? NotaGasto::ESTADO_FACTURADA : NotaGasto::ESTADO_NOTA_GENERADA,
                'fel_numero' => $numero % 2 === 0 ? "FEL-LOTE-$numero" : null,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('cartas-porte.index'));

        DB::disableQueryLog();

        $response->assertOk()
            ->assertSee('Pendiente Factura SAT')
            ->assertSee('Facturada');

        $this->assertLessThanOrEqual(7, count(DB::getQueryLog()));
    }

    public function test_nota_gasto_quantity_inputs_hide_unnecessary_decimal_zeroes(): void
    {
        $this->actingAs(User::factory()->create());

        $concepto = ConceptoGasto::where('codigo', 'flete')->firstOrFail();
        $nota = NotaGasto::create([
            'fecha' => '2026-09-02',
            'consignatario_nombre' => 'Cliente de Prueba',
            'bl' => 'BL-CANTIDAD',
            'poliza' => 'POL-CANTIDAD',
            'cantidad_contenedores' => 2,
            'descripcion' => 'Nota para cantidades',
            'subtotal' => 450,
            'total' => 450,
            'estado' => NotaGasto::ESTADO_NOTA_GENERADA,
        ]);

        $nota->detalles()->create([
            'concepto_gasto_id' => $concepto->id,
            'concepto_nombre' => 'Flete parcial',
            'precio_unitario' => 100,
            'cantidad' => 1.50,
            'total' => 150,
            'grupo' => 'subtotal',
            'incluido' => true,
            'orden' => 1,
        ]);
        $nota->detalles()->create([
            'concepto_gasto_id' => $concepto->id,
            'concepto_nombre' => 'Flete entero',
            'precio_unitario' => 100,
            'cantidad' => 3.00,
            'total' => 300,
            'grupo' => 'subtotal',
            'incluido' => true,
            'orden' => 2,
        ]);

        $response = $this->get(route('facturacion.notas-gastos.edit', $nota));

        $response->assertOk()
            ->assertSee('name="detalles[0][cantidad]" type="number" min="0" step="0.01" value="1.5"', false)
            ->assertSee('name="detalles[1][cantidad]" type="number" min="0" step="0.01" value="3"', false)
            ->assertDontSee('value="1.50"', false)
            ->assertDontSee('value="3.00"', false);
    }

    public function test_nota_gasto_is_created_and_displayed_without_public_correlativo(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'bl' => 'BL-SIN-NG',
            'poliza' => 'POL-SIN-NG',
        ]))->assertRedirect();

        $carta = CartaPorte::firstOrFail();
        $concepto = ConceptoGasto::where('codigo', 'flete')->firstOrFail();

        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), [
            'detalles' => [
                [
                    'concepto_gasto_id' => $concepto->id,
                    'concepto_nombre' => 'Flete',
                    'precio_unitario' => 100,
                    'cantidad' => 1,
                    'grupo' => 'subtotal',
                    'incluido' => 1,
                    'orden' => 1,
                ],
            ],
        ])->assertRedirect();

        $nota = NotaGasto::firstOrFail();

        $this->assertNull($nota->numero_correlativo);

        $this->put(route('facturacion.notas-gastos.facturar.update', $nota), [
            'fel_numero' => 'FEL-SIN-NG',
        ])->assertRedirect(route('facturacion.notas-gastos.show', $nota));

        foreach ([
            route('facturacion.notas-gastos.index'),
            route('facturacion.notas-gastos.show', $nota),
            route('facturacion.notas-gastos.edit', $nota),
            route('facturacion.notas-gastos.facturar', $nota),
            route('facturacion.notas-gastos.imprimir', [$nota, 'copias' => 1]),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('NG-')
                ->assertDontSee('No. Nota')
                ->assertDontSee('Numero</th>', false);
        }
    }

    public function test_nota_gasto_can_be_deleted_without_deleting_related_carta(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'bl' => 'BL-ELIMINAR',
            'poliza' => 'POL-ELIMINAR',
        ]))->assertRedirect();

        $carta = CartaPorte::firstOrFail();
        $concepto = ConceptoGasto::where('codigo', 'flete')->firstOrFail();
        $nota = NotaGasto::create([
            'fecha' => '2026-09-02',
            'consignatario_nombre' => 'Cliente de Prueba',
            'bl' => 'BL-ELIMINAR',
            'poliza' => 'POL-ELIMINAR',
            'cantidad_contenedores' => 1,
            'descripcion' => 'Nota para eliminar',
            'subtotal' => 100,
            'total' => 100,
            'estado' => NotaGasto::ESTADO_NOTA_GENERADA,
        ]);

        $detalle = $nota->detalles()->create([
            'concepto_gasto_id' => $concepto->id,
            'concepto_nombre' => 'Flete',
            'precio_unitario' => 100,
            'cantidad' => 1,
            'total' => 100,
            'grupo' => 'subtotal',
            'incluido' => true,
            'orden' => 1,
        ]);
        $nota->cartasPorte()->attach($carta->id, [
            'numero_correlativo' => $carta->numero_correlativo,
            'contenedor' => $carta->contenedor,
        ]);

        $this->delete(route('facturacion.notas-gastos.destroy', $nota))
            ->assertRedirect(route('facturacion.notas-gastos.index'))
            ->assertSessionHas('status', 'Nota de Gastos eliminada correctamente.');

        $this->assertDatabaseMissing('notas_gastos', ['id' => $nota->id]);
        $this->assertDatabaseMissing('nota_gasto_detalles', ['id' => $detalle->id]);
        $this->assertDatabaseMissing('carta_porte_nota_gasto', ['nota_gasto_id' => $nota->id]);
        $this->assertDatabaseHas('cartas_porte', ['id' => $carta->id]);
        $this->assertDatabaseHas('conceptos_gastos', ['id' => $concepto->id]);
    }

    public function test_facturada_nota_gasto_cannot_be_deleted_directly(): void
    {
        $this->actingAs(User::factory()->create());

        $nota = NotaGasto::create([
            'fecha' => '2026-09-02',
            'consignatario_nombre' => 'Cliente de Prueba',
            'bl' => 'BL-FACTURADA',
            'poliza' => 'POL-FACTURADA',
            'cantidad_contenedores' => 1,
            'descripcion' => 'Nota facturada',
            'subtotal' => 100,
            'total' => 100,
            'estado' => NotaGasto::ESTADO_FACTURADA,
        ]);

        $this->delete(route('facturacion.notas-gastos.destroy', $nota))
            ->assertRedirect(route('facturacion.notas-gastos.index'))
            ->assertSessionHas('error', 'La Nota de Gastos esta FACTURADA. Use la opcion Anular Nota si la factura correspondiente fue anulada.');

        $this->assertDatabaseHas('notas_gastos', ['id' => $nota->id]);
    }

    public function test_anulada_nota_gasto_can_be_permanently_deleted_without_deleting_related_carta(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'bl' => 'BL-ANULADA-ELIMINAR',
            'poliza' => 'POL-ANULADA-ELIMINAR',
        ]))->assertRedirect();

        $carta = CartaPorte::firstOrFail();
        $concepto = ConceptoGasto::where('codigo', 'flete')->firstOrFail();
        $nota = NotaGasto::create([
            'fecha' => '2026-09-02',
            'consignatario_nombre' => 'Cliente de Prueba',
            'bl' => 'BL-ANULADA-ELIMINAR',
            'poliza' => 'POL-ANULADA-ELIMINAR',
            'cantidad_contenedores' => 1,
            'descripcion' => 'Nota anulada para eliminar',
            'subtotal' => 100,
            'total' => 100,
            'estado' => NotaGasto::ESTADO_ANULADA,
            'fel_numero' => 'FEL-ANULADA-ELIMINAR',
            'fecha_anulacion' => now(),
        ]);

        $detalle = $nota->detalles()->create([
            'concepto_gasto_id' => $concepto->id,
            'concepto_nombre' => 'Flete',
            'precio_unitario' => 100,
            'cantidad' => 1,
            'total' => 100,
            'grupo' => 'subtotal',
            'incluido' => true,
            'orden' => 1,
        ]);
        $nota->cartasPorte()->attach($carta->id, [
            'numero_correlativo' => $carta->numero_correlativo,
            'contenedor' => $carta->contenedor,
        ]);

        $this->get(route('facturacion.notas-gastos.index'))
            ->assertOk()
            ->assertSee('ANULADA')
            ->assertSee('eliminar permanentemente esta Nota de Gastos anulada');

        $this->get(route('facturacion.notas-gastos.show', $nota))
            ->assertOk()
            ->assertSee('ANULADA')
            ->assertSee('Eliminar')
            ->assertSee('eliminar permanentemente esta Nota de Gastos anulada');

        $this->delete(route('facturacion.notas-gastos.destroy', $nota))
            ->assertRedirect(route('facturacion.notas-gastos.index'))
            ->assertSessionHas('status', 'Nota de Gastos anulada eliminada permanentemente.');

        $this->assertDatabaseMissing('notas_gastos', ['id' => $nota->id]);
        $this->assertDatabaseMissing('nota_gasto_detalles', ['id' => $detalle->id]);
        $this->assertDatabaseMissing('carta_porte_nota_gasto', ['nota_gasto_id' => $nota->id]);
        $this->assertDatabaseHas('cartas_porte', ['id' => $carta->id]);
        $this->assertDatabaseHas('conceptos_gastos', ['id' => $concepto->id]);

        $this->get(route('facturacion.notas-gastos.desde-carta', $carta))
            ->assertOk()
            ->assertSee('Valor flete Santo Tomas de Castilla hacia Guatemala por 1 contenedor');
    }

    public function test_facturada_nota_gasto_can_be_anulada_and_operation_can_be_regenerated(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'bl' => 'BL-ANULAR',
            'poliza' => 'POL-ANULAR',
        ]))->assertRedirect();

        $carta = CartaPorte::firstOrFail();
        $concepto = ConceptoGasto::where('codigo', 'flete')->firstOrFail();
        $nota = NotaGasto::create([
            'fecha' => '2026-09-02',
            'consignatario_nombre' => 'Cliente de Prueba',
            'bl' => 'BL-ANULAR',
            'poliza' => 'POL-ANULAR',
            'cantidad_contenedores' => 1,
            'descripcion' => 'Nota facturada para anular',
            'subtotal' => 100,
            'total' => 100,
            'estado' => NotaGasto::ESTADO_FACTURADA,
            'fel_numero' => 'FEL-ANULADA',
            'factura_fecha' => '2026-09-02',
        ]);
        $nota->detalles()->create([
            'concepto_gasto_id' => $concepto->id,
            'concepto_nombre' => 'Flete',
            'precio_unitario' => 100,
            'cantidad' => 1,
            'total' => 100,
            'grupo' => 'subtotal',
            'incluido' => true,
            'orden' => 1,
        ]);
        $nota->cartasPorte()->attach($carta->id, [
            'numero_correlativo' => $carta->numero_correlativo,
            'contenedor' => $carta->contenedor,
        ]);

        $this->put(route('facturacion.notas-gastos.anular', $nota), [
            'motivo_anulacion' => 'Factura SAT anulada',
        ])->assertRedirect(route('facturacion.notas-gastos.show', $nota))
            ->assertSessionHas('status', 'Nota de Gastos anulada correctamente.');

        $nota->refresh();

        $this->assertSame(NotaGasto::ESTADO_ANULADA, $nota->estado);
        $this->assertFalse($nota->esta_facturada);
        $this->assertSame('FEL-ANULADA', $nota->fel_numero);
        $this->assertSame('Factura SAT anulada', $nota->motivo_anulacion);
        $this->assertNotNull($nota->fecha_anulacion);
        $this->assertDatabaseHas('cartas_porte', ['id' => $carta->id]);

        $this->get(route('facturacion.notas-gastos.desde-carta', $carta))
            ->assertOk()
            ->assertSee('Valor flete Santo Tomas de Castilla hacia Guatemala por 1 contenedor');

        $this->post(route('facturacion.notas-gastos.store-desde-carta', $carta), [
            'detalles' => [
                [
                    'concepto_gasto_id' => $concepto->id,
                    'concepto_nombre' => 'Flete',
                    'precio_unitario' => 100,
                    'cantidad' => 1,
                    'grupo' => 'subtotal',
                    'incluido' => 1,
                    'orden' => 1,
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('notas_gastos', [
            'id' => $nota->id,
            'estado' => NotaGasto::ESTADO_ANULADA,
            'fel_numero' => 'FEL-ANULADA',
        ]);
        $this->assertDatabaseHas('notas_gastos', [
            'bl' => 'BL-ANULAR',
            'poliza' => 'POL-ANULAR',
            'estado' => NotaGasto::ESTADO_NOTA_GENERADA,
        ]);
        $this->assertDatabaseCount('cartas_porte', 1);
    }

    private function cartaPayload(array $overrides = []): array
    {
        return array_merge([
            'numero_correlativo' => 1,
            'fecha' => '2026-06-09',
            'consignatario_nombre' => 'Cliente de Prueba',
            'procedencia_nombre' => 'Santo Tomas de Castilla',
            'destino' => 'Guatemala',
            'poliza' => 'POL-001',
            'id_documento' => 'ID-001',
            'da' => 'DA-001',
            'mi' => 'MI-001',
            'contacto' => 'Juan Perez',
            'telefono' => '5555-5555',
            'contenedor' => 'CONT-001',
            'bultos' => '12',
            'contenido' => 'Mercaderia general',
            'peso_kls' => '1500',
            'vapor' => 'Vapor Uno',
            'fecha_vapor' => '2026-06-10',
            'bl' => 'BL-001',
            'piloto_nombre' => 'Piloto de Prueba',
            'cabezal_placa' => 'P-123ABC',
            'licencia_numero' => 'LIC-001',
        ], $overrides);
    }
}
