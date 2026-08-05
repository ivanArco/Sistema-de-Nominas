@extends('layouts.app')

@section('contenido')
    <style>
        .usuarios-shell { display: grid; gap: 14px; }

        .usuarios-head {
            border: 1px solid #d9e5ef;
            border-radius: 16px;
            background: linear-gradient(120deg, #edf6ff 0%, #f6fff7 100%);
            padding: 16px;
        }

        .usuarios-head h2 { margin: 0; color: #1c3851; }
        .usuarios-head p { margin: 6px 0 0; color: #54708a; }

        .usuarios-resumen {
            margin-top: 10px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pill {
            border: 1px solid #d8e4ef;
            border-radius: 999px;
            background: #fff;
            color: #294862;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 10px;
        }

        .usuarios-filtros {
            background: #fff;
            border: 1px solid #dde8f2;
            border-radius: 14px;
            padding: 12px;
        }

        .filtros-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: 2fr 1fr 1fr 1fr;
        }

        .filtros-grid .bloque {
            background: #fbfdff;
            border: 1px solid #e6edf5;
            border-radius: 10px;
            padding: 9px;
        }

        .filtros-acciones {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .usuarios-lista {
            border: 1px solid #dde8f2;
            border-radius: 14px;
            background: #fff;
            padding: 10px;
            overflow-x: auto;
        }

        .nombre-usuario { font-weight: 700; color: #1f3f5a; }
        .mini { font-size: 12px; color: #5e758b; margin-top: 2px; }

        .badge {
            display: inline-block;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border: 1px solid transparent;
        }

        .badge-rol { background: #eff6ff; border-color: #d5e5fb; color: #22527a; }
        .badge-activo-si { background: #eaf9ef; border-color: #cdebd8; color: #1f6d3f; }
        .badge-activo-no { background: #fdeff0; border-color: #f3d4d7; color: #8d2e39; }

        .acciones-col { min-width: 230px; }

        details.eliminar-box {
            margin-top: 8px;
            border: 1px solid #f0d2d6;
            border-radius: 8px;
            background: #fff9fa;
            padding: 6px;
        }

        details.eliminar-box summary {
            cursor: pointer;
            color: #8d2e39;
            font-weight: 700;
        }

        .eliminar-grid {
            margin-top: 8px;
            display: grid;
            gap: 6px;
            grid-template-columns: 1fr;
        }

        @media (max-width: 980px) {
            .filtros-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 620px) {
            .filtros-grid { grid-template-columns: 1fr; }
        }
    </style>

    @php
        $totalPagina = $usuarios->count();
        $activosPagina = $usuarios->where('activo', true)->count();
        $inactivosPagina = $totalPagina - $activosPagina;
    @endphp

    <div class="usuarios-shell">
        <section class="usuarios-head">
            <h2>Usuarios y Accesos</h2>
            <p>Vista simple para consultar rapidamente el estado de cada cuenta.</p>

            <div class="usuarios-resumen">
                <span class="pill">Registros: {{ number_format($totalPagina) }}</span>
                <span class="pill">Activos: {{ number_format($activosPagina) }}</span>
                <span class="pill">Inactivos: {{ number_format($inactivosPagina) }}</span>
            </div>
        </section>

        <section class="usuarios-filtros">
            <form method="GET" action="{{ route('usuarios.index') }}">
                <div class="filtros-grid">
                    <div class="bloque">
                        <label>Busqueda general</label>
                        <input name="texto" value="{{ $filtros['texto'] ?? '' }}" placeholder="Nombre, correo, CURP, NSS">
                    </div>
                    <div class="bloque">
                        <label>Rol</label>
                        <select name="rol">
                            <option value="">Todos</option>
                            @foreach(\App\Models\User::rolesDisponibles() as $rol)
                                <option value="{{ $rol }}" @selected(($filtros['rol'] ?? '') === $rol)>{{ $rol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bloque">
                        <label>Activo</label>
                        <select name="activo">
                            <option value="">Todos</option>
                            <option value="1" @selected(($filtros['activo'] ?? '') === '1')>Si</option>
                            <option value="0" @selected(($filtros['activo'] ?? '') === '0')>No</option>
                        </select>
                    </div>
                    <div class="bloque">
                        <label>Estado</label>
                        <input name="estado" value="{{ $filtros['estado'] ?? '' }}">
                    </div>
                    <div class="bloque">
                        <label>Fecha contratacion desde</label>
                        <input type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}">
                    </div>
                    <div class="bloque">
                        <label>Fecha contratacion hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}">
                    </div>
                </div>

                <div class="filtros-acciones">
                    <button class="boton" type="submit">Consultar</button>
                    <a class="boton secundario" href="{{ route('usuarios.index') }}">Limpiar</a>
                    @if($puedeGestionar)
                        <a class="boton" href="{{ route('usuarios.create') }}">Nuevo usuario</a>
                    @endif
                    <a class="boton secundario" href="{{ route('usuarios.reporte.pdf', request()->query()) }}">Exportar PDF</a>
                </div>
            </form>
        </section>

        <section class="usuarios-lista">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Contacto</th>
                        <th>Area / Estado</th>
                        <th>Activo</th>
                        <th class="acciones-col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td>
                                <div class="nombre-usuario">{{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}</div>
                                <div class="mini">CURP: {{ $usuario->curp }}</div>
                                <div class="mini"><span class="badge badge-rol">{{ $usuario->rol }}</span></div>
                            </td>
                            <td>
                                <div>{{ $usuario->email }}</div>
                                <div class="mini">Tel: {{ $usuario->telefono_contacto_1 }}</div>
                            </td>
                            <td>
                                <div>{{ $usuario->area_contratacion }}</div>
                                <div class="mini">{{ $usuario->estado }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $usuario->activo ? 'badge-activo-si' : 'badge-activo-no' }}">
                                    {{ $usuario->activo ? 'Si' : 'No' }}
                                </span>
                            </td>
                            <td class="acciones-col">
                                <div class="acciones">
                                    @if($puedeGestionar)
                                        <a class="boton secundario" href="{{ route('usuarios.edit', $usuario->id) }}">Editar</a>
                                    @endif
                                </div>

                                @if($puedeEliminar)
                                    <details class="eliminar-box">
                                        <summary>Eliminar usuario</summary>
                                        <form method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}" class="eliminar-grid">
                                            @csrf
                                            @method('DELETE')
                                            <input name="usuario_supervisor" placeholder="Supervisor o jefe de area" required>
                                            <input type="password" name="contrasena_supervisor" placeholder="Contrasena" required>
                                            <button class="boton alerta" type="submit">Confirmar eliminacion</button>
                                        </form>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No hay usuarios con esos criterios de consulta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top: 12px;">{{ $usuarios->links() }}</div>
        </section>
    </div>
@endsection
