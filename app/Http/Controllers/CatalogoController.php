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
        $registro = $config['model']::query()
            ->when($catalogo === 'pilotos', fn ($query) => $query->with(['licencias', 'cabezalUsual']))
            ->findOrFail($id);

        return view('catalogos.edit', [
            'catalogo' => $catalogo,
            'config' => $config,
            'registro' => $registro,
            ...$this->catalogFormData(),
        ]);
    }

    public function create(string $catalogo)
    {
        $config = $this->config($catalogo);
        $registro = new $config['model'];

        return view('catalogos.create', [
            'catalogo' => $catalogo,
            'config' => $config,
            'registro' => $registro,
            ...$this->catalogFormData(),
        ]);
    }

    public function store(Request $request, string $catalogo)
    {
        $config = $this->config($catalogo);

        $validated = $request->validate($this->rules($config));
        $registro = $config['model']::create($this->mainData($validated, $config));

        if ($catalogo === 'pilotos') {
            $this->syncPilotoDetails($registro, $validated['licencia_numero'] ?? null, $validated['cabezal_placa'] ?? null);
        }

        return redirect()
            ->route('catalogos.index')
            ->with('status', $config['singular'].' agregado correctamente.');
    }

    public function quickStore(Request $request, string $catalogo)
    {
        $config = $this->config($catalogo);

        $validated = $request->validate([
            $config['main'] => ['required', 'string', 'max:255'],
        ]);

        $value = trim($validated[$config['main']]);
        $registro = $config['model']::firstOrCreate([$config['main'] => $value]);

        return response()->json([
            'id' => $registro->id,
            'value' => $registro->{$config['main']},
            'message' => $config['singular'].' guardado en datos.',
        ]);
    }

    public function update(Request $request, string $catalogo, int $id)
    {
        $config = $this->config($catalogo);
        $registro = $config['model']::findOrFail($id);

        $validated = $request->validate($this->rules($config, $registro));
        $registro->update($this->mainData($validated, $config));

        if ($catalogo === 'pilotos') {
            $this->syncPilotoDetails($registro, $validated['licencia_numero'] ?? null, $validated['cabezal_placa'] ?? null);
        }

        return redirect()
            ->route('catalogos.index')
            ->with('status', $config['singular'].' actualizado correctamente.');
    }

    public function destroy(string $catalogo, int $id)
    {
        $config = $this->config($catalogo);
        $registro = $config['model']::findOrFail($id);

        $registro->delete();

        return redirect()
            ->route('catalogos.index')
            ->with('status', $config['singular'].' eliminado de datos. Las cartas ya creadas conservaran la informacion registrada.');
    }

    private function catalogosConDatos(): array
    {
        $catalogos = [];

        foreach ($this->configs() as $key => $config) {
            $query = $config['model']::query()
                ->withCount($config['relation'])
                ->orderBy($config['main']);

            if ($key === 'pilotos') {
                $query->with(['licencias', 'cabezalUsual']);
            }

            $catalogos[$key] = [
                ...$config,
                'items' => $query->get(),
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

        if (($config['extras'] ?? null) === 'piloto_detalles') {
            $rules['licencia_numero'] = ['nullable', 'string', 'max:255'];
            $rules['cabezal_placa'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    private function mainData(array $validated, array $config): array
    {
        return collect($validated)
            ->only(array_filter([$config['main'], $config['extra'] ?? null]))
            ->all();
    }

    private function syncPilotoDetails(Piloto $piloto, ?string $licenciaNumero, ?string $cabezalPlaca): void
    {
        $cabezalPlaca = trim((string) $cabezalPlaca);
        $piloto->forceFill([
            'cabezal_id' => $cabezalPlaca !== ''
                ? Cabezal::firstOrCreate(['placa' => $cabezalPlaca])->id
                : null,
        ])->save();

        $licenciaNumero = trim((string) $licenciaNumero);
        $licenciaActual = $piloto->licencias()->oldest('id')->first();

        if ($licenciaNumero === '') {
            $licenciaActual?->update(['piloto_id' => null]);

            return;
        }

        if ($licenciaActual && $licenciaActual->numero !== $licenciaNumero) {
            $licenciaActual->update(['piloto_id' => null]);
        }

        Licencia::firstOrCreate(['numero' => $licenciaNumero])->update([
            'piloto_id' => $piloto->id,
        ]);
    }

    private function catalogFormData(): array
    {
        return [
            'cabezales' => Cabezal::orderBy('placa')->get(),
        ];
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
                'extras' => 'piloto_detalles',
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
