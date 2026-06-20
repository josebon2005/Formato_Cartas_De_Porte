<?php

namespace App\Http\Controllers;

use App\Models\Cabezal;
use App\Models\CartaPorte;
use App\Models\Consignatario;
use App\Models\Licencia;
use App\Models\Piloto;
use App\Models\Procedencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartaPorteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cartas = CartaPorte::query()
            ->with(['consignatario', 'procedencia', 'piloto'])
            ->when($request->filled('fecha'), fn ($query) => $query->whereDate('fecha', $request->fecha))
            ->when($request->filled('consignatario'), function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $subQuery
                        ->where('consignatario_nombre', 'like', '%'.$request->consignatario.'%')
                        ->orWhereHas('consignatario', fn ($catalogQuery) => $catalogQuery->where('nombre', 'like', '%'.$request->consignatario.'%'));
                });
            })
            ->when($request->filled('bl'), fn ($query) => $query->where('bl', 'like', '%'.$request->bl.'%'))
            ->when($request->filled('poliza'), fn ($query) => $query->where('poliza', 'like', '%'.$request->poliza.'%'))
            ->latest('fecha')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('cartas_porte.index', compact('cartas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ultimaCarta = CartaPorte::with(['consignatario', 'procedencia', 'piloto', 'cabezal', 'licencia'])
            ->latest('id')
            ->first();

        return view('cartas_porte.create', [
            'cartaPorte' => $this->newCartaPorte($ultimaCarta),
            'cargadaDesdeUltima' => (bool) $ultimaCarta,
            ...$this->catalogs(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $cartaPorte = CartaPorte::create($data);

        return redirect()
            ->route('cartas-porte.imprimir', [$cartaPorte, 'autoprint' => 1])
            ->with('status', 'Carta de porte creada correctamente. Lista para imprimir.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CartaPorte $cartaPorte)
    {
        $cartaPorte->load(['consignatario', 'procedencia', 'piloto', 'cabezal', 'licencia']);

        return view('cartas_porte.show', compact('cartaPorte'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CartaPorte $cartaPorte)
    {
        $cartaPorte->load(['consignatario', 'procedencia', 'piloto', 'cabezal', 'licencia']);

        return view('cartas_porte.edit', [
            'cartaPorte' => $cartaPorte,
            ...$this->catalogs(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CartaPorte $cartaPorte)
    {
        $data = $this->validatedData($request, $cartaPorte);
        $cartaPorte->update($data);

        return redirect()
            ->route('cartas-porte.show', $cartaPorte)
            ->with('status', 'Carta de porte actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartaPorte $cartaPorte)
    {
        $cartaPorte->delete();

        return redirect()
            ->route('cartas-porte.index')
            ->with('status', 'Carta de porte eliminada correctamente.');
    }

    public function imprimir(CartaPorte $cartaPorte)
    {
        $cartaPorte->load(['consignatario', 'procedencia', 'piloto', 'cabezal', 'licencia']);

        return view('cartas_porte.print', compact('cartaPorte'));
    }

    private function validatedData(Request $request, ?CartaPorte $cartaPorte = null): array
    {
        $validated = $request->validate([
            'numero_correlativo' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('cartas_porte', 'numero_correlativo')->ignore($cartaPorte?->id),
            ],
            'fecha' => ['required', 'date'],
            'consignatario_id' => ['nullable', 'exists:consignatarios,id'],
            'consignatario_nombre' => ['required_without:consignatario_id', 'nullable', 'string', 'max:255'],
            'procedencia_id' => ['nullable', 'exists:procedencias,id'],
            'procedencia_nombre' => ['required_without:procedencia_id', 'nullable', 'string', 'max:255'],
            'destino' => ['nullable', 'string', 'max:255'],
            'poliza' => ['nullable', 'string', 'max:255'],
            'id_documento' => ['nullable', 'string', 'max:255'],
            'da' => ['nullable', 'string', 'max:255'],
            'mi' => ['nullable', 'string', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'contenedor' => ['nullable', 'string', 'max:255'],
            'bultos' => ['nullable', 'string', 'max:255'],
            'contenido' => ['nullable', 'string'],
            'peso_kls' => ['nullable', 'string', 'max:255'],
            'vapor' => ['nullable', 'string', 'max:255'],
            'fecha_vapor' => ['nullable', 'date'],
            'bl' => ['nullable', 'string', 'max:255'],
            'piloto_id' => ['nullable', 'exists:pilotos,id'],
            'piloto_nombre' => ['required_without:piloto_id', 'nullable', 'string', 'max:255'],
            'cabezal_id' => ['nullable', 'exists:cabezales,id'],
            'cabezal_placa' => ['required_without:cabezal_id', 'nullable', 'string', 'max:255'],
            'licencia_id' => ['nullable', 'exists:licencias,id'],
            'licencia_numero' => ['required_without:licencia_id', 'nullable', 'string', 'max:255'],
        ]);

        $pilotoId = $this->catalogId($validated['piloto_id'] ?? null);

        return [
            'numero_correlativo' => $validated['numero_correlativo'],
            'fecha' => $validated['fecha'],
            'consignatario_id' => $this->catalogId($validated['consignatario_id'] ?? null),
            'consignatario_nombre' => $this->catalogText($validated['consignatario_nombre'] ?? null),
            'procedencia_id' => $this->catalogId($validated['procedencia_id'] ?? null),
            'procedencia_nombre' => $this->catalogText($validated['procedencia_nombre'] ?? null),
            'destino' => $validated['destino'] ?? null,
            'poliza' => $validated['poliza'] ?? null,
            'id_documento' => $validated['id_documento'] ?? null,
            'da' => $validated['da'] ?? null,
            'mi' => $validated['mi'] ?? null,
            'contacto' => $validated['contacto'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'contenedor' => $validated['contenedor'] ?? null,
            'bultos' => $validated['bultos'] ?? null,
            'contenido' => $validated['contenido'] ?? null,
            'peso_kls' => $validated['peso_kls'] ?? null,
            'vapor' => $validated['vapor'] ?? null,
            'fecha_vapor' => $validated['fecha_vapor'] ?? null,
            'bl' => $validated['bl'] ?? null,
            'piloto_id' => $pilotoId,
            'piloto_nombre' => $this->catalogText($validated['piloto_nombre'] ?? null),
            'cabezal_id' => $this->catalogId($validated['cabezal_id'] ?? null),
            'cabezal_placa' => $this->catalogText($validated['cabezal_placa'] ?? null),
            'licencia_id' => $this->catalogId($validated['licencia_id'] ?? null),
            'licencia_numero' => $this->catalogText($validated['licencia_numero'] ?? null),
        ];
    }

    private function catalogId(?string $id): ?int
    {
        return $id ? (int) $id : null;
    }

    private function catalogText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function nextCorrelativo(): int
    {
        return ((int) CartaPorte::max('numero_correlativo')) + 1;
    }

    private function newCartaPorte(?CartaPorte $base = null): CartaPorte
    {
        $data = [
            'numero_correlativo' => $this->nextCorrelativo(),
            'fecha' => now(),
        ];

        if ($base) {
            $data += [
                'consignatario_id' => $base->consignatario_id,
                'consignatario_nombre' => $base->consignatario_texto,
                'procedencia_id' => $base->procedencia_id,
                'procedencia_nombre' => $base->procedencia_texto,
                'destino' => $base->destino,
                'poliza' => $base->poliza,
                'id_documento' => $base->id_documento,
                'da' => $base->da,
                'mi' => $base->mi,
                'contacto' => $base->contacto,
                'telefono' => $base->telefono,
                'bultos' => $base->bultos,
                'contenido' => $base->contenido,
                'peso_kls' => $base->peso_kls,
                'vapor' => $base->vapor,
                'fecha_vapor' => $base->fecha_vapor,
                'bl' => $base->bl,
                'piloto_id' => $base->piloto_id,
                'piloto_nombre' => $base->piloto_texto,
                'cabezal_id' => $base->cabezal_id,
                'cabezal_placa' => $base->cabezal_texto,
                'licencia_id' => $base->licencia_id,
                'licencia_numero' => $base->licencia_texto,
            ];
        }

        $cartaPorte = new CartaPorte($data);

        if ($base) {
            $cartaPorte->setRelation('consignatario', $base->consignatario);
            $cartaPorte->setRelation('procedencia', $base->procedencia);
            $cartaPorte->setRelation('piloto', $base->piloto);
            $cartaPorte->setRelation('cabezal', $base->cabezal);
            $cartaPorte->setRelation('licencia', $base->licencia);
        }

        return $cartaPorte;
    }

    private function catalogs(): array
    {
        return [
            'consignatarios' => Consignatario::orderBy('nombre')->get(),
            'procedencias' => Procedencia::orderBy('nombre')->get(),
            'pilotos' => Piloto::with(['licencias', 'cabezalUsual'])->orderBy('nombre')->get(),
            'cabezales' => Cabezal::orderBy('placa')->get(),
            'licencias' => Licencia::orderBy('numero')->get(),
        ];
    }
}
