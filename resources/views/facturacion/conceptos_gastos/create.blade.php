@extends('layouts.app')

@section('title', 'Nuevo Concepto')

@section('content')
    <div class="page-head">
        <div>
            <h1>Nuevo Concepto</h1>
            <p class="subtle">Este concepto podra usarse en tarifas de clientes.</p>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('facturacion.conceptos-gastos.store') }}">
            @csrf
            @include('facturacion.conceptos_gastos._form')
        </form>
    </section>
@endsection
