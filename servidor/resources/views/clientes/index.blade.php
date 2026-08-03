@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Clientes</h2>

        <form method="GET" action="{{ route('clientes.index') }}" class="grilla">
            <div>
                <label>Busqueda general</label>
                <input name="texto" value="{{ $filtros['texto'] ?? '' }}" placeholder="Nombre, RFC, contacto, correo">
            </div>
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    <option value="ACTIVO" @selected(($filtros['estatus'] ?? '') === 'ACTIVO')>ACTIVO</option>
                    <option value="INACTIVO" @selected(($filtros['estatus'] ?? '') === 'INACTIVO')>INACTIVO</option>
                </select>
            </div>
            <div>
                <label>Ciudad</label>
                <input name="ciudad" value="{{ $filtros['ciudad'] ?? '' }}">
            </div>
            <div>
                <label>Estado</label>
                <input name="estado" value="{{ $filtros['estado'] ?? '' }}">
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('clientes.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('clientes.create') }}">Nuevo cliente</a>
                <a class="boton secundario" href="{{ route('clientes.reporte.pdf', request()->query()) }}">Exportar PDF</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Nombre comercial</th>
                    <th>Contacto</th>
                    <th>Correo</th>
                    <th>Telefono</th>
                    <th>Ciudad</th>
                    <th>Estado</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->nombre_comercial }}</td>
                        <td>{{ $cliente->nombre_contacto }}</td>
                        <td>{{ $cliente->correo_electronico }}</td>
                        <td>{{ $cliente->telefono_contacto_1 }}</td>
                        <td>{{ $cliente->ciudad }}</td>
                        <td>{{ $cliente->estado }}</td>
                        <td>{{ $cliente->estatus }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('clientes.edit', $cliente->id) }}">Editar</a>
                            </div>
                            <form method="POST" action="{{ route('clientes.destroy', $cliente->id) }}" class="grilla" style="margin-top: 8px; grid-template-columns: repeat(2, minmax(120px, 1fr));">
                                @csrf
                                @method('DELETE')
                                <input name="usuario_supervisor" placeholder="Supervisor" required>
                                <input type="password" name="contrasena_supervisor" placeholder="Contrasena" required>
                                <button class="boton alerta" style="grid-column: 1 / -1;" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay clientes con esos criterios de consulta.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $clientes->links() }}
        </div>
    </div>
@endsection
