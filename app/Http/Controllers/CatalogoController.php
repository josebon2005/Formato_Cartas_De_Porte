<?php

namespace App\Http\Controllers;

use App\Models\Cabezal;
use App\Models\Consignatario;
use App\Models\Licencia;
use App\Models\Piloto;
use App\Models\Procedencia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogoController extends Controller
{
    public function index()
    {
        return view('catalogos.index', [
            'catalogos' => $this->catalogosConDatos(),
        ]);
    }

    public function edit(string $catalogo, int $id)
    {
        $config = $this->config($catalogo);
        $registro = $config['model']::findOrFail($id);

        return view('catalogos.edit', compact('catalogo', 'config', 'registro'));
    }

    public function create(string $catalogo)
    {
        $config = $this->config($catalogo);
        $registro = new $config['model'];

        return view('catalogos.create', compact('catalogo', 'config', 'registro'));
    }

    public function store(Request $request, string $catalogo)
    {
        $config = $this->config($catalogo);

        $validated = $request->validate($this->rules($config));
        $config['model']::create($validated);

        return redirect()
            ->route('catalogos.index')
            ->with('status', $config['singular'].' agregado correctamente.');
    }

    public function update(Request $request, string $catalogo, int $id)
    {
        $config = $this->config($catalogo);
        $registro = $config['model']::findOrFail($id);

        $validated = $request->validate($this->rules($config, $registro));
        $registro->update($validated);

        return redirect()
            ->route('catalogos.index')
            ->with('status', $config['singular'].' actualizado correctamente.');
    }

    public function destroy(string $catalogo, int $id)
    {
        $config = $this->config($catalogo);
        $registro = $config['model']::findOrFail($id);

        if ($this->estaEnUso($registro, $config['relation'])) {
            return redirect()
                ->route('catalogos.index')
                ->with('error', 'No se puede eliminar '.$config['singular'].' porque ya esta usado en una carta de porte.');
        }

        $registro->delete();

        return redirect()
            ->route('catalogos.index')
            ->with('status', $config['singular'].' eliminado correctamente.');
    }

    private function catalogosConDatos(): array
    {
        $catalogos = [];

        foreach ($this->configs() as $key => $config) {
            $catalogos[$key] = [
                ...$config,
                'items' => $config['model']::query()
                    ->withCount($config['relation'])
                    ->orderBy($config['main'])
                    ->get(),
            ];
        }

        return $catalogos;
    }

    private function rules(array $config, ?Model $registro = null): array
    {
        $rules = [
            $config['main'] => [
                'required',
                'string',
                'max:255',
                Rule::unique($config['table'], $config['main'])->ignore($registro?->id),
            ],
        ];

        if (($config['extra'] ?? null) === 'descripcion') {
            $rules['descripcion'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    private function estaEnUso(Model $registro, string $relation): bool
    {
        return $registro->{$relation}()->exists();
    }

    private function config(string $catalogo): array
    {
        abort_unless(array_key_exists($catalogo, $this->configs()), 404);

        return $this->configs()[$catalogo];
    }

    private function configs(): array
    {
        return [
            'consignatarios' => [
                'titulo' => 'Consignatarios',
                'singular' => 'Consignatario',
                'model' => Consignatario::class,
                'table' => 'consignatarios',
                'main' => 'nombre',
                'main_label' => 'Nombre',
                'relation' => 'cartasPorte',
            ],
            'procedencias' => [
                'titulo' => 'Procedencias',
                'singular' => 'Procedencia',
                'model' => Procedencia::class,
                'table' => 'procedencias',
                'main' => 'nombre',
                'main_label' => 'Nombre',
                'relation' => 'cartasPorte',
            ],
            'pilotos' => [
                'titulo' => 'Pilotos',
                'singular' => 'Piloto',
                'model' => Piloto::class,
                'table' => 'pilotos',
                'main' => 'nombre',
                'main_label' => 'Nombre',
                'relation' => 'cartasPorte',
            ],
            'cabezales' => [
                'titulo' => 'Cabezales / Placas',
                'singular' => 'Cabezal / placa',
                'model' => Cabezal::class,
                'table' => 'cabezales',
                'main' => 'placa',
                'main_label' => 'Placa',
                'extra' => 'descripcion',
                'extra_label' => 'Descripcion',
                'relation' => 'cartasPorte',
            ],
            'licencias' => [
                'titulo' => 'Licencias',
                'singular' => 'Licencia',
                'model' => Licencia::class,
                'table' => 'licencias',
                'main' => 'numero',
                'main_label' => 'Numero',
                'relation' => 'cartasPorte',
            ],
        ];
    }
}
