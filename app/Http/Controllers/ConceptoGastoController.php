<?php

namespace App\Http\Controllers;

use App\Models\ConceptoGasto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConceptoGastoController extends Controller
{
    public function index()
    {
        $conceptos = ConceptoGasto::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('facturacion.conceptos_gastos.index', compact('conceptos'));
    }

    public function create()
    {
        return view('facturacion.conceptos_gastos.create', [
            'concepto' => new ConceptoGasto,
        ]);
    }

    public function store(Request $request)
    {
        ConceptoGasto::create([
            ...$this->validatedData($request),
            'codigo' => null,
            'tipo_calculo' => 'fijo',
            'grupo' => 'subtotal',
            'activo' => true,
            'orden' => ((int) ConceptoGasto::max('orden')) + 1,
        ]);

        return redirect()
            ->route('facturacion.conceptos-gastos.index')
            ->with('status', 'Cobro creado correctamente.');
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
        if ($conceptosGasto->notaGastoDetalles()->exists() || $conceptosGasto->tarifasClientes()->exists()) {
            $conceptosGasto->update(['activo' => false]);

            return redirect()
                ->route('facturacion.conceptos-gastos.index')
                ->with('status', 'Cobro desactivado correctamente. Las Notas de Gastos anteriores conservaran su historial.');
        }

        $conceptosGasto->delete();

        return redirect()
            ->route('facturacion.conceptos-gastos.index')
            ->with('status', 'Cobro eliminado correctamente.');
    }

    private function validatedData(Request $request, ?ConceptoGasto $concepto = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('conceptos_gastos', 'nombre')->ignore($concepto?->id),
            ],
        ]);
    }
}
