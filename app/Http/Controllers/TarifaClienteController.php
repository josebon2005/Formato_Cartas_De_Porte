<?php

namespace App\Http\Controllers;

use App\Models\ConceptoGasto;
use App\Models\Consignatario;
use App\Models\TarifaCliente;
use Illuminate\Http\Request;

class TarifaClienteController extends Controller
{
    public function index()
    {
        $clientes = Consignatario::withCount('tarifasClientes')
            ->orderBy('nombre')
            ->get();

        return view('facturacion.tarifas_clientes.index', compact('clientes'));
    }

    public function edit(Consignatario $consignatario)
    {
        $conceptos = ConceptoGasto::orderBy('orden')->orderBy('nombre')->get();
        $tarifas = $consignatario->tarifasClientes()
            ->with('conceptoGasto')
            ->get()
            ->keyBy('concepto_gasto_id');

        return view('facturacion.tarifas_clientes.edit', compact('consignatario', 'conceptos', 'tarifas'));
    }

    public function update(Request $request, Consignatario $consignatario)
    {
        $validated = $request->validate([
            'tarifas' => ['nullable', 'array'],
            'tarifas.*.concepto_gasto_id' => ['required', 'exists:conceptos_gastos,id'],
            'tarifas.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'tarifas.*.cantidad_default' => ['nullable', 'numeric', 'min:0'],
            'tarifas.*.incluir_por_defecto' => ['nullable', 'boolean'],
            'tarifas.*.activo' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['tarifas'] ?? [] as $tarifaData) {
            TarifaCliente::updateOrCreate(
                [
                    'consignatario_id' => $consignatario->id,
                    'concepto_gasto_id' => $tarifaData['concepto_gasto_id'],
                ],
                [
                    'precio_unitario' => $tarifaData['precio_unitario'] ?? 0,
                    'cantidad_default' => $tarifaData['cantidad_default'] ?? null,
                    'incluir_por_defecto' => (bool) ($tarifaData['incluir_por_defecto'] ?? false),
                    'activo' => (bool) ($tarifaData['activo'] ?? false),
                ]
            );
        }

        return redirect()
            ->route('facturacion.tarifas-clientes.index')
            ->with('status', 'Tarifas del cliente actualizadas correctamente.');
    }
}
