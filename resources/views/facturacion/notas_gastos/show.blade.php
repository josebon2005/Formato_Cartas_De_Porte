@extends('layouts.app')

@section('title', 'Nota de Gastos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Nota de Gastos</h1>
            <p class="subtle">Nota de Gastos {{ strtolower(str_replace('_', ' ', $notaGasto->estado)) }}.</p>
        </div>
        <div class="actions">
            @if ($notaGasto->esta_facturada && $notaGasto->fel_numero)
                <button class="btn" type="button" onclick="askPrintCopies(@js(route('facturacion.notas-gastos.imprimir', $notaGasto)))">Imprimir</button>
            @elseif (! $notaGasto->esta_anulada)
                <span class="btn secondary disabled">Pendiente de factura</span>
            @endif
            @if (! $notaGasto->esta_anulada)
                <a class="btn secondary" href="{{ route('facturacion.notas-gastos.edit', $notaGasto) }}">Editar</a>
            @endif
            @if ($notaGasto->esta_facturada)
                <a class="btn accent" href="{{ route('facturacion.notas-gastos.facturar', $notaGasto) }}">Editar No. Factura</a>
                <form method="POST" action="{{ route('facturacion.notas-gastos.anular', $notaGasto) }}" onsubmit="return confirmAnulacion(this);">
                    @csrf
                    @method('PUT')
                    <input name="motivo_anulacion" type="hidden">
                    <button class="btn danger" type="submit">Anular Nota</button>
                </form>
            @elseif (! $notaGasto->esta_anulada)
                <a class="btn accent" href="{{ route('facturacion.notas-gastos.facturar', $notaGasto) }}">Agregar No. Factura</a>
            @endif
            @if ($notaGasto->esta_anulada)
                <form method="POST" action="{{ route('facturacion.notas-gastos.destroy', $notaGasto) }}" onsubmit="return confirm('¿Está seguro de que desea eliminar permanentemente esta Nota de Gastos anulada? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn danger" type="submit">Eliminar</button>
                </form>
            @endif
            <a class="btn secondary" href="{{ route('facturacion.notas-gastos.index') }}">Volver</a>
        </div>
    </div>

    <section class="panel" style="margin-bottom: 18px;">
        <div class="detail-grid">
            <div class="detail"><strong>Fecha</strong>{{ $notaGasto->fecha?->format('d/m/Y') }}</div>
            <div class="detail"><strong>Cliente</strong>{{ $notaGasto->consignatario_nombre ?: $notaGasto->consignatario?->nombre }}</div>
            <div class="detail"><strong>B/L</strong>{{ $notaGasto->bl }}</div>
            <div class="detail"><strong>Poliza</strong>{{ $notaGasto->poliza }}</div>
            <div class="detail"><strong>Contenedores</strong>{{ $notaGasto->cantidad_contenedores }}</div>
            <div class="detail">
                <strong>Estado</strong>
                <span class="status-badge {{ $notaGasto->esta_anulada ? 'cancelled' : ($notaGasto->esta_facturada ? 'billed' : 'generated') }}">
                    {{ str_replace('_', ' ', $notaGasto->estado) }}
                </span>
            </div>
            <div class="detail"><strong>No. Factura SAT</strong>{{ $notaGasto->fel_numero ?: 'Pendiente de facturacion' }}</div>
            <div class="detail"><strong>Fecha factura</strong>{{ $notaGasto->factura_fecha?->format('d/m/Y') }}</div>
            @if ($notaGasto->esta_anulada)
                <div class="detail"><strong>Fecha anulacion</strong>{{ $notaGasto->fecha_anulacion?->format('d/m/Y H:i') }}</div>
                <div class="detail span-2"><strong>Motivo anulacion</strong>{{ $notaGasto->motivo_anulacion }}</div>
            @endif
        </div>
    </section>

    <section class="panel" style="margin-bottom: 18px;">
        <h1 style="font-size: 20px;">Descripcion</h1>
        <p>{{ $notaGasto->descripcion }}</p>
    </section>

    <section class="panel" style="margin-bottom: 18px;">
        <h1 style="font-size: 20px;">Conceptos</h1>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Grupo</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notaGasto->detalles->where('incluido', true)->where('grupo', 'subtotal') as $detalle)
                        <tr>
                            <td>
                                @if ($detalle->numero_factura)
                                    {{ $detalle->numero_factura }} &mdash;
                                @endif
                                {{ $detalle->concepto_nombre }}
                            </td>
                            <td>{{ ucfirst($detalle->grupo) }}</td>
                            <td>Q{{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                            <td>{{ number_format((float) $detalle->cantidad, 2) }}</td>
                            <td>Q{{ number_format((float) $detalle->total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th colspan="4">Subtotal</th>
                        <th>Q{{ number_format((float) $notaGasto->subtotal, 2) }}</th>
                    </tr>
                    @foreach ($notaGasto->detalles->where('incluido', true)->where('grupo', 'adicional') as $detalle)
                        <tr>
                            <td>
                                @if ($detalle->numero_factura)
                                    {{ $detalle->numero_factura }} &mdash;
                                @endif
                                {{ $detalle->concepto_nombre }}
                            </td>
                            <td>{{ ucfirst($detalle->grupo) }}</td>
                            <td>Q{{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                            <td>{{ number_format((float) $detalle->cantidad, 2) }}</td>
                            <td>Q{{ number_format((float) $detalle->total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th colspan="4">Total</th>
                        <th>Q{{ number_format((float) $notaGasto->total, 2) }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h1 style="font-size: 20px;">Cartas incluidas</h1>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Carta</th>
                        <th>Contenedor</th>
                        <th>Destino</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notaGasto->cartasPorte as $carta)
                        <tr>
                            <td>
                                <a href="{{ route('cartas-porte.show', $carta) }}">
                                    CP-{{ str_pad((string) $carta->pivot->numero_correlativo, 3, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td>{{ $carta->pivot->contenedor }}</td>
                            <td>{{ $carta->destino }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
