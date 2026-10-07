<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago de {{ $pagoPiloto->piloto_nombre }} · {{ $meses[$pagoPiloto->mes] }} {{ $pagoPiloto->anio }}</title>
    <style>
        @page { size: letter portrait; margin: 12mm 10mm; }
        * { box-sizing: border-box; }
        body { color: #111; background: #ececec; font-family: Arial, Helvetica, sans-serif; font-size: 11px; margin: 0; }
        .print-tools { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin: 18px auto; max-width: 800px; padding: 0 12px; }
        .print-tools button, .print-tools a { background: #171316; border: 0; border-radius: 5px; color: #fff; cursor: pointer; font: inherit; font-size: 14px; padding: 10px 16px; text-decoration: none; }
        .print-tools p { margin: 0; width: 100%; text-align: center; }
        .sheet { background: #fff; margin: 0 auto 24px; max-width: 215.9mm; padding: 12mm 10mm; }
        h1 { font-size: 20px; margin: 0 0 7px; text-align: center; text-transform: uppercase; overflow-wrap: anywhere; }
        .period { font-size: 13px; margin: 0 0 5px; text-align: center; }
        .meta { color: #333; margin: 0 0 18px; text-align: center; }
        .cancelled { border: 2px solid #a00; color: #a00; font-size: 18px; font-weight: bold; margin-bottom: 15px; padding: 8px; text-align: center; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        thead { display: table-header-group; }
        th, td { border-bottom: 1px solid #bbb; padding: 7px 5px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        th { border-top: 1px solid #111; border-bottom: 2px solid #111; font-size: 10px; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .date { white-space: nowrap; }
        .summary { margin-top: 12px; }
        .summary td { border-bottom: 0; }
        .summary .travel-total td { border-top: 2px solid #111; border-bottom: 1px solid #999; font-weight: bold; padding-top: 10px; padding-bottom: 10px; }
        .summary .grand-total td { border-top: 3px double #111; border-bottom: 3px double #111; font-size: 22px; font-weight: 800; padding-top: 13px; padding-bottom: 13px; }
        .notes { margin-top: 18px; white-space: pre-wrap; overflow-wrap: anywhere; }
        @media print {
            body { background: #fff; }
            .print-tools { display: none !important; }
            .sheet { margin: 0; max-width: none; padding: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="print-tools">
        <button type="button" onclick="window.print()">Imprimir pago</button>
        <a href="{{ route('pagos-pilotos.show', $pagoPiloto) }}">Volver al pago</a>
        <p>Use papel tamaño carta y desactive «Encabezados y pies de página» para ocultar la URL y los datos del navegador.</p>
    </div>
    <main class="sheet">
        <h1>{{ $pagoPiloto->piloto_nombre }}</h1>
        <p class="period">PAGO DE PILOTO · {{ mb_strtoupper($meses[$pagoPiloto->mes]) }} {{ $pagoPiloto->anio }}</p>
        <p class="meta">{{ $pagoPiloto->estado }}@if ($pagoPiloto->fecha_pago) · Fecha de pago: {{ $pagoPiloto->fecha_pago_texto }}@endif</p>
        @if ($pagoPiloto->estado === 'ANULADO')<div class="cancelled">PAGO ANULADO</div>@endif
        <table aria-label="Detalle de viajes">
            <colgroup><col style="width: 15%;"><col style="width: 15%;"><col style="width: 28%;"><col style="width: 25%;"><col style="width: 17%;"></colgroup>
            <thead><tr><th>FECHA</th><th>C.P.</th><th>CONSIGNATARIO</th><th>DESTINO</th><th class="amount">VALOR</th></tr></thead>
            <tbody>
                @forelse ($pagoPiloto->viajes as $viaje)
                    <tr><td class="date">{{ $viaje->fecha?->format('d-m-Y') }}</td><td>{{ $viaje->referencia }}</td><td>{{ $viaje->consignatario }}</td><td>{{ $viaje->destino }}</td><td class="amount">Q. {{ number_format((float) $viaje->valor, 2) }}</td></tr>
                @empty
                    <tr><td colspan="5" style="text-align: center;">Sin viajes registrados en este pago.</td></tr>
                @endforelse
            </tbody>
        </table>
        <table class="summary" aria-label="Cálculo del pago">
            <colgroup><col style="width: 68%;"><col style="width: 32%;"></colgroup>
            <tbody>
                <tr class="travel-total"><td>TOTAL DE VIAJES</td><td class="amount">Q. {{ number_format((float) $pagoPiloto->total_viajes, 2) }}</td></tr>
                <tr><td>+ SUELDO BASE</td><td class="amount">Q. {{ number_format((float) $pagoPiloto->sueldo_base, 2) }}</td></tr>
                @foreach ($pagoPiloto->movimientos->where('aplicar', true)->where('tipo', 'SUMA') as $movimiento)
                    <tr><td>+ {{ $movimiento->concepto }}</td><td class="amount">Q. {{ number_format((float) $movimiento->valor, 2) }}</td></tr>
                @endforeach
                @foreach ($pagoPiloto->movimientos->where('aplicar', true)->where('tipo', 'DESCUENTO') as $movimiento)
                    <tr><td>− {{ $movimiento->concepto }}</td><td class="amount">Q. {{ number_format((float) $movimiento->valor, 2) }}</td></tr>
                @endforeach
                <tr class="grand-total"><td>TOTAL</td><td class="amount">Q. {{ number_format((float) $pagoPiloto->total_pagar, 2) }}</td></tr>
            </tbody>
        </table>
        @if ($pagoPiloto->observaciones)<div class="notes"><strong>Observaciones:</strong><br>{{ $pagoPiloto->observaciones }}</div>@endif
    </main>
</body>
</html>
