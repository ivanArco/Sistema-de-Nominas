@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Incidencias</h2>

        <form method="GET" action="{{ route('incidencias.index') }}" class="grilla">
            <div>
                <label>Periodo</label>
                <select name="periodo_nomina_id">
                    <option value="">Todos</option>
                    @foreach($periodos as $periodo)
                        @php($descripcion = $periodo->anio . ' / ' . $periodo->numero_periodo . ' / ' . $periodo->tipo_periodo)
                        <option value="{{ $periodo->id }}" @selected((string) ($filtros['periodo_nomina_id'] ?? '') === (string) $periodo->id)>{{ $descripcion }}</option>
                    @endforeach
                </select>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('incidencias.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('incidencias.create') }}">Nueva incidencia</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Periodo</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Monto</th>
                    <th>Descripcion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidencias as $incidencia)
                    <tr>
                        <td>{{ $incidencia->empleado->num_empleado ?? '-' }}</td>
                        <td>{{ $incidencia->periodo->anio ?? '' }}-{{ $incidencia->periodo->numero_periodo ?? '' }}</td>
                        <td>{{ $incidencia->tipo }}</td>
                        <td>{{ number_format((float) $incidencia->cantidad, 2) }}</td>
                        <td>${{ number_format((float) $incidencia->monto, 2) }}</td>
                        <td>{{ $incidencia->descripcion }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('incidencias.edit', $incidencia->id) }}">Editar</a>
                                <form method="POST" action="{{ route('incidencias.destroy', $incidencia->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay incidencias registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $incidencias->links() }}
        </div>
    </div>
@endsection
