<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Sistema de Nomina Empresarial' }}</title>
    <style>
        :root {
            --azul-industrial: #0b2f5b;
            --azul-acento: #0f4c93;
            --gris-fondo: #eef3f8;
            --gris-placa: #f8fafc;
            --texto-base: #1f2937;
            --verde-ok: #2e7d32;
            --rojo-alerta: #b42318;
        }
        body {
            font-family: 'Source Sans Pro', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: radial-gradient(circle at 20% 0%, #ffffff 0%, var(--gris-fondo) 58%, #dde7f1 100%);
            color: var(--texto-base);
        }
        .contenedor {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
        }
        .barra {
            background: linear-gradient(90deg, #091f3f 0%, var(--azul-industrial) 45%, var(--azul-acento) 100%);
            color: white;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .barra a {
            color: white;
            text-decoration: none;
            margin-right: 16px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .tarjeta {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 8px 22px rgba(4, 24, 56, 0.08);
            margin-bottom: 18px;
            border: 1px solid #dde6f0;
        }
        .grilla {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
        }
        input, select {
            width: 100%;
            box-sizing: border-box;
            padding: 8px;
            border: 1px solid #d0d7e2;
            border-radius: 6px;
            margin-top: 4px;
            background: var(--gris-placa);
        }
        label {
            font-size: 14px;
            font-weight: 600;
        }
        .acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .boton {
            border: 0;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            color: white;
            background: var(--azul-acento);
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 700;
        }
        .boton.secundario { background: #48566d; }
        .boton.alerta { background: var(--rojo-alerta); }
        .tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .tabla th, .tabla td {
            border-bottom: 1px solid #e7ebf2;
            padding: 8px;
            text-align: left;
        }
        .mensaje {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }
        .mensaje.exito {
            background: #e8f7ee;
            color: var(--verde-ok);
        }
        .mensaje.error {
            background: #ffe8e8;
            color: #8f1d1d;
        }
        .barra-seccion {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .chip-usuario {
            font-size: 12px;
            background: rgba(255, 255, 255, 0.14);
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.26);
        }
    </style>
</head>
<body>
    <div class="barra">
        <div class="barra-seccion">
            <div><strong>Sistema de Nomina Empresarial Mexico</strong></div>
            @auth
                <div class="chip-usuario">{{ auth()->user()->nombre_usuario ?? auth()->user()->email }} | {{ auth()->user()->rol ?? 'USUARIO' }}</div>
            @endauth
        </div>
        @auth
            <nav>
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('usuarios.index') }}">Usuarios</a>
                <a href="{{ route('clientes.index') }}">Clientes</a>
                <a href="{{ route('empleados.index') }}">Empleados</a>
                <a href="{{ route('nominas.index') }}">Nominas</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button class="boton alerta" type="submit">Salir</button>
                </form>
            </nav>
        @endauth
    </div>

    <div class="contenedor">
        @if(session('exito'))
            <div class="mensaje exito">{{ session('exito') }}</div>
        @endif

        @if(session('error'))
            <div class="mensaje error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="mensaje error">
                <strong>Hay errores en el formulario:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('contenido')
    </div>
</body>
</html>
