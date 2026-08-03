@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Empleados</h2>

        @if(!empty($areaSupervisor))
            <div class="mensaje exito" style="margin-top:10px;">
                Vista filtrada por tu area: {{ $areaSupervisor }}
            </div>
        @endif

        <form method="GET" action="{{ route('empleados.index') }}" class="grilla">
            <div>
                <label>Busqueda</label>
                <input name="texto" value="{{ $filtros['texto'] ?? '' }}" placeholder="Numero, nombre, CURP, RFC, NSS">
            </div>
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    <option value="ACTIVO" @selected(($filtros['estatus'] ?? '') === 'ACTIVO')>ACTIVO</option>
                    <option value="BAJA" @selected(($filtros['estatus'] ?? '') === 'BAJA')>BAJA</option>
                </select>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('empleados.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('empleados.create') }}">Nuevo empleado</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Tipo de pago</th>
                    <th>Salario diario</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empleados as $empleado)
                    <tr>
                        <td>{{ $empleado->num_empleado }}</td>
                        <td>{{ $empleado->nombre }} {{ $empleado->ap_paterno }} {{ $empleado->ap_materno }}</td>
                        <td>{{ $empleado->departamento->nombre ?? '-' }}</td>
                        <td>{{ $empleado->puesto->nombre ?? '-' }}</td>
                        <td>{{ $empleado->tipo_pago ?? '-' }}</td>
                        <td>${{ number_format((float) $empleado->sal_dia, 2) }}</td>
                        <td>{{ $empleado->estatus }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('empleados.edit', $empleado->id) }}">Editar</a>
                                <form method="POST" action="{{ route('empleados.destroy', $empleado->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay empleados con esos criterios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $empleados->links() }}
        </div>
    </div>
@endsection
