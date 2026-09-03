@extends('layouts.app')

@section('title', $notaGasto->fel_numero ? 'Editar No. Factura' : 'Agregar No. Factura')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $notaGasto->fel_numero ? 'Editar No. Factura' : 'Agregar No. Factura' }}</h1>
            <p class="subtle">BL {{ $notaGasto->bl }} / Poliza {{ $notaGasto->poliza }}</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('facturacion.notas-gastos.show', $notaGasto) }}">Volver a nota</a>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('facturacion.notas-gastos.facturar.update', $notaGasto) }}">
            @csrf
            @method('PUT')

            <div class="grid">
                <div>
                    <label for="fel_numero">Numero de Factura SAT</label>
                    <input id="fel_numero" name="fel_numero" required value="{{ old('fel_numero', $notaGasto->fel_numero) }}">
                    @error('fel_numero') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="btn secondary" href="{{ route('facturacion.notas-gastos.show', $notaGasto) }}">Cancelar</a>
                <button class="btn accent" type="submit">Confirmar</button>
            </div>
        </form>
    </section>
@endsection
