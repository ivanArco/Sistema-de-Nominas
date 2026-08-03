@extends('layouts.app')

@section('contenido')
<div class="tarjeta">
    <h2>Roles</h2>
    <form method="GET" class="grilla">
        <div>
            <label>Busqueda</label>
            <input type="text" name="texto" value="{{ $filtros['texto'] ?? '' }}" placeholder="Clave o nombre">
        </div>
        <div class="acciones" style="grid-column: 1 / -1;">
            <button class="boton" type="submit">Buscar</button>
            <a class="boton secundario" href="{{ route('roles.index') }}">Limpiar</a>
            <a href="{{ route('roles.create') }}" class="boton">Nuevo rol</a>
        </div>
    </form>
</div>

<div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Nombre</th>
                    <th>Usuarios</th>
                    <th>Permisos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $rol)
                    <tr>
                        <td><strong>{{ $rol->clave }}</strong></td>
                        <td>{{ $rol->nombre }}</td>
                        <td>{{ $rol->usuarios_count }}</td>
                        <td>{{ $rol->permisos->count() }}</td>
                        <td>
                            <div class="acciones">
                                <a href="{{ route('roles.edit', $rol) }}" class="boton secundario">Editar</a>
                            </div>
                            <form action="{{ route('roles.destroy', $rol) }}" method="POST" style="margin-top: 8px;" onsubmit="return confirm('Eliminar este rol?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="boton alerta">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No hay roles registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
</div>

<div style="margin-top: 12px;">
    {{ $roles->links() }}
</div>
@endsection
