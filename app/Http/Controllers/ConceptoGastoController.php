<?php

namespace App\Http\Controllers;

use App\Models\ConceptoGasto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConceptoGastoController extends Controller
{
    public function index()
    {
        $conceptos = ConceptoGasto::orderBy('orden')->orderBy('nombre')->get();

        return view('facturacion.conceptos_gastos.index', compact('conceptos'));
    }

    public function create()
    {
        return view('facturacion.conceptos_gastos.create', [
            'concepto' => new ConceptoGasto([
                'tipo_calculo' => 'fijo',
                'grupo' => 'subtotal',
                'activo' => true,
                'orden' => ((int) ConceptoGasto::max('orden')) + 1,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        ConceptoGasto::create($this->validatedData($request));

        return redirect()
            ->route('facturacion.conceptos-gastos.index')
            ->with('status', 'Concepto de gasto creado correctamente.');
    }

    public function edit(ConceptoGasto $conceptosGasto)
    {
        return view('facturacion.conceptos_gastos.edit', [
            'concepto' => $conceptosGasto,
        ]);
    }

    public function update(Request $request, ConceptoGasto $conceptosGasto)
    {
        $conceptosGasto->update($this->validatedData($request, $conceptosGasto));

        return redirect()
            ->route('facturacion.conceptos-gastos.index')
            ->with('status', 'Concepto de gasto actualizado correctamente.');
    }

    public function destroy(ConceptoGasto $conceptosGasto)
    {
        $conceptosGasto->delete();

        return redirect()
            ->route('facturacion.conceptos-gastos.index')
            ->with('status', 'Concepto de gasto eliminado correctamente.');
    }

    private function validatedData(Request $request, ?ConceptoGasto $concepto = null): array
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('conceptos_gastos', 'nombre')->ignore($concepto?->id),
            ],
            'codigo' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('conceptos_gastos', 'codigo')->ignore($concepto?->id),
            ],
            'tipo_calculo' => ['required', Rule::in(['fijo', 'por_contenedor'])],
            'grupo' => ['required', Rule::in(['subtotal', 'adicional'])],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        return [
            ...$validated,
            'codigo' => $validated['codigo'] ? trim($validated['codigo']) : null,
            'orden' => $validated['orden'] ?? 0,
            'activo' => $request->boolean('activo'),
        ];
    }
}
