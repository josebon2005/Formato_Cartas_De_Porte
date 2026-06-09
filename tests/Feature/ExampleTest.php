<?php

namespace Tests\Feature;

use App\Models\CartaPorte;
use App\Models\Consignatario;
use App\Models\Piloto;
use App\Models\User;
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

        $response = $this->post(route('cartas-porte.store'), [
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
        ]);

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
}
