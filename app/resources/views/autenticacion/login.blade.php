<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | Sistema de Nomina</title>
    <style>
        :root {
            --fondo: #f5f7fa;
            --superficie: #ffffff;
            --borde: #d9e1ea;
            --texto: #1f2937;
            --texto-sec: #687484;
            --acento: #2368a2;
            --acento-hover: #1c5788;
            --ok: #2f7d52;
            --error: #9a3346;
            --sombra: 0 14px 36px rgba(16, 30, 52, 0.1);
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: 'Source Sans Pro', 'Segoe UI', Tahoma, sans-serif;
            background: var(--fondo);
            color: var(--texto);
        }
        .contenedor {
            width: min(950px, 92vw);
            background: var(--superficie);
            border-radius: 18px;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            overflow: hidden;
            box-shadow: var(--sombra);
            border: 1px solid var(--borde);
        }
        .marca {
            background: linear-gradient(170deg, #f7f9fc 0%, #edf2f8 100%);
            color: var(--texto);
            padding: 34px;
            border-right: 1px solid var(--borde);
        }
        .marca h1 {
            margin: 0 0 8px 0;
            font-size: 29px;
            line-height: 1.1;
            color: #2a3a4d;
        }
        .marca p {
            margin-top: 0;
            color: var(--texto-sec);
        }
        .insignia {
            margin-top: 18px;
            border: 1px solid var(--borde);
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            background: #ffffff;
            color: #4d5d70;
        }
        .panel {
            padding: 34px;
        }
        .panel h2 {
            margin-top: 0;
            color: #2a3a4d;
        }
        label { display: block; font-weight: 700; margin-top: 10px; }
        input {
            width: 100%;
            box-sizing: border-box;
            margin-top: 5px;
            border: 1px solid #ced7e2;
            background: #fbfcfe;
            border-radius: 10px;
            padding: 11px;
        }
        .boton {
            margin-top: 18px;
            width: 100%;
            border: 0;
            border-radius: 10px;
            background: var(--acento);
            color: white;
            font-weight: 700;
            padding: 11px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .boton:hover {
            background: var(--acento-hover);
        }
        .error {
            margin: 10px 0;
            background: #fbeff2;
            border: 1px solid #f0c8d1;
            color: var(--error);
            border-radius: 10px;
            padding: 10px;
            font-size: 14px;
        }
        @media (max-width: 820px) {
            .contenedor {
                grid-template-columns: 1fr;
            }
            .marca {
                order: 2;
                border-right: 0;
                border-top: 1px solid var(--borde);
            }
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <section class="marca">
            <h1>Nomina Empresarial Mexico</h1>
            <p>Operacion orientada a plantas industriales y corporativos de alta demanda.</p>
            <div class="insignia">
                Control de usuarios, clientes, empleados, incidencias y nominas con trazabilidad operativa.
            </div>
        </section>

        <section class="panel">
            <h2>Iniciar sesion</h2>

            @if(session('exito'))
                <div class="error" style="background:#edf8f2;border-color:#c9e6d5;color:#2f7d52;">{{ session('exito') }}</div>
            @endif

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.iniciar') }}">
                @csrf
                <label>Usuario o correo</label>
                <input name="acceso" value="{{ old('acceso') }}" required>

                <label>Contrasena</label>
                <input type="password" name="password" required>

                <label style="font-weight:600; margin-top:14px; display:flex; gap:8px; align-items:center;">
                    <input type="checkbox" name="recordarme" value="1" style="width:auto; margin:0;"> Recordarme en este equipo
                </label>

                <button class="boton" type="submit">Acceder al sistema</button>
            </form>
        </section>
    </div>
</body>
</html>
