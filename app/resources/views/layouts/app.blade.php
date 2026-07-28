<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Sistema de Nomina Empresarial' }}</title>
    <style>
        :root {
            --fondo-principal: #f5f7fa;
            --fondo-superficie: #ffffff;
            --fondo-panel: #f1f3f6;
            --borde-suave: #d8dee6;
            --texto-base: #1f2937;
            --texto-secundario: #667282;
            --acento: #2368a2;
            --acento-hover: #1c5788;
            --ok: #2f7d52;
            --error: #a83a4b;
            --sombra: 0 8px 24px rgba(16, 30, 52, 0.07);
        }
        body {
            font-family: 'Source Sans Pro', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: var(--fondo-principal);
            color: var(--texto-base);
            min-height: 100vh;
        }

        .aplicacion {
            display: grid;
            grid-template-columns: 290px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--fondo-panel);
            border-right: 1px solid var(--borde-suave);
            display: flex;
            flex-direction: column;
            height: 100vh;
            box-shadow: 2px 0 16px rgba(16, 30, 52, 0.04);
            position: sticky;
            top: 0;
            overflow-y: auto;
        }

        .sidebar-top {
            background: #7c8898;
            height: 14px;
        }

        .sidebar-marca {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--borde-suave);
        }

        .sidebar-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #dde6f2;
            color: #2d4059;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid #c6d3e3;
        }

        .sidebar-titulo {
            font-size: 26px;
            line-height: 1;
            margin: 0;
            color: #2b3b4f;
            font-weight: 700;
        }

        .sidebar-subtitulo {
            margin: 3px 0 0;
            color: var(--texto-secundario);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .sidebar-orga {
            margin: 12px 10px;
            padding: 12px;
            border-top: 1px solid var(--borde-suave);
            border-bottom: 1px solid var(--borde-suave);
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 10px;
            background: #f8fafc;
        }

        .sidebar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(145deg, #d5dde8, #9fb3c8);
            border: 2px solid #ecf1f7;
        }

        .sidebar-orga strong {
            font-size: 16px;
            color: #2a3b50;
            letter-spacing: 0.2px;
        }

        .sidebar-nav {
            padding: 6px 10px 14px;
            flex: 1;
        }

        .sidebar-nav-titulo {
            margin: 8px 8px 10px;
            color: var(--texto-secundario);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 11px 12px;
            margin-bottom: 4px;
            text-decoration: none;
            color: var(--texto-base);
            border-radius: 8px;
            border: 1px solid transparent;
            transition: background 0.2s ease, border-color 0.2s ease;
            font-size: 15px;
        }

        .sidebar-link:hover {
            background: #fafbfd;
            border-color: #d9e1ea;
            transform: translateX(2px);
        }

        .sidebar-link.activo {
            background: #ffffff;
            border-color: #cbd6e2;
            box-shadow: 0 2px 8px rgba(16, 30, 52, 0.06);
        }

        .sidebar-link.activo .menu-icono {
            background: var(--acento);
        }

        .sidebar-link .izq {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .menu-icono {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            background: #a7b8ca;
            flex: 0 0 auto;
        }

        .menu-texto {
            font-size: 16px;
            color: #2a3a4d;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-flecha {
            color: #6e7b8b;
            font-size: 11px;
            font-weight: 700;
            flex: 0 0 auto;
            width: 18px;
            height: 18px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: #e8edf3;
            line-height: 1;
        }

        .sidebar-link.activo .menu-flecha {
            color: var(--acento);
            background: #deebf8;
        }

        .sidebar-link:hover .menu-flecha {
            background: #dfe8f2;
        }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--borde-suave);
            position: sticky;
            bottom: 0;
            background: var(--fondo-panel);
        }

        .chip-usuario {
            font-size: 12px;
            background: #ffffff;
            color: #425162;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #d5dce5;
            margin-bottom: 10px;
            word-break: break-word;
        }

        .contenido-principal {
            min-width: 0;
            background: var(--fondo-principal);
        }

        .contenido-header {
            background: #ffffff;
            border-bottom: 1px solid #dce3eb;
            padding: 12px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .contenido-header h2 {
            margin: 0;
            font-size: 18px;
            color: #2a3a4d;
        }

        .contenido-header small {
            color: var(--texto-secundario);
            font-weight: 600;
        }

        .contenedor {
            max-width: 1400px;
            margin: 0 auto;
            padding: 22px;
        }

        .tarjeta {
            background: var(--fondo-superficie);
            border-radius: 14px;
            padding: 18px;
            box-shadow: var(--sombra);
            margin-bottom: 18px;
            border: 1px solid #dbe2ea;
        }
        .grilla {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
        }
        input, select {
            width: 100%;
            box-sizing: border-box;
            padding: 9px 10px;
            border: 1px solid #ced7e2;
            border-radius: 10px;
            margin-top: 4px;
            background: #fbfcfe;
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
            border-radius: 10px;
            padding: 9px 13px;
            cursor: pointer;
            color: white;
            background: var(--acento);
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 700;
            transition: background 0.2s ease;
        }
        .boton:hover { background: var(--acento-hover); }
        .boton.secundario { background: #667282; }
        .boton.secundario:hover { background: #586576; }
        .boton.alerta { background: var(--error); }
        .boton.alerta:hover { background: #912f3f; }
        .tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .tabla th, .tabla td {
            border-bottom: 1px solid #e5eaf1;
            padding: 9px;
            text-align: left;
        }
        .tabla th {
            color: #506073;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .mensaje {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 12px;
        }
        .mensaje.exito {
            background: #edf8f2;
            color: var(--ok);
            border: 1px solid #c9e6d5;
        }
        .mensaje.error {
            background: #fbeff2;
            color: #8b2a3b;
            border: 1px solid #f0c8d1;
        }
        .sidebar .boton.alerta {
            width: 100%;
            text-align: center;
            padding: 10px;
        }

        nav[role="navigation"] svg {
            width: 14px;
            height: 14px;
        }

        nav[role="navigation"] a,
        nav[role="navigation"] span {
            font-size: 13px;
        }

        @media (max-width: 980px) {
            .aplicacion {
                grid-template-columns: 1fr;
            }

            .sidebar {
                min-height: auto;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--borde-suave);
                box-shadow: none;
                position: static;
                overflow: visible;
            }

            .sidebar-nav {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 6px;
                flex: initial;
            }

            .sidebar-link {
                margin-bottom: 0;
            }

            .contenedor {
                padding: 16px;
            }

            .contenido-header {
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="aplicacion">
        @auth
            <aside class="sidebar">
                <div class="sidebar-top"></div>

                <div class="sidebar-marca">
                    <div class="sidebar-logo">SN</div>
                    <div>
                        <h1 class="sidebar-titulo">Nomina</h1>
                        <p class="sidebar-subtitulo">Plataforma institucional</p>
                    </div>
                </div>

                <div class="sidebar-orga">
                    <div class="sidebar-avatar"></div>
                    <strong>COMITE DE NOMINAS</strong>
                </div>

                <nav class="sidebar-nav">
                    <p class="sidebar-nav-titulo">Menu principal</p>

                    <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'activo' : '' }}" href="{{ route('dashboard') }}">
                        <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Dashboard</span></span>
                        <span class="menu-flecha">&gt;</span>
                    </a>
                    @if(auth()->user()?->tieneAlgunRol(['JEFE_AREA', 'SECRETARIA']))
                        <a class="sidebar-link {{ request()->routeIs('usuarios.*') ? 'activo' : '' }}" href="{{ route('usuarios.index') }}">
                            <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Usuarios</span></span>
                            <span class="menu-flecha">&gt;</span>
                        </a>
                    @endif
                    @if(auth()->user()?->tieneAlgunRol(['JEFE_AREA', 'SECRETARIA', 'SUPERVISOR', 'VENDEDOR']))
                        <a class="sidebar-link {{ request()->routeIs('clientes.*') ? 'activo' : '' }}" href="{{ route('clientes.index') }}">
                            <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Clientes</span></span>
                            <span class="menu-flecha">&gt;</span>
                        </a>
                    @endif
                    @if(auth()->user()?->tieneAlgunRol(['JEFE_AREA', 'SECRETARIA', 'SUPERVISOR']))
                        <a class="sidebar-link {{ request()->routeIs('empleados.*') ? 'activo' : '' }}" href="{{ route('empleados.index') }}">
                            <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Empleados</span></span>
                            <span class="menu-flecha">&gt;</span>
                        </a>
                    @endif
                    @if(auth()->user()?->tieneAlgunRol(['JEFE_AREA', 'CONTADOR', 'SUPERVISOR', 'SECRETARIA']))
                        <a class="sidebar-link {{ request()->routeIs('catalogos-nomina.*') || request()->routeIs('periodos-nomina.*') || request()->routeIs('conceptos-nomina.*') ? 'activo' : '' }}" href="{{ route('catalogos-nomina.index') }}">
                            <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Catalogos de nomina</span></span>
                            <span class="menu-flecha">&gt;</span>
                        </a>
                        <a class="sidebar-link {{ request()->routeIs('incidencias.*') ? 'activo' : '' }}" href="{{ route('incidencias.index') }}">
                            <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Incidencias</span></span>
                            <span class="menu-flecha">&gt;</span>
                        </a>
                        <a class="sidebar-link {{ request()->routeIs('nominas.*') ? 'activo' : '' }}" href="{{ route('nominas.index') }}">
                            <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Nominas</span></span>
                            <span class="menu-flecha">&gt;</span>
                        </a>
                    @endif
                    @if(auth()->user()?->tieneAlgunRol(['JEFE_AREA', 'CONTADOR', 'SUPERVISOR', 'SECRETARIA']))
                        <a class="sidebar-link {{ request()->routeIs('reportes.*') ? 'activo' : '' }}" href="{{ route('reportes.index') }}">
                            <span class="izq"><span class="menu-icono"></span><span class="menu-texto">Reportes</span></span>
                            <span class="menu-flecha">&gt;</span>
                        </a>
                    @endif
                </nav>

                <div class="sidebar-footer">
                    <div class="chip-usuario">{{ auth()->user()->nombre_usuario ?? auth()->user()->email }} | {{ auth()->user()->rol ?? 'USUARIO' }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="boton alerta" type="submit">Salir</button>
                    </form>
                </div>
            </aside>
        @endauth

        <main class="contenido-principal">
            @auth
                <header class="contenido-header">
                    <h2>Sistema de Nomina Empresarial</h2>
                    <small>Operacion diaria y control administrativo</small>
                </header>
            @endauth

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
        </main>
    </div>
</body>
</html>
