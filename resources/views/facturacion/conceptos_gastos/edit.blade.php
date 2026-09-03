@extends('layouts.app')

@section('title', 'Editar Concepto')

@section('content')
    <div class="page-head">
        <div>
            <h1>Editar Concepto</h1>
            <p class="subtle">Las notas ya generadas conservan su propio detalle.</p>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('facturacion.conceptos-gastos.update', $concepto) }}">
            @csrf
            @method('PUT')
            @include('facturacion.conceptos_gastos._form')
        </form>
    </section>
@endsection
