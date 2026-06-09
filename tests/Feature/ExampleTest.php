<?php

namespace Tests\Feature;

use App\Models\CartaPorte;
use App\Models\Consignatario;
use App\Models\Piloto;
use App\Models\User;
use Database\Seeders\PilotosPropiosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    public function test_carta_porte_can_be_created_and_prints_two_copies_without_duplicate_record(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('cartas-porte.store'), $this->cartaPayload());

        $carta = CartaPorte::firstOrFail();

        $response->assertRedirect(route('cartas-porte.imprimir', [$carta, 'autoprint' => 1]));
        $this->assertDatabaseCount('cartas_porte', 1);
        $this->assertDatabaseHas('consignatarios', ['nombre' => 'Cliente de Prueba']);
        $this->assertDatabaseHas('pilotos', ['nombre' => 'Piloto de Prueba']);

        $print = $this->get(route('cartas-porte.imprimir', $carta));

        $print->assertOk();
        $this->assertSame(2, substr_count($print->getContent(), '"CARTA DE PORTE"'));
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
            'consignatario_id' => $original->consignatario_id,
            'contenedor' => 'CONT-001',
        ]);
        $this->assertDatabaseHas('cartas_porte', [
            'numero_correlativo' => 2,
            'poliza' => 'POL-001',
            'bl' => 'BL-001',
            'contenido' => 'Mercaderia general',
            'peso_kls' => '1500',
            'contenedor' => 'CONT-002',
        ]);
        $this->assertDatabaseHas('consignatarios', ['nombre' => 'Cliente Nuevo']);
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
        $this->assertDatabaseCount('pilotos', 9);
    }

    public function test_known_driver_allows_manual_license_and_plate_changes_when_saving(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seed(PilotosPropiosSeeder::class);

        $this->post(route('cartas-porte.store'), $this->cartaPayload([
            'piloto_nombre' => 'ENRIQUE MANOLO REYES ORELLANA',
            'cabezal_placa' => 'C-999ZZZ',
            'licencia_numero' => 'LIC-MANUAL',
        ]))->assertRedirect();

        $carta = CartaPorte::firstOrFail();

        $this->assertSame('ENRIQUE MANOLO REYES ORELLANA', $carta->piloto->nombre);
        $this->assertSame('C-999ZZZ', $carta->cabezal->placa);
        $this->assertSame('LIC-MANUAL', $carta->licencia->numero);
        $this->assertDatabaseHas('cabezales', ['placa' => 'C-491BXM']);
        $this->assertDatabaseHas('licencias', ['numero' => '3918 86266 1801']);
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
        $this->test_carta_porte_can_be_created_and_prints_two_copies_without_duplicate_record();

        $this->post(route('catalogos.store', 'procedencias'), [
            'nombre' => 'Procedencia nueva',
        ])->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseHas('procedencias', ['nombre' => 'Procedencia nueva']);

        $consignatario = Consignatario::firstOrFail();

        $this->put(route('catalogos.update', ['consignatarios', $consignatario]), [
            'nombre' => 'Cliente Actualizado',
        ])->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseHas('consignatarios', ['nombre' => 'Cliente Actualizado']);

        $this->delete(route('catalogos.destroy', ['consignatarios', $consignatario]))
            ->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseHas('consignatarios', ['nombre' => 'Cliente Actualizado']);

        $pilotoLibre = Piloto::create(['nombre' => 'Piloto Libre']);

        $this->delete(route('catalogos.destroy', ['pilotos', $pilotoLibre]))
            ->assertRedirect(route('catalogos.index'));

        $this->assertDatabaseMissing('pilotos', ['nombre' => 'Piloto Libre']);
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
