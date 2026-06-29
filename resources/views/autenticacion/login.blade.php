<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | Sistema de Nomina</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: 'Source Sans Pro', 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(150deg, #072347 0%, #0c3568 55%, #1f6cb3 100%);
            color: #0b1a2f;
        }
        .contenedor {
            width: min(950px, 92vw);
            background: #f9fbfe;
            border-radius: 16px;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            overflow: hidden;
            box-shadow: 0 16px 45px rgba(5, 18, 38, 0.4);
        }
        .marca {
            background: linear-gradient(170deg, #071f3e 0%, #0d3f79 70%, #2b74b8 100%);
            color: white;
            padding: 34px;
        }
        .marca h1 {
            margin: 0 0 8px 0;
            font-size: 29px;
            line-height: 1.1;
        }
        .marca p {
            margin-top: 0;
            opacity: 0.9;
        }
        .insignia {
            margin-top: 18px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.08);
        }
        .panel {
            padding: 34px;
        }
        label { display: block; font-weight: 700; margin-top: 10px; }
        input {
            width: 100%;
            box-sizing: border-box;
            margin-top: 5px;
            border: 1px solid #cdd8e7;
            background: #eef4fb;
            border-radius: 7px;
            padding: 10px;
        }
        .boton {
            margin-top: 18px;
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: #0f4c93;
            color: white;
            font-weight: 700;
            padding: 11px;
            cursor: pointer;
        }
        .error {
            margin: 10px 0;
            background: #ffe8e8;
            border: 1px solid #ffc8c8;
            color: #8f1d1d;
            border-radius: 8px;
            padding: 10px;
            font-size: 14px;
        }
        @media (max-width: 820px) {
            .contenedor {
                grid-template-columns: 1fr;
            }
            .marca {
                order: 2;
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
                <div class="error" style="background:#e8f7ee;border-color:#b7e6c2;color:#196a35;">{{ session('exito') }}</div>
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
