@extends('layouts.app')

@section('title', 'Conceptos de Gastos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Conceptos de Gastos</h1>
            <p class="subtle">Administra los conceptos disponibles para tarifas y notas.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('facturacion.notas-gastos.index') }}">Notas</a>
            <a class="btn accent" href="{{ route('facturacion.conceptos-gastos.create') }}">Nuevo concepto</a>
        </div>
    </div>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Codigo</th>
                        <th>Tipo</th>
                        <th>Grupo</th>
                        <th>Activo</th>
                        <th>Orden</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conceptos as $concepto)
                        <tr>
                            <td>{{ $concepto->nombre }}</td>
                            <td>{{ $concepto->codigo }}</td>
                            <td>{{ str_replace('_', ' ', $concepto->tipo_calculo) }}</td>
                            <td>{{ ucfirst($concepto->grupo) }}</td>
                            <td>{{ $concepto->activo ? 'Si' : 'No' }}</td>
                            <td>{{ $concepto->orden }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary small" href="{{ route('facturacion.conceptos-gastos.edit', $concepto) }}">Editar</a>
                                    <form method="POST" action="{{ route('facturacion.conceptos-gastos.destroy', $concepto) }}" onsubmit="return confirm('Eliminar este concepto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger small" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
