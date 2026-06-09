@extends('layouts.app')

@section('title', 'Nueva Carta de Porte')

@section('content')
    <div class="page-head">
        <div>
            <h1>Nueva Carta de Porte</h1>
            <p class="subtle">Registra una carta y agrega datos nuevos a los catalogos desde este formulario.</p>
        </div>
    </div>

    <section class="panel">
        @if ($cargadaDesdeUltima)
            <div class="alert">
                Formulario cargado con datos de la ultima carta. Puede editar cualquier campo antes de guardar.
            </div>
        @endif

        <form method="POST" action="{{ route('cartas-porte.store') }}">
            @csrf
            @include('cartas_porte._form')
        </form>
    </section>
@endsection
