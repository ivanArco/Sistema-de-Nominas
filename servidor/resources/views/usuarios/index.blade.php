@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Usuarios</h2>

        <form method="GET" action="{{ route('usuarios.index') }}" class="grilla">
            <div>
                <label>Busqueda general</label>
                <input name="texto" value="{{ $filtros['texto'] ?? '' }}" placeholder="Nombre, usuario, correo, CURP, NSS">
            </div>
            <div>
                <label>Rol</label>
                <select name="rol">
                    <option value="">Todos</option>
                    @foreach(\App\Models\User::rolesDisponibles() as $rol)
                        <option value="{{ $rol }}" @selected(($filtros['rol'] ?? '') === $rol)>{{ $rol }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Activo</label>
                <select name="activo">
                    <option value="">Todos</option>
                    <option value="1" @selected(($filtros['activo'] ?? '') === '1')>Si</option>
                    <option value="0" @selected(($filtros['activo'] ?? '') === '0')>No</option>
                </select>
            </div>
            <div>
                <label>Estado</label>
                <input name="estado" value="{{ $filtros['estado'] ?? '' }}">
            </div>
            <div>
                <label>Fecha contratacion desde</label>
                <input type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}">
            </div>
            <div>
                <label>Fecha contratacion hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}">
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('usuarios.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('usuarios.create') }}">Nuevo usuario</a>
                <a class="boton secundario" href="{{ route('usuarios.reporte.pdf', request()->query()) }}">Exportar PDF</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre completo</th>
                    <th>Correo</th>
                    <th>Telefono</th>
                    <th>Area</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->nombre_usuario }}</td>
                        <td>{{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->telefono_contacto_1 }}</td>
                        <td>{{ $usuario->area_contratacion }}</td>
                        <td>{{ $usuario->rol }}</td>
                        <td>{{ $usuario->estado }}</td>
                        <td>{{ $usuario->activo ? 'Si' : 'No' }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('usuarios.edit', $usuario->id) }}">Editar</a>
                            </div>
                            <form method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}" class="grilla" style="margin-top: 8px; grid-template-columns: repeat(2, minmax(120px, 1fr));">
                                @csrf
                                @method('DELETE')
                                <input name="usuario_supervisor" placeholder="Supervisor o jefe de area" required>
                                <input type="password" name="contrasena_supervisor" placeholder="Contrasena" required>
                                <button class="boton alerta" style="grid-column: 1 / -1;" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">No hay usuarios con esos criterios de consulta.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $usuarios->links() }}
        </div>
    </div>
@endsection
