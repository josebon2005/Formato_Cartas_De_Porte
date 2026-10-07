@extends('layouts.app')
@section('title', 'Pago de ' . $pagoPiloto->piloto_nombre)
@section('container_class', 'container-wide')

@section('content')
    @include('pagos_pilotos._styles')
    <div class="page-head"><div><h1>Pago de piloto</h1><p class="subtle">{{ $pagoPiloto->piloto_nombre }} · {{ $meses[$pagoPiloto->mes] }} {{ $pagoPiloto->anio }}</p></div><div class="actions"><a class="btn secondary" href="{{ route('pagos-pilotos.historial') }}">Historial</a><a class="btn secondary" href="{{ route('pagos-pilotos.index') }}">Pilotos</a></div></div>
    @include('pagos_pilotos._errors')
    @if (session('pago_existente'))
        <div class="actions" style="margin-bottom: 16px;"><a class="btn accent" href="#detalle-pago">VER PAGO EXISTENTE</a></div>
    @endif
    <section id="detalle-pago" class="panel pagos-section">
        <div class="detail-grid pagos-metadata">
            <div class="detail"><strong>Piloto</strong>{{ $pagoPiloto->piloto_nombre }}</div>
            <div class="detail"><strong>Período</strong>{{ $meses[$pagoPiloto->mes] }} {{ $pagoPiloto->anio }}</div>
            <div class="detail"><strong>Cabezal</strong>{{ $pagoPiloto->cabezal_placa ?: '—' }}</div>
            <div class="detail"><strong>Licencia</strong>{{ $pagoPiloto->licencia_numero ?: '—' }}</div>
            <div class="detail"><strong>Estado</strong><span class="status-badge {{ $pagoPiloto->estado === 'PAGADO' ? 'billed' : ($pagoPiloto->estado === 'ANULADO' ? 'cancelled' : 'generated') }}">{{ $pagoPiloto->estado }}</span></div>
            @if ($pagoPiloto->fecha_pago)<div class="detail"><strong>Fecha de pago</strong>{{ $pagoPiloto->fecha_pago_texto }}</div>@endif
        </div>
        @if ($pagoPiloto->estado === 'ANULADO')
            <p class="alert danger">Este pago está anulado y se conserva como histórico.</p>
        @elseif ($pagoPiloto->estado === 'BORRADOR')
            <p class="subtle" style="margin-bottom: 14px;">Revise e imprima el pago. Cuando lo haya realizado, utilice «Marcar como pagado».</p>
        @endif
        @include('pagos_pilotos._actions', ['ocultarVer' => true, 'mostrarPagar' => true])
        @if ($pagoPiloto->estado === 'ANULADO' && $pagoPiloto->piloto_id)
            <div class="actions" style="margin-top: 12px;"><a class="btn accent small" href="{{ route('pagos-pilotos.create', ['piloto_id' => $pagoPiloto->piloto_id, 'mes' => $pagoPiloto->mes, 'anio' => $pagoPiloto->anio]) }}">Generar nuevo pago del período</a></div>
        @endif
    </section>
    <section class="panel pagos-section">
        <h2>Viajes</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Fecha</th><th>C.P. / referencia</th><th>Consignatario</th><th>Destino</th><th>Valor</th><th>Origen</th><th>Observación</th></tr></thead>
                <tbody>
                    @forelse ($pagoPiloto->viajes as $viaje)
                        <tr><td style="white-space: nowrap;">{{ $viaje->fecha?->format('d-m-Y') }}</td><td>{{ $viaje->referencia ?: '—' }}</td><td>{{ $viaje->consignatario }}</td><td>{{ $viaje->destino }}</td><td class="pagos-number">Q. {{ number_format((float) $viaje->valor, 2) }}</td><td>{{ $viaje->es_manual ? 'Manual' : 'Carta de Porte' }}</td><td class="pagos-note">{{ $viaje->observacion }}</td></tr>
                    @empty
                        <tr><td class="empty" colspan="7">Este pago no incluye viajes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="pagos-total-viajes">TOTAL VIAJES: Q. {{ number_format((float) $pagoPiloto->total_viajes, 2) }}</p>
    </section>
    <section class="panel pagos-section">
        <h2>Bonificaciones, ingresos y descuentos</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Concepto</th><th>Tipo</th><th>Valor</th><th>Aplicar</th><th>Observación</th></tr></thead>
                <tbody>
                    @forelse ($pagoPiloto->movimientos as $movimiento)
                        <tr class="{{ $movimiento->aplicar ? '' : 'pagos-disabled' }}"><td>{{ $movimiento->concepto }}</td><td>{{ $movimiento->tipo }}</td><td class="pagos-number">Q. {{ number_format((float) $movimiento->valor, 2) }}</td><td>{{ $movimiento->aplicar ? 'Sí' : 'No · excluido del cálculo' }}</td><td class="pagos-note">{{ $movimiento->observacion }}</td></tr>
                    @empty
                        <tr><td class="empty" colspan="5">Este pago no incluye movimientos adicionales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="panel pagos-section">
        <h2>Resumen</h2>
        @include('pagos_pilotos._resumen')
        @if ($pagoPiloto->observaciones)<div style="margin-top: 20px;"><strong>Observaciones</strong><p class="pagos-note">{{ $pagoPiloto->observaciones }}</p></div>@endif
    </section>
@endsection
