@extends('layouts.app')

@section('title', 'Cobros')

@section('content')
    <div class="page-head">
        <div>
            <h1>Cobros</h1>
            <p class="subtle">Administra los conceptos disponibles para Notas de Gastos.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('facturacion.notas-gastos.index') }}">Notas</a>
            <a class="btn accent" href="{{ route('facturacion.conceptos-gastos.create') }}">Nuevo cobro</a>
        </div>
    </div>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conceptos as $concepto)
                        <tr>
                            <td>{{ $concepto->nombre }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn secondary small" href="{{ route('facturacion.conceptos-gastos.edit', $concepto) }}">Editar</a>
                                    <form method="POST" action="{{ route('facturacion.conceptos-gastos.destroy', $concepto) }}" onsubmit="return confirm('Eliminar este cobro? Si ya fue utilizado, se desactivara para conservar el historial.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger small" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty">Todavia no hay cobros disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
