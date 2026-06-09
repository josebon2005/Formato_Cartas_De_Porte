<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar | Cartas de Porte</title>
    <style>
        :root {
            --ink: #171316;
            --accent: #c9141f;
            --muted: #667085;
            --line: #d7dce5;
            --white: #fff;
        }

        * { box-sizing: border-box; }

        body {
            align-items: center;
            background:
                linear-gradient(135deg, rgba(23, 19, 22, .92), rgba(71, 13, 18, .9)),
                #171316;
            color: #111827;
            display: flex;
            font-family: Arial, Helvetica, sans-serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
            padding: 24px;
        }

        .login-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
            max-width: 420px;
            padding: 30px;
            width: 100%;
        }

        .logo {
            display: block;
            height: 92px;
            margin: 0 auto 18px;
            object-fit: contain;
            width: 132px;
        }

        h1 {
            font-size: 26px;
            margin: 0 0 6px;
            text-align: center;
        }

        h1::after {
            background: var(--accent);
            border-radius: 999px;
            content: "";
            display: block;
            height: 4px;
            margin: 12px auto 0;
            width: 58px;
        }

        p {
            color: var(--muted);
            margin: 0 0 24px;
            text-align: center;
        }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 6px;
        }

        input {
            border: 1px solid var(--line);
            border-radius: 6px;
            font: inherit;
            min-height: 42px;
            padding: 10px 12px;
            width: 100%;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(201, 20, 31, .14);
            outline: none;
        }

        .field { margin-bottom: 16px; }

        .row {
            align-items: center;
            display: flex;
            gap: 8px;
            margin-bottom: 18px;
        }

        .row input {
            min-height: auto;
            width: auto;
        }

        .btn {
            background: var(--accent);
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            min-height: 44px;
            width: 100%;
        }

        .error {
            color: #b42318;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <main class="login-card">
        @if (file_exists(public_path('images/logo-empresa.png')))
            <img class="logo" src="{{ asset('images/logo-empresa.png') }}" alt="Multiservicios W. Orellana">
        @endif

        <h1>Cartas de Porte</h1>
        <p>Ingresa para administrar el sistema.</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="field">
                <label for="email">Correo</label>
                <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}">
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <label class="row">
                <input name="remember" type="checkbox" value="1">
                Recordarme
            </label>

            <button class="btn" type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
