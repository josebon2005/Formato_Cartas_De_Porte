@extends('layouts.app')

@section('title', 'Nuevo Cobro')

@section('content')
    <div class="page-head">
        <div>
            <h1>Nuevo Cobro</h1>
            <p class="subtle">Este cobro podra usarse en nuevas Notas de Gastos.</p>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('facturacion.conceptos-gastos.store') }}">
            @csrf
            @include('facturacion.conceptos_gastos._form')
        </form>
    </section>
@endsection
