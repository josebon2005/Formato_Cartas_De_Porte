@extends('layouts.app')

@section('title', 'Tarifas de ' . $consignatario->nombre)

@section('content')
    <div class="page-head">
        <div>
            <h1>Tarifas de {{ $consignatario->nombre }}</h1>
            <p class="subtle">Estos precios se usaran como base al generar nuevas notas.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('facturacion.tarifas-clientes.index') }}">Volver a tarifas</a>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('facturacion.tarifas-clientes.update', $consignatario) }}">
            @csrf
            @method('PUT')

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Activo</th>
                            <th>Incluir</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Grupo</th>
                            <th>Precio unitario</th>
                            <th>Cantidad fija</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conceptos as $index => $concepto)
                            @php
                                $tarifa = $tarifas->get($concepto->id);
                            @endphp
                            <tr>
                                <td>
                                    <input name="tarifas[{{ $index }}][activo]" type="hidden" value="0">
                                    <input name="tarifas[{{ $index }}][activo]" type="checkbox" value="1" {{ old("tarifas.$index.activo", $tarifa?->activo ?? true) ? 'checked' : '' }} style="min-height: auto; width: auto;">
                                </td>
                                <td>
                                    <input name="tarifas[{{ $index }}][incluir_por_defecto]" type="hidden" value="0">
                                    <input name="tarifas[{{ $index }}][incluir_por_defecto]" type="checkbox" value="1" {{ old("tarifas.$index.incluir_por_defecto", $tarifa?->incluir_por_defecto ?? true) ? 'checked' : '' }} style="min-height: auto; width: auto;">
                                </td>
                                <td>
                                    {{ $concepto->nombre }}
                                    <input name="tarifas[{{ $index }}][concepto_gasto_id]" type="hidden" value="{{ $concepto->id }}">
                                </td>
                                <td>{{ str_replace('_', ' ', $concepto->tipo_calculo) }}</td>
                                <td>{{ ucfirst($concepto->grupo) }}</td>
                                <td>
                                    <input name="tarifas[{{ $index }}][precio_unitario]" type="number" min="0" step="0.01" value="{{ old("tarifas.$index.precio_unitario", number_format((float) ($tarifa?->precio_unitario ?? 0), 2, '.', '')) }}">
                                    @error("tarifas.$index.precio_unitario") <div class="error">{{ $message }}</div> @enderror
                                </td>
                                <td>
                                    <input name="tarifas[{{ $index }}][cantidad_default]" type="number" min="0" step="0.01" value="{{ old("tarifas.$index.cantidad_default", $tarifa?->cantidad_default) }}">
                                    <div class="field-help">Para conceptos por contenedor se usa la cantidad de cartas.</div>
                                    @error("tarifas.$index.cantidad_default") <div class="error">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty">Agrega conceptos antes de configurar tarifas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <a class="btn secondary" href="{{ route('facturacion.tarifas-clientes.index') }}">Cancelar</a>
                <button class="btn accent" type="submit">Guardar tarifas</button>
            </div>
        </form>
    </section>
@endsection
