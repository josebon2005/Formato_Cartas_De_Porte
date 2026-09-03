@extends('layouts.app')

@section('title', 'Tarifas por Cliente')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tarifas por Cliente</h1>
            <p class="subtle">Configura precios por consignatario.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('facturacion.notas-gastos.index') }}">Notas</a>
            <a class="btn secondary" href="{{ route('facturacion.conceptos-gastos.index') }}">Conceptos</a>
        </div>
    </div>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Tarifas configuradas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->nombre }}</td>
                            <td>{{ $cliente->tarifas_clientes_count }}</td>
                            <td>
                                <a class="btn secondary small" href="{{ route('facturacion.tarifas-clientes.edit', $cliente) }}">Configurar tarifas</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty">Agrega consignatarios en Datos para configurar tarifas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
