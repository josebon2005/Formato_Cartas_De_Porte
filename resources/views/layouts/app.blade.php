<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cartas de Porte')</title>
    <script>
        (() => {
            try {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (error) {
                // localStorage may be unavailable in private or restricted browser modes.
            }
        })();
    </script>
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
            --on-dark: #fff;
            --button-text: #fff;
            --body-bg: #f1f2f4;
            --surface: #fff;
            --surface-soft: #f7f7f8;
            --text: #111827;
            --label: #344054;
            --field-bg: #fff;
            --field-border: #cbd5e1;
            --table-head: #171316;
            --table-row-hover: #fafafa;
            --shadow: 0 10px 28px rgba(17, 24, 39, .06);
        }

        html.dark-mode {
            --muted: #a4acba;
            --line: #343a46;
            --soft: #20242c;
            --white: #161a22;
            --body-bg: #101217;
            --surface: #171b23;
            --surface-soft: #20242c;
            --text: #eef2f7;
            --label: #d7dce5;
            --field-bg: #10141b;
            --field-border: #485160;
            --table-head: #25090d;
            --table-row-hover: #1d222b;
            --shadow: 0 14px 32px rgba(0, 0, 0, .34);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--body-bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        a { color: inherit; text-decoration: none; }

        .shell { min-height: 100vh; }

        .topbar {
            background: linear-gradient(90deg, #0f0e10 0%, #211d20 62%, #3a0b10 100%);
            border-bottom: 4px solid var(--accent);
            color: var(--on-dark);
            padding: 12px 24px;
        }

        .topbar-inner {
            align-items: center;
            display: flex;
            gap: 16px;
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
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
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
            color: var(--label);
            display: block;
            font-weight: 700;
            margin-bottom: 6px;
        }

        input, textarea, select {
            background: var(--field-bg);
            border: 1px solid var(--field-border);
            border-radius: 6px;
            color: var(--text);
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

        .catalog-field {
            position: relative;
        }

        .catalog-suggestions {
            background: var(--surface);
            border: 1px solid var(--field-border);
            border-radius: 6px;
            box-shadow: 0 12px 24px rgba(17, 24, 39, .14);
            display: none;
            left: 0;
            max-height: 220px;
            overflow-y: auto;
            position: absolute;
            right: 0;
            top: calc(100% + 4px);
            z-index: 10;
        }

        .catalog-suggestions.open {
            display: block;
        }

        .catalog-suggestion {
            background: var(--surface);
            border: 0;
            border-bottom: 1px solid var(--line);
            color: var(--text);
            cursor: pointer;
            display: block;
            font: inherit;
            padding: 10px 12px;
            text-align: left;
            width: 100%;
        }

        .catalog-suggestion:hover,
        .catalog-suggestion:focus {
            background: var(--surface-soft);
            outline: none;
        }

        .btn {
            align-items: center;
            background: var(--ink);
            border: 1px solid var(--ink);
            border-radius: 6px;
            color: var(--button-text);
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
            background: var(--surface);
            border-color: var(--field-border);
            color: var(--text);
        }

        .btn.accent {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--button-text);
        }

        .btn.accent:hover {
            background: var(--accent-dark);
            border-color: var(--accent-dark);
        }

        .btn.warning {
            background: #f59e0b;
            border-color: #d97706;
            color: #111827;
        }

        .btn.warning:hover {
            background: #d97706;
            border-color: #b45309;
            color: #111827;
        }

        .btn.success {
            background: #067647;
            border-color: #067647;
            color: var(--button-text);
        }

        .btn.danger {
            background: var(--danger);
            border-color: var(--danger);
            color: var(--button-text);
        }

        .btn.small {
            min-height: 34px;
            padding: 7px 10px;
        }

        .btn.disabled,
        .btn:disabled {
            cursor: not-allowed;
            opacity: .62;
            pointer-events: none;
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

        tbody tr:hover {
            background: var(--table-row-hover);
        }

        th {
            background: var(--table-head);
            color: #fff;
            font-size: 12px;
            text-transform: uppercase;
        }

        .empty {
            color: var(--muted);
            padding: 24px;
            text-align: center;
        }

        .status-badge {
            border: 1px solid var(--line);
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            padding: 4px 9px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-badge.generated {
            background: #eef4ff;
            border-color: #b2ccff;
            color: #3538cd;
        }

        .status-badge.billed {
            background: #ecfdf3;
            border-color: #abefc6;
            color: #067647;
        }

        .status-badge.cancelled {
            background: #fef3f2;
            border-color: #fecdca;
            color: #b42318;
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

        .field-help {
            color: var(--muted);
            font-size: 12px;
            margin-top: 5px;
        }

        .detail-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .detail {
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 12px;
        }

        html.dark-mode .brand-logo {
            background: #fff;
        }

        html.dark-mode .alert {
            background: #0e2b20;
            border-color: #1b694c;
            color: #86efac;
        }

        html.dark-mode .alert.danger {
            background: #321315;
            border-color: #743236;
            color: #fca5a5;
        }

        html.dark-mode .status-badge.generated {
            background: #1d2447;
            border-color: #4b5cad;
            color: #b9c7ff;
        }

        html.dark-mode .status-badge.billed {
            background: #0e2b20;
            border-color: #1b694c;
            color: #86efac;
        }

        html.dark-mode .status-badge.cancelled {
            background: #321315;
            border-color: #743236;
            color: #fca5a5;
        }

        html.dark-mode .btn {
            box-shadow: none;
        }

        html.dark-mode .btn.warning {
            background: #fbbf24;
            border-color: #f59e0b;
            color: #111827;
        }

        html.dark-mode .btn.success {
            background: #16a34a;
            border-color: #15803d;
            color: #fff;
        }

        html.dark-mode input::placeholder,
        html.dark-mode textarea::placeholder {
            color: #8f99aa;
        }

        html.dark-mode select option {
            background: var(--field-bg);
            color: var(--text);
        }

        .theme-toggle {
            min-width: 118px;
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
            .topbar-inner { align-items: flex-start; flex-direction: column; }
            .filters, .grid, .detail-grid { grid-template-columns: 1fr; }
            .span-2, .span-3 { grid-column: auto; }
            .page-head { display: block; }
            .page-head .actions { margin-top: 12px; }
            table { min-width: 820px; }
            .table-wrap { overflow-x: auto; }
        }

        @media print {
            html.dark-mode,
            html.dark-mode body {
                background: #fff;
                color: #000;
            }

            html.dark-mode .panel,
            html.dark-mode .detail,
            html.dark-mode input,
            html.dark-mode textarea,
            html.dark-mode select {
                background: #fff;
                color: #000;
            }
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
                    <span>TRANSPORTES W. ORELLANA</span>
                </a>
                <nav class="actions">
                    <a class="btn secondary small" href="{{ route('cartas-porte.index') }}">Listado</a>
                    <a class="btn secondary small" href="{{ route('catalogos.index') }}">Datos</a>
                    <a class="btn secondary small" href="{{ route('facturacion.notas-gastos.index') }}">Facturacion</a>
                    <a class="btn accent small" href="{{ route('cartas-porte.create') }}">Nueva carta</a>
                    <button class="btn secondary small theme-toggle" type="button" data-theme-toggle>Modo oscuro</button>
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
    <script>
        (() => {
            const toggle = document.querySelector('[data-theme-toggle]');
            const root = document.documentElement;

            if (!toggle) {
                return;
            }

            const updateLabel = () => {
                toggle.textContent = root.classList.contains('dark-mode') ? 'Modo claro' : 'Modo oscuro';
            };

            toggle.addEventListener('click', () => {
                root.classList.toggle('dark-mode');

                try {
                    localStorage.setItem('theme', root.classList.contains('dark-mode') ? 'dark' : 'light');
                } catch (error) {
                    // The visual toggle should still work even if storage is unavailable.
                }

                updateLabel();
            });

            updateLabel();
        })();

        function askPrintCopies(url) {
            const copies = window.prompt('¿Cuántas copias desea imprimir?\n\nEscriba 1 para 1 copia o 2 para 2 copias.', '1');

            if (copies === null) {
                return;
            }

            if (copies !== '1' && copies !== '2') {
                alert('Solo se permite imprimir 1 copia o 2 copias.');
                return;
            }

            window.location.href = `${url}?copias=${copies}`;
        }

        function confirmAnulacion(form) {
            const confirmed = window.confirm('¿Está seguro de que desea anular esta Nota de Gastos? Utilice esta opción únicamente si la factura correspondiente también fue anulada.');

            if (!confirmed) {
                return false;
            }

            const motivo = window.prompt('Motivo de anulación (opcional):', '');
            const field = form.querySelector('[name="motivo_anulacion"]');

            if (field && motivo !== null) {
                field.value = motivo;
            }

            return true;
        }
    </script>
</body>
</html>
