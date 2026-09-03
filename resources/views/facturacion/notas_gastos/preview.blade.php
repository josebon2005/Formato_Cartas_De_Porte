@extends('layouts.app')

@section('title', 'Generar Nota de Gastos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Generar Nota de Gastos</h1>
            <p class="subtle">Operacion agrupada por BL {{ $cartaPorte->bl }} y poliza {{ $cartaPorte->poliza }}.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('cartas-porte.show', $cartaPorte) }}">Volver a carta</a>
        </div>
    </div>

    <section class="panel" style="margin-bottom: 18px;">
        <div class="detail-grid">
            <div class="detail"><strong>Cliente</strong>{{ $cartaPorte->consignatario_texto }}</div>
            <div class="detail"><strong>B/L</strong>{{ $cartaPorte->bl }}</div>
            <div class="detail"><strong>Poliza</strong>{{ $cartaPorte->poliza }}</div>
            <div class="detail"><strong>Contenedores</strong>{{ $cartas->count() }}</div>
            <div class="detail"><strong>Procedencia</strong>{{ $cartaPorte->procedencia_texto }}</div>
            <div class="detail"><strong>Destino</strong>{{ $cartaPorte->destino }}</div>
        </div>
    </section>

    <section class="panel" style="margin-bottom: 18px;">
        <h1 style="font-size: 20px;">Cartas incluidas</h1>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Carta</th>
                        <th>Fecha</th>
                        <th>Contenedor</th>
                        <th>Piloto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cartas as $carta)
                        <tr>
                            <td>CP-{{ str_pad((string) $carta->numero_correlativo, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $carta->fecha?->format('d/m/Y') }}</td>
                            <td>{{ $carta->contenedor }}</td>
                            <td>{{ $carta->piloto_texto }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <form method="POST" action="{{ route('facturacion.notas-gastos.store-desde-carta', $cartaPorte) }}">
            @csrf
            <div class="grid">
                @include('facturacion.notas_gastos._detalles_form')
            </div>

            <div class="form-actions">
                <a class="btn secondary" href="{{ route('cartas-porte.show', $cartaPorte) }}">Cancelar</a>
                <button class="btn accent" type="submit" @disabled(count($detalles) === 0)>Guardar nota</button>
            </div>
        </form>
    </section>
@endsection
