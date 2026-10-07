@extends('layouts.app')
@section('title', $pagoPiloto->exists ? 'Editar pago de piloto' : 'Realizar pago de piloto')
@section('container_class', 'container-wide')

@section('content')
    @include('pagos_pilotos._styles')
    @php
        $viajesFormulario = session()->hasOldInput() ? (old('viajes') ?: []) : $viajes;
        $movimientosFormulario = session()->hasOldInput() ? (old('movimientos') ?: []) : $movimientos;
    @endphp
    <div class="page-head">
        <div><h1>{{ $pagoPiloto->exists ? 'Editar pago de piloto' : 'Realizar pago de piloto' }}</h1><p class="subtle">{{ $pagoPiloto->piloto_nombre ?: $piloto?->nombre }} · {{ $meses[$pagoPiloto->mes] }} {{ $pagoPiloto->anio }}</p></div>
        <a class="btn secondary" href="{{ $pagoPiloto->exists ? route('pagos-pilotos.show', $pagoPiloto) : route('pagos-pilotos.index') }}">Volver</a>
    </div>
    @include('pagos_pilotos._errors')
    @if (! $pagoPiloto->exists)
        <section class="panel pagos-section">
            <h2>1. Seleccione el período</h2>
            <form class="pagos-filter" method="GET" action="{{ route('pagos-pilotos.create') }}" data-periodo-form>
                <input type="hidden" name="piloto_id" value="{{ $piloto->id }}">
                <div><label for="periodo_mes">Mes</label><select name="mes" id="periodo_mes">@foreach ($meses as $numero => $nombre)<option value="{{ $numero }}" @selected((int) $pagoPiloto->mes === (int) $numero)>{{ $nombre }}</option>@endforeach</select></div>
                <div><label for="periodo_anio">Año</label><input id="periodo_anio" name="anio" type="number" min="1900" max="9999" value="{{ $pagoPiloto->anio }}" required></div>
                <div class="actions"><button class="btn" type="submit">Cargar viajes del período</button></div>
            </form>
            <p class="field-help">Cambiar el período vuelve a cargar los viajes y los conceptos predeterminados del piloto.</p>
        </section>
    @endif
    <form method="POST" action="{{ $pagoPiloto->exists ? route('pagos-pilotos.update', $pagoPiloto) : route('pagos-pilotos.store') }}" data-pago-form>
        @csrf
        @if ($pagoPiloto->exists)
            @method('PUT')
        @else
            <input type="hidden" name="piloto_id" value="{{ $piloto->id }}">
            <input type="hidden" name="mes" value="{{ $pagoPiloto->mes }}">
            <input type="hidden" name="anio" value="{{ $pagoPiloto->anio }}">
        @endif
        <section class="panel pagos-section">
            <div class="pagos-section-head"><h2>Viajes del período</h2><button class="btn secondary" type="button" data-add-viaje>+ Agregar viaje</button></div>
            <p class="subtle" style="margin-bottom: 14px;">Revise el valor de cada viaje. Las Cartas de Porte no tienen una tarifa de pago al piloto: los viajes nuevos se cargan en Q. 0.00. Los cambios se guardan únicamente en este pago.</p>
            <input type="hidden" name="viajes" value="">
            <div class="table-wrap">
                <table class="pagos-editor">
                    <thead><tr><th>Fecha</th><th>C.P. / referencia</th><th>Consignatario</th><th>Destino</th><th>Valor (Q.)</th><th>Observación</th><th>Acción</th></tr></thead>
                    <tbody data-viajes>
                        @foreach ($viajesFormulario as $indice => $viaje)
                            @include('pagos_pilotos._viaje_row')
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="empty" data-viajes-empty @if (count($viajesFormulario)) hidden @endif>No hay viajes en este pago. Puede agregar un viaje manual.</p>
            <p class="pagos-total-viajes">TOTAL VIAJES: <span data-total="viajes">Q. 0.00</span></p>
        </section>
        <section class="panel pagos-section">
            <h2>Sueldo base</h2>
            <div style="max-width: 300px;"><label for="sueldo_base">Sueldo base (Q.)</label><input id="sueldo_base" name="sueldo_base" type="number" min="0" max="9999999999.99" step="0.01" inputmode="decimal" value="{{ old('sueldo_base', $pagoPiloto->sueldo_base ?: '0.00') }}" data-sueldo required></div>
            <p class="field-help">El sueldo base se suma una sola vez desde este campo; no lo agregue también como concepto.</p>
        </section>
        <section class="panel pagos-section">
            <div class="pagos-section-head"><h2>Bonificaciones, ingresos y descuentos</h2><button class="btn secondary" type="button" data-add-movimiento>+ Agregar concepto</button></div>
            <p class="subtle" style="margin-bottom: 14px;">Desmarque «Aplicar» para conservar un concepto sin incluirlo en el cálculo de este mes.</p>
            <input type="hidden" name="movimientos" value="">
            <div class="table-wrap">
                <table class="pagos-editor">
                    <thead><tr><th>Concepto</th><th>Tipo</th><th>Valor (Q.)</th><th>Aplicar</th><th>Observación</th><th>Acción</th></tr></thead>
                    <tbody data-movimientos>
                        @foreach ($movimientosFormulario as $indice => $movimiento)
                            @include('pagos_pilotos._movimiento_row')
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="empty" data-movimientos-empty @if (count($movimientosFormulario)) hidden @endif>No hay conceptos adicionales. Puede agregar ingresos y descuentos.</p>
        </section>
        <section class="panel pagos-section">
            <h2>Resumen del pago</h2>
            @include('pagos_pilotos._resumen')
            <div style="margin-top: 20px;"><label for="observaciones">Observaciones del pago (opcional)</label><textarea id="observaciones" name="observaciones" maxlength="10000">{{ old('observaciones', $pagoPiloto->observaciones) }}</textarea></div>
            <div class="form-actions actions"><a class="btn secondary" href="{{ $pagoPiloto->exists ? route('pagos-pilotos.show', $pagoPiloto) : route('pagos-pilotos.index') }}">Cancelar</a><button class="btn accent" type="submit">Guardar {{ $pagoPiloto->exists ? 'cambios' : 'borrador' }}</button></div>
        </section>
    </form>
    <template id="pago-viaje-template">@include('pagos_pilotos._viaje_row', ['indice' => '__INDEX__', 'viaje' => ['es_manual' => true]])</template>
    <template id="pago-movimiento-template">@include('pagos_pilotos._movimiento_row', ['indice' => '__INDEX__', 'movimiento' => ['aplicar' => true]])</template>
@endsection

@section('scripts')
    <script src="{{ asset('js/pagos-pilotos.js') }}" defer></script>
@endsection
