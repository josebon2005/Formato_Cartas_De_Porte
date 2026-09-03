@extends('layouts.app')

@section('title', 'Notas de Gastos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Notas de Gastos</h1>
            <p class="subtle">Consulta las operaciones agrupadas por BL y poliza.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('facturacion.tarifas-clientes.index') }}">Tarifas</a>
            <a class="btn secondary" href="{{ route('facturacion.conceptos-gastos.index') }}">Conceptos</a>
        </div>
    </div>

    <section class="panel">
        <form class="filters" method="GET" action="{{ route('facturacion.notas-gastos.index') }}">
            <div>
                <label for="fecha_desde">Desde</label>
                <input id="fecha_desde" name="fecha_desde" type="date" value="{{ request('fecha_desde') }}">
            </div>
            <div>
                <label for="fecha_hasta">Hasta</label>
                <input id="fecha_hasta" name="fecha_hasta" type="date" value="{{ request('fecha_hasta') }}">
            </div>
            <div>
                <label for="cliente">Cliente</label>
                <input id="cliente" name="cliente" value="{{ request('cliente') }}">
            </div>
            <div>
                <label for="bl">B/L</label>
                <input id="bl" name="bl" value="{{ request('bl') }}">
            </div>
            <div>
                <label for="poliza">Poliza</label>
                <input id="poliza" name="poliza" value="{{ request('poliza') }}">
            </div>
            <div>
                <label for="fel_numero">No. FEL</label>
                <input id="fel_numero" name="fel_numero" value="{{ request('fel_numero') }}">
            </div>
            <div>
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    @foreach ($estados as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="align-self: end;" class="actions">
                <button class="btn" type="submit">Buscar</button>
                <a class="btn secondary" href="{{ route('facturacion.notas-gastos.index') }}">Limpiar</a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>B/L</th>
                        <th>Poliza</th>
                        <th>Contenedores</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>No. Factura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notas as $nota)
                        <tr>
                            <td>{{ $nota->fecha?->format('d/m/Y') }}</td>
                            <td>{{ $nota->consignatario_nombre ?: $nota->consignatario?->nombre }}</td>
                            <td>{{ $nota->bl }}</td>
                            <td>{{ $nota->poliza }}</td>
                            <td>{{ $nota->cantidad_contenedores }}</td>
                            <td>Q{{ number_format((float) $nota->total, 2) }}</td>
                            <td>
                                <span class="status-badge {{ $nota->esta_anulada ? 'cancelled' : ($nota->esta_facturada ? 'billed' : 'generated') }}">
                                    {{ str_replace('_', ' ', $nota->estado) }}
                                </span>
                            </td>
                            <td>{{ $nota->fel_numero }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary small" href="{{ route('facturacion.notas-gastos.show', $nota) }}">Ver</a>
                                    @if (! $nota->esta_anulada)
                                        <a class="btn secondary small" href="{{ route('facturacion.notas-gastos.edit', $nota) }}">Editar</a>
                                    @endif
                                    @if ($nota->esta_facturada && $nota->fel_numero)
                                        <button class="btn small" type="button" onclick="askPrintCopies(@js(route('facturacion.notas-gastos.imprimir', $nota)))">Imprimir</button>
                                    @elseif (! $nota->esta_anulada)
                                        <span class="btn secondary small disabled">Pendiente de factura</span>
                                    @endif
                                    @if ($nota->esta_facturada)
                                        <a class="btn accent small" href="{{ route('facturacion.notas-gastos.facturar', $nota) }}">Editar No. Factura</a>
                                        <form method="POST" action="{{ route('facturacion.notas-gastos.anular', $nota) }}" onsubmit="return confirmAnulacion(this);">
                                            @csrf
                                            @method('PUT')
                                            <input name="motivo_anulacion" type="hidden">
                                            <button class="btn danger small" type="submit">Anular Nota</button>
                                        </form>
                                    @elseif (! $nota->esta_anulada)
                                        <a class="btn accent small" href="{{ route('facturacion.notas-gastos.facturar', $nota) }}">Agregar No. Factura</a>
                                        <form method="POST" action="{{ route('facturacion.notas-gastos.destroy', $nota) }}" onsubmit="return confirm('¿Está seguro de que desea eliminar esta Nota de Gastos? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger small" type="submit">Eliminar</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('facturacion.notas-gastos.destroy', $nota) }}" onsubmit="return confirm('¿Está seguro de que desea eliminar permanentemente esta Nota de Gastos anulada? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger small" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty">Todavia no hay notas de gastos registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $notas->links() }}
        </div>
    </section>
@endsection
