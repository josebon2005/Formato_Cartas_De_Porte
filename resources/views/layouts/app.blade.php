<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cartas de Porte')</title>
    <style>
        :root {
            --ink: #171316;
            --ink-2: #262126;
            --muted: #667085;
            --line: #d7dce5;
            --soft: #f7f7f8;
            --accent: #c9141f;
            --accent-dark: #9f111a;
            --danger: #b42318;
            --white: #fff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f1f2f4;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        a { color: inherit; text-decoration: none; }

        .shell { min-height: 100vh; }

        .topbar {
            background: linear-gradient(90deg, #0f0e10 0%, #211d20 62%, #3a0b10 100%);
            border-bottom: 4px solid var(--accent);
            color: var(--white);
            padding: 12px 24px;
        }

        .topbar-inner {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1180px;
        }

        .brand {
            align-items: center;
            display: inline-flex;
            gap: 12px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .brand-logo {
            background: #fff;
            border-radius: 6px;
            height: 48px;
            object-fit: contain;
            padding: 4px;
            width: 58px;
        }

        .container {
            margin: 0 auto;
            max-width: 1180px;
            padding: 28px 20px 48px;
        }

        .page-head {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 28px;
            margin: 0 0 6px;
        }

        h1::after {
            background: var(--accent);
            border-radius: 999px;
            content: "";
            display: block;
            height: 4px;
            margin-top: 8px;
            width: 56px;
        }

        .subtle { color: var(--muted); margin: 0; }

        .panel {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(17, 24, 39, .06);
            padding: 20px;
        }

        .filters {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            margin-bottom: 18px;
        }

        .grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .span-2 { grid-column: span 2; }
        .span-3 { grid-column: span 3; }

        label {
            color: #344054;
            display: block;
            font-weight: 700;
            margin-bottom: 6px;
        }

        input, textarea, select {
            background: var(--white);
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #111827;
            font: inherit;
            min-height: 40px;
            padding: 9px 10px;
            width: 100%;
        }

        textarea { min-height: 92px; resize: vertical; }

        input:focus, textarea:focus, select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(201, 20, 31, .14);
            outline: none;
        }

        .catalog-input {
            align-items: stretch;
            display: flex;
            gap: 8px;
        }

        .catalog-input input { min-width: 0; }

        .btn {
            align-items: center;
            background: var(--ink);
            border: 1px solid var(--ink);
            border-radius: 6px;
            color: var(--white);
            cursor: pointer;
            display: inline-flex;
            font-weight: 700;
            gap: 8px;
            justify-content: center;
            min-height: 40px;
            padding: 9px 14px;
            white-space: nowrap;
        }

        .btn:hover {
            filter: brightness(.95);
        }

        .btn.secondary {
            background: var(--white);
            border-color: #cbd5e1;
            color: #111827;
        }

        .btn.accent {
            background: var(--accent);
            border-color: var(--accent);
        }

        .btn.accent:hover {
            background: var(--accent-dark);
            border-color: var(--accent-dark);
        }

        .btn.danger {
            background: var(--danger);
            border-color: var(--danger);
        }

        .btn.small {
            min-height: 34px;
            padding: 7px 10px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .form-actions {
            border-top: 1px solid var(--line);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 22px;
            padding-top: 18px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border-bottom: 1px solid var(--line);
            padding: 12px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #171316;
            color: #fff;
            font-size: 12px;
            text-transform: uppercase;
        }

        .empty {
            color: var(--muted);
            padding: 24px;
            text-align: center;
        }

        .alert {
            background: #ecfdf3;
            border: 1px solid #abefc6;
            border-radius: 8px;
            color: #067647;
            margin-bottom: 16px;
            padding: 12px 14px;
        }

        .alert.danger {
            background: #fef3f2;
            border-color: #fecdca;
            color: var(--danger);
        }

        .error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 5px;
        }

        .detail-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .detail {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 12px;
        }

        .detail strong {
            color: var(--muted);
            display: block;
            font-size: 12px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .pagination { margin-top: 18px; }

        @media (max-width: 900px) {
            .filters, .grid, .detail-grid { grid-template-columns: 1fr; }
            .span-2, .span-3 { grid-column: auto; }
            .page-head { display: block; }
            .page-head .actions { margin-top: 12px; }
            table { min-width: 820px; }
            .table-wrap { overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <div class="topbar-inner">
                <a class="brand" href="{{ route('cartas-porte.index') }}">
                    @if (file_exists(public_path('images/logo-empresa.png')))
                        <img class="brand-logo" src="{{ asset('images/logo-empresa.png') }}" alt="Multiservicios W. Orellana">
                    @endif
                    <span>Cartas de Porte</span>
                </a>
                <nav class="actions">
                    <a class="btn secondary small" href="{{ route('cartas-porte.index') }}">Listado</a>
                    <a class="btn secondary small" href="{{ route('catalogos.index') }}">Datos</a>
                    <a class="btn accent small" href="{{ route('cartas-porte.create') }}">Nueva carta</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn secondary small" type="submit">Salir</button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="container">
            @if (session('status'))
                <div class="alert">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
