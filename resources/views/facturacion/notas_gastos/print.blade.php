<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <style>
        @page {
            size: letter;
            margin: 0;
        }

        * { box-sizing: border-box; }

        body {
            background: #e5e7eb;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
        }

        .screen-actions {
            background: #111827;
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 14px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .btn {
            background: #c9141f;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            padding: 10px 14px;
            text-decoration: none;
        }

        .btn.secondary { background: #334155; }

        .sheet {
            background: #fff;
            min-height: 10.1in;
            margin: 16px auto;
            padding: .62in .55in .5in;
            position: relative;
            width: 7.7in;
        }

        .header {
            align-items: center;
            border-bottom: 3px solid #151a3d;
            display: grid;
            gap: .2in;
            grid-template-columns: 1.7in 1fr 1.35in;
            min-height: 1.08in;
            padding-bottom: .18in;
        }

        .logo-image {
            max-height: 1.02in;
            max-width: 1.72in;
            object-fit: contain;
        }

        .company {
            color: #151a3d;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 13.5px;
            line-height: 1.32;
            text-align: center;
        }

        .company strong {
            display: block;
            font-size: 18px;
            text-transform: uppercase;
        }

        .number {
            color: #8b2331;
            font-size: 23px;
            font-weight: 800;
            text-align: right;
        }

        .invoice-number {
            color: #111827;
            font-size: 12px;
            margin-top: .06in;
        }

        h1 {
            font-size: 24px;
            margin: .42in 0 .24in;
            text-align: center;
            text-transform: uppercase;
        }

        .meta {
            display: grid;
            gap: .11in;
            grid-template-columns: repeat(2, 1fr);
            margin-bottom: .31in;
        }

        .field {
            border-bottom: 1px solid #9ca3af;
            min-height: .31in;
            padding: .06in 0;
        }

        .field strong {
            color: #4b5563;
            display: inline-block;
            font-size: 11px;
            margin-right: .08in;
            text-transform: uppercase;
        }

        .description {
            font-size: 14.5px;
            line-height: 1.55;
            margin: .24in 0 .31in;
            text-transform: uppercase;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border-bottom: 1px solid #d1d5db;
            padding: .095in .05in;
            text-align: left;
        }

        th {
            background: #171316;
            color: #fff;
            font-size: 11px;
            text-transform: uppercase;
        }

        .money { text-align: right; white-space: nowrap; }

        tfoot th {
            background: #fff;
            color: #111827;
            font-size: 13px;
            padding-top: .12in;
            padding-bottom: .12in;
        }

        .cartas {
            color: #4b5563;
            font-size: 12px;
            line-height: 1.4;
            margin-top: .36in;
        }

        .copy-break {
            break-after: page;
            page-break-after: always;
        }

        @media print {
            html,
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }

            .screen-actions { display: none; }
            .sheet {
                margin: 0;
                min-height: 11in;
                padding: .68in .55in .52in;
                width: auto;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            table, tr, td, th { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="screen-actions">
        <button class="btn" type="button" onclick="window.print()">Imprimir</button>
        <a class="btn secondary" href="{{ route('facturacion.notas-gastos.show', $notaGasto) }}">Volver</a>
    </div>

    @for ($copy = 1; $copy <= $copias; $copy++)
    <section class="sheet {{ $copy < $copias ? 'copy-break' : '' }}">
        <header class="header">
            <div>
                @if (file_exists(public_path('images/logo-empresa.png')))
                    <img class="logo-image" src="{{ asset('images/logo-empresa.png') }}" alt="Multiservicios W. Orellana">
                @endif
            </div>
            <div class="company">
                <strong>Wilfredy Abel Orellana</strong>
                <div>Comercial Plan de Prestaciones Local No. 05</div>
                <div>Santo Tomas de Castilla, Tels: 7948 3939, 5923 8160</div>
                <div>transorellana@hotmail.com</div>
            </div>
            <div class="number">
                <div class="invoice-number">Factura SAT {{ $notaGasto->fel_numero }}</div>
            </div>
        </header>

        <h1>Nota de Gastos</h1>

        <div class="meta">
            <div class="field"><strong>Fecha</strong>{{ $notaGasto->fecha?->format('d/m/Y') }}</div>
            <div class="field"><strong>Cliente</strong>{{ $notaGasto->consignatario_nombre ?: $notaGasto->consignatario?->nombre }}</div>
        </div>

        <p class="description">{{ $notaGasto->descripcion }}</p>

        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="money">Precio</th>
                    <th class="money">Cantidad</th>
                    <th class="money">Total</th>
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
                        <td class="money">Q{{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                        <td class="money">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                        <td class="money">Q{{ number_format((float) $detalle->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="money">Subtotal</th>
                    <th class="money">Q{{ number_format((float) $notaGasto->subtotal, 2) }}</th>
                </tr>
                @foreach ($notaGasto->detalles->where('incluido', true)->where('grupo', 'adicional') as $detalle)
                    <tr>
                        <td>
                            @if ($detalle->numero_factura)
                                {{ $detalle->numero_factura }} &mdash;
                            @endif
                            {{ $detalle->concepto_nombre }}
                        </td>
                        <td class="money">Q{{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                        <td class="money">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                        <td class="money">Q{{ number_format((float) $detalle->total, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="3" class="money">Total</th>
                    <th class="money">Q{{ number_format((float) $notaGasto->total, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="cartas">
            <strong>Cartas incluidas:</strong>
            @foreach ($notaGasto->cartasPorte as $carta)
                CP-{{ str_pad((string) $carta->pivot->numero_correlativo, 3, '0', STR_PAD_LEFT) }} / {{ $carta->pivot->contenedor }}{{ ! $loop->last ? '; ' : '' }}
            @endforeach
        </div>
    </section>
    @endfor
</body>
</html>
