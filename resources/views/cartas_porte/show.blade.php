@extends('layouts.app')

@section('title', 'Carta de Porte No. ' . $cartaPorte->numero_correlativo)

@section('content')
    <div class="page-head">
        <div>
            <h1>Carta de Porte No. {{ $cartaPorte->numero_correlativo }}</h1>
            <p class="subtle">Registro guardado el {{ $cartaPorte->created_at?->format('d/m/Y H:i') }}.</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('cartas-porte.imprimir', $cartaPorte) }}">Imprimir</a>
            <a class="btn secondary" href="{{ route('cartas-porte.edit', $cartaPorte) }}">Editar</a>
            <a class="btn secondary" href="{{ route('cartas-porte.index') }}">Volver</a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail"><strong>Fecha</strong>{{ $cartaPorte->fecha?->format('d/m/Y') }}</div>
            <div class="detail"><strong>Consignatario</strong>{{ $cartaPorte->consignatario?->nombre }}</div>
            <div class="detail"><strong>Procedencia</strong>{{ $cartaPorte->procedencia?->nombre }}</div>
            <div class="detail"><strong>Destino</strong>{{ $cartaPorte->destino }}</div>
            <div class="detail"><strong>Poliza</strong>{{ $cartaPorte->poliza }}</div>
            <div class="detail"><strong>ID</strong>{{ $cartaPorte->id_documento }}</div>
            <div class="detail"><strong>DA</strong>{{ $cartaPorte->da }}</div>
            <div class="detail"><strong>MI</strong>{{ $cartaPorte->mi }}</div>
            <div class="detail"><strong>Comunicarse con</strong>{{ $cartaPorte->contacto }}</div>
            <div class="detail"><strong>Telefono</strong>{{ $cartaPorte->telefono }}</div>
            <div class="detail"><strong>Contenedor</strong>{{ $cartaPorte->contenedor }}</div>
            <div class="detail"><strong>Bultos</strong>{{ $cartaPorte->bultos }}</div>
            <div class="detail span-2"><strong>Contenido</strong>{{ $cartaPorte->contenido }}</div>
            <div class="detail"><strong>Peso KLS</strong>{{ $cartaPorte->peso_kls }}</div>
            <div class="detail"><strong>Vapor</strong>{{ $cartaPorte->vapor }}</div>
            <div class="detail"><strong>Fecha vapor</strong>{{ $cartaPorte->fecha_vapor?->format('d/m/Y') }}</div>
            <div class="detail"><strong>B/L</strong>{{ $cartaPorte->bl }}</div>
            <div class="detail"><strong>Piloto</strong>{{ $cartaPorte->piloto?->nombre }}</div>
            <div class="detail"><strong>Cabezal placas</strong>{{ $cartaPorte->cabezal?->placa }}</div>
            <div class="detail"><strong>Licencia</strong>{{ $cartaPorte->licencia?->numero }}</div>
        </div>
    </section>
@endsection
