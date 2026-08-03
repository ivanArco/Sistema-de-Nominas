@extends('layouts.app')

@section('contenido')
<div class="tarjeta">
    <h2>Permisos</h2>
    <form method="GET" class="grilla">
        <div>
            <label>Busqueda</label>
            <input type="text" name="texto" value="{{ $filtros['texto'] ?? '' }}" placeholder="Clave o nombre">
        </div>
        <div class="acciones" style="grid-column: 1 / -1;">
            <button class="boton" type="submit">Buscar</button>
            <a class="boton secundario" href="{{ route('permisos.index') }}">Limpiar</a>
            <a href="{{ route('permisos.create') }}" class="boton">Nuevo permiso</a>
        </div>
    </form>
</div>

<div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Nombre</th>
                    <th>Roles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permisos as $permiso)
                    <tr>
                        <td><strong>{{ $permiso->clave }}</strong></td>
                        <td>{{ $permiso->nombre }}</td>
                        <td>{{ $permiso->roles_count }}</td>
                        <td>
                            <div class="acciones">
                                <a href="{{ route('permisos.edit', $permiso) }}" class="boton secundario">Editar</a>
                            </div>
                            <form action="{{ route('permisos.destroy', $permiso) }}" method="POST" style="margin-top: 8px;" onsubmit="return confirm('Eliminar este permiso?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="boton alerta">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay permisos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
</div>

<div style="margin-top: 12px;">
    {{ $permisos->links() }}
</div>
@endsection
