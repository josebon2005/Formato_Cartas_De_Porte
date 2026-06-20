<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carta de Porte</title>
    <style>
        @page {
            size: letter;
            margin: 0;
        }

        * { box-sizing: border-box; }

        body {
            background: #e5e7eb;
            color: #151a3d;
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
            background: #0f766e;
            border: 0;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-weight: 700;
            padding: 10px 14px;
            text-decoration: none;
        }

        .btn.secondary { background: #334155; }

        .sheet {
            background: #fff;
            height: 10.1in;
            margin: 16px auto;
            overflow: hidden;
            padding: 0.1in 0.15in;
            position: relative;
            width: 7.7in;
        }

        .copy-break {
            break-after: page;
            page-break-after: always;
        }

        .header {
            align-items: flex-start;
            display: grid;
            grid-template-columns: 2.45in 1fr .85in;
            gap: 0.18in;
            min-height: 1.26in;
        }

        .logo {
            color: #11184a;
            font-weight: 900;
            line-height: .9;
            padding-top: .12in;
            text-transform: uppercase;
        }

        .logo-image {
            display: block;
            max-height: 1.18in;
            max-width: 2.35in;
            object-fit: contain;
            object-position: left top;
            width: 100%;
        }

        .watermark {
            left: 50%;
            opacity: .075;
            position: absolute;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 5.2in;
            z-index: 0;
        }

        .sheet > :not(.watermark):not(.signatures) {
            position: relative;
            z-index: 1;
        }

        .logo-main {
            font-size: 23px;
            letter-spacing: -.02em;
        }

        .logo-sub {
            font-size: 18px;
            margin-left: .35in;
        }

        .logo-caption {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0;
            margin-top: 5px;
            text-transform: none;
        }

        .company {
            color: #16153d;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.15;
            text-align: center;
        }

        .company .owner {
            font-size: 18px;
            text-transform: uppercase;
        }

        .number {
            color: #8b2331;
            font-size: 26px;
            letter-spacing: .08em;
            padding-top: .36in;
            text-align: center;
        }

        .title {
            font-size: 21px;
            font-weight: 900;
            margin: .04in 0 .48in;
            text-align: center;
        }

        .row {
            align-items: end;
            display: grid;
            gap: .08in;
            margin-bottom: .18in;
        }

        .row.date {
            grid-template-columns: max-content .28in .72in .28in 1.04in .62in .74in;
            column-gap: .07in;
        }
        .row.one { grid-template-columns: auto 1fr; }
        .row.two { grid-template-columns: auto 1fr auto 1fr; }
        .row.docs { grid-template-columns: auto 1.7in auto 1in auto 1in auto 1in; }
        .row.contact { grid-template-columns: auto 1fr auto 1.7in; }

        .label {
            font-size: 15px;
            font-style: italic;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .plain-label {
            font-size: 15px;
            font-style: italic;
            font-weight: 900;
            white-space: nowrap;
        }

        .line {
            border-bottom: 2px solid #1f2147;
            display: block;
            min-height: .22in;
            padding: 0 .05in .02in;
            word-break: break-word;
        }

        .boxline {
            border: 2px solid #1f2147;
            display: block;
            min-height: .31in;
            padding: .03in .05in;
            word-break: break-word;
        }

        .form-table {
            border-collapse: collapse;
            color: #151a3d;
            margin: .13in 0 .2in;
            table-layout: fixed;
            width: 100%;
        }

        .form-table th,
        .form-table td {
            border: 2px solid #1f2147;
            font-size: 15px;
            height: .34in;
            padding: .04in .06in;
            text-align: center;
            vertical-align: middle;
            word-break: break-word;
        }

        .form-table th {
            font-style: italic;
            font-weight: 900;
            text-transform: uppercase;
        }

        .form-table td {
            font-size: 13px;
            text-align: left;
        }

        .signatures {
            bottom: .32in;
            display: grid;
            gap: .45in;
            grid-template-columns: repeat(3, 1fr);
            left: .25in;
            position: absolute;
            right: .25in;
            z-index: 1;
        }

        .signature {
            color: #151a3d;
            font-size: 14px;
            font-style: italic;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .signature .sigline {
            border-bottom: 2px solid #1f2147;
            display: block;
            height: .24in;
            margin-bottom: .03in;
            text-align: left;
        }

        @media print {
            body { background: #fff; }
            .screen-actions { display: none; }
            .sheet {
                height: 11in;
                margin: 0;
                padding: .42in .5in;
                width: 8.5in;
            }
            .signatures {
                bottom: .52in;
                left: .58in;
                right: .58in;
            }
            table, tr, td, th, .row, .signatures {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    @php
        $monthNames = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $monthName = $cartaPorte->fecha ? $monthNames[(int) $cartaPorte->fecha->format('n')] : '';
        $copies = 3;
    @endphp

    <div class="screen-actions">
        <button class="btn" type="button" onclick="cleanPrint()">Imprimir</button>
        <a class="btn" href="{{ route('cartas-porte.create') }}">Nueva carta</a>
        <a class="btn secondary" href="{{ route('cartas-porte.show', $cartaPorte) }}">Volver</a>
    </div>

    @for ($copy = 1; $copy <= $copies; $copy++)
        <section class="sheet {{ $copy < $copies ? 'copy-break' : '' }}">
            @if (file_exists(public_path('images/logo-empresa.png')))
                <img class="watermark" src="{{ asset('images/logo-empresa.png') }}" alt="">
            @endif

            <header class="header">
                <div class="logo">
                    @if (file_exists(public_path('images/logo-empresa.png')))
                        <img class="logo-image" src="{{ asset('images/logo-empresa.png') }}" alt="Multiservicios W. Orellana">
                    @else
                        <div class="logo-main">Multiservicios</div>
                        <div class="logo-sub">Orellana</div>
                        <div class="logo-caption">Transportes de Carga y Tramites Aduanales</div>
                    @endif
                </div>
                <div class="company">
                    <div class="owner">Wilfredy Abel Orellana</div>
                    <div>Comercial Plan de Prestaciones Local No. 05</div>
                    <div>Santo Tomas de Castilla, Tels: 7948 3939, 5923 8160</div>
                    <div>transorellana@hotmail.com</div>
                </div>
                <div class="number">{{ $cartaPorte->numero_correlativo }}</div>
            </header>

            <div class="title">"CARTA DE PORTE"</div>

            <div class="row date">
                <span class="label">Santo Tomas de C.</span>
                <span class="label">de</span>
                <span class="line">{{ $cartaPorte->fecha?->format('d') }}</span>
                <span class="label">de</span>
                <span class="line">{{ $monthName }}</span>
                <span class="label">del 20</span>
                <span class="line">{{ $cartaPorte->fecha?->format('y') }}</span>
            </div>

            <div class="row one">
                <span class="label">Consignatario:</span>
                <span class="line">{{ $cartaPorte->consignatario_texto }}</span>
            </div>

            <div class="row two">
                <span class="label">Procedencia</span>
                <span class="line">{{ $cartaPorte->procedencia_texto }}</span>
                <span class="label">Destino</span>
                <span class="line">{{ $cartaPorte->destino }}</span>
            </div>

            <div class="row docs">
                <span class="label">Poliza</span>
                <span class="line">{{ $cartaPorte->poliza }}</span>
                <span class="label">ID</span>
                <span class="boxline">{{ $cartaPorte->id_documento }}</span>
                <span class="label">DA</span>
                <span class="boxline">{{ $cartaPorte->da }}</span>
                <span class="label">MI</span>
                <span class="boxline">{{ $cartaPorte->mi }}</span>
            </div>

            <div class="row contact">
                <span class="label">Comunicarse con</span>
                <span class="line">{{ $cartaPorte->contacto }}</span>
                <span class="plain-label">Tel</span>
                <span class="line">{{ $cartaPorte->telefono }}</span>
            </div>

            <table class="form-table">
                <colgroup>
                    <col style="width: 21%;">
                    <col style="width: 13%;">
                    <col style="width: 51%;">
                    <col style="width: 15%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Contenedor</th>
                        <th>Bultos</th>
                        <th>Contenido</th>
                        <th>Peso Kls.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $cartaPorte->contenedor }}</td>
                        <td>{{ $cartaPorte->bultos }}</td>
                        <td>{{ $cartaPorte->contenido }}</td>
                        <td>{{ $cartaPorte->peso_kls }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table">
                <colgroup>
                    <col style="width: 21%;">
                    <col style="width: 38%;">
                    <col style="width: 41%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Vapor</th>
                        <th>Fecha</th>
                        <th>B/L</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $cartaPorte->vapor }}</td>
                        <td>{{ $cartaPorte->fecha_vapor?->format('d/m/Y') }}</td>
                        <td>{{ $cartaPorte->bl }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table">
                <colgroup>
                    <col style="width: 38%;">
                    <col style="width: 24%;">
                    <col style="width: 38%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Piloto</th>
                        <th>Cabezal placas</th>
                        <th>Licencia</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $cartaPorte->piloto_texto }}</td>
                        <td>{{ $cartaPorte->cabezal_texto }}</td>
                        <td>{{ $cartaPorte->licencia_texto }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="signatures">
                <div class="signature"><span class="sigline">F:</span>Piloto</div>
                <div class="signature"><span class="sigline">F:</span>Por la empresa</div>
                <div class="signature"><span class="sigline">F:</span>Recibi conforme</div>
            </div>
        </section>
    @endfor

    @if (request()->boolean('autoprint'))
        <script>
            window.addEventListener('load', () => cleanPrint());
        </script>
    @endif

    <script>
        function cleanPrint() {
            const originalTitle = document.title;
            const cleanUrl = window.location.pathname;

            document.title = ' ';

            if (window.history.replaceState && window.location.search) {
                window.history.replaceState({}, '', cleanUrl);
            }

            window.print();
            window.setTimeout(() => {
                document.title = originalTitle;
            }, 500);
        }
    </script>
</body>
</html>
