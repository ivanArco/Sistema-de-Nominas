@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Evaluaciones de desempeno</h2>

        <form method="GET" action="{{ route('evaluaciones.index') }}" class="grilla">
            <div>
                <label>Periodo</label>
                <input name="periodo" value="{{ $filtros['periodo'] ?? '' }}" placeholder="YYYY-MM">
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('evaluaciones.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('evaluaciones.create') }}">Nueva evaluacion</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Puntaje</th>
                    <th>Resultado</th>
                    <th>Estatus</th>
                    <th>Evaluador</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluaciones as $evaluacion)
                    <tr>
                        <td>{{ $evaluacion->periodo }}</td>
                        <td>{{ $evaluacion->empleado->num_empleado ?? '-' }} - {{ $evaluacion->empleado->nombre ?? '-' }}</td>
                        <td>{{ optional($evaluacion->fecha_evaluacion)->format('d/m/Y') }}</td>
                        <td>{{ $evaluacion->puntaje }}</td>
                        <td>{{ $evaluacion->resultado }}</td>
                        <td>{{ $evaluacion->estatus }}</td>
                        <td>{{ $evaluacion->evaluador->nombre_usuario ?? '-' }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('evaluaciones.edit', $evaluacion->id) }}">Editar</a>
                                <form method="POST" action="{{ route('evaluaciones.destroy', $evaluacion->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No hay evaluaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $evaluaciones->links() }}</div>
    </div>
@endsection
