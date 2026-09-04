@extends('layouts.app')

@section('title', 'Cartas de Porte')
@section('container_class', 'container-wide')

@section('content')
    <div class="page-head">
        <div>
            <h1>Cartas de Porte</h1>
            <p class="subtle">Busca, revisa e imprime las cartas guardadas.</p>
        </div>
        <div class="actions">
            <a class="btn accent" href="{{ route('cartas-porte.create') }}">Nueva carta</a>
        </div>
    </div>

    <section class="panel">
        <form class="filters" method="GET" action="{{ route('cartas-porte.index') }}">
            <div>
                <label for="fecha">Fecha</label>
                <input id="fecha" name="fecha" type="date" value="{{ request('fecha') }}">
            </div>
            <div>
                <label for="consignatario">Consignatario</label>
                <input id="consignatario" name="consignatario" value="{{ request('consignatario') }}">
            </div>
            <div>
                <label for="bl">B/L</label>
                <input id="bl" name="bl" value="{{ request('bl') }}">
            </div>
            <div>
                <label for="poliza">Poliza</label>
                <input id="poliza" name="poliza" value="{{ request('poliza') }}">
            </div>
            <div style="align-self: end;" class="actions">
                <button class="btn" type="submit">Buscar</button>
                <a class="btn secondary" href="{{ route('cartas-porte.index') }}">Limpiar</a>
            </div>
        </form>

        <div class="table-wrap">
            <table class="cartas-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Fecha</th>
                        <th>Consignatario</th>
                        <th>Procedencia</th>
                        <th>B/L</th>
                        <th>Poliza</th>
                        <th>Piloto</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cartas as $carta)
                        @php
                            $notaOperacion = $carta->notaGastoOperacion;
                        @endphp
                        <tr>
                            <td>{{ $carta->numero_correlativo }}</td>
                            <td>{{ $carta->fecha?->format('d/m/Y') }}</td>
                            <td>{{ $carta->consignatario_texto }}</td>
                            <td>{{ $carta->procedencia_texto }}</td>
                            <td>{{ $carta->bl }}</td>
                            <td>{{ $carta->poliza }}</td>
                            <td>{{ $carta->piloto_texto }}</td>
                            <td class="actions-cell">
                                <div class="actions table-actions">
                                    <a class="btn secondary small" href="{{ route('cartas-porte.show', $carta) }}">Ver</a>
                                    <a class="btn secondary small" href="{{ route('cartas-porte.edit', $carta) }}">Editar</a>
                                    <a class="btn small" href="{{ route('cartas-porte.imprimir', $carta) }}">Imprimir</a>
                                    @if (! $notaOperacion)
                                        <a class="btn accent small" href="{{ route('facturacion.notas-gastos.desde-carta', $carta) }}">Generar Nota</a>
                                    @elseif (! $notaOperacion->fel_numero)
                                        <a class="btn warning small" href="{{ route('facturacion.notas-gastos.facturar', $notaOperacion) }}">Pendiente Factura SAT</a>
                                    @elseif ($notaOperacion->esta_facturada)
                                        <a class="btn success small" href="{{ route('facturacion.notas-gastos.show', $notaOperacion) }}">Facturada</a>
                                    @else
                                        <a class="btn secondary small" href="{{ route('facturacion.notas-gastos.show', $notaOperacion) }}">Ver Nota</a>
                                    @endif
                                    <form method="POST" action="{{ route('cartas-porte.destroy', $carta) }}" onsubmit="return confirm('Eliminar esta carta de porte?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger small" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">Todavia no hay cartas de porte registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $cartas->links('vendor.pagination.app') }}
        </div>
    </section>
@endsection
