@extends('layouts.app')

@section('title', 'Editar Nota de Gastos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Editar Nota de Gastos</h1>
            <p class="subtle">Los cambios aplican solo a esta Nota de Gastos.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('facturacion.notas-gastos.show', $notaGasto) }}">Ver nota</a>
        </div>
    </div>

    <section class="panel" style="margin-bottom: 18px;">
        <div class="detail-grid">
            <div class="detail"><strong>Cliente</strong>{{ $notaGasto->consignatario_nombre ?: $notaGasto->consignatario?->nombre }}</div>
            <div class="detail"><strong>B/L</strong>{{ $notaGasto->bl }}</div>
            <div class="detail"><strong>Poliza</strong>{{ $notaGasto->poliza }}</div>
            <div class="detail"><strong>Contenedores</strong>{{ $notaGasto->cantidad_contenedores }}</div>
        </div>
    </section>

    <section class="panel">
        <form method="POST" action="{{ route('facturacion.notas-gastos.update', $notaGasto) }}">
            @csrf
            @method('PUT')
            <div class="grid">
                @include('facturacion.notas_gastos._detalles_form')
            </div>

            <div class="form-actions">
                <a class="btn secondary" href="{{ route('facturacion.notas-gastos.show', $notaGasto) }}">Cancelar</a>
                <button class="btn accent" type="submit">Guardar cambios</button>
            </div>
        </form>
    </section>
@endsection
