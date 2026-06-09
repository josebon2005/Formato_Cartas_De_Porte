@extends('layouts.app')

@section('title', 'Editar Carta de Porte')

@section('content')
    <div class="page-head">
        <div>
            <h1>Editar Carta No. {{ $cartaPorte->numero_correlativo }}</h1>
            <p class="subtle">Actualiza los datos guardados de esta carta de porte.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('cartas-porte.show', $cartaPorte) }}">Ver carta</a>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('cartas-porte.update', $cartaPorte) }}">
            @csrf
            @method('PUT')
            @include('cartas_porte._form')
        </form>
    </section>
@endsection
