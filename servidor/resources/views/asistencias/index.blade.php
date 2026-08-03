@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Asistencias</h2>

        <form method="GET" action="{{ route('asistencias.index') }}" class="grilla">
            <div>
                <label>Fecha</label>
                <input type="date" name="fecha" value="{{ $filtros['fecha'] ?? '' }}">
            </div>
            <div>
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    @foreach(['ASISTENCIA','RETARDO','FALTA','PERMISO','VACACIONES'] as $estado)
                        <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('asistencias.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('asistencias.create') }}">Nueva asistencia</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Horas</th>
                    <th>Origen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistencias as $asistencia)
                    <tr>
                        <td>{{ $asistencia->empleado->num_empleado ?? '-' }} - {{ $asistencia->empleado->nombre ?? '-' }}</td>
                        <td>{{ optional($asistencia->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $asistencia->estado }}</td>
                        <td>{{ number_format((float) $asistencia->horas_trabajadas, 2) }}</td>
                        <td>{{ $asistencia->origen }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('asistencias.edit', $asistencia->id) }}">Editar</a>
                                <form method="POST" action="{{ route('asistencias.destroy', $asistencia->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hay asistencias registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $asistencias->links() }}</div>
    </div>
@endsection
