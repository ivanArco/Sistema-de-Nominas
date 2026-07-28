@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Reporte de Incidencias</h2>
        <form method="GET" action="{{ route('reportes.incidencias') }}" class="grilla">
            <div>
                <label>Tipo incidencia</label>
                <select name="tipo">
                    <option value="">Todas</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Tipo periodo</label>
                <select name="tipo_periodo">
                    <option value="">Todos</option>
                    @foreach(['SEMANAL', 'QUINCENAL', 'MENSUAL'] as $tipoPeriodo)
                        <option value="{{ $tipoPeriodo }}" @selected(($filtros['tipo_periodo'] ?? '') === $tipoPeriodo)>{{ $tipoPeriodo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="acciones" style="grid-column:1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('reportes.incidencias') }}">Limpiar</a>
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
                </tr>
            </thead>
            <tbody>
                @forelse($incidencias as $incidencia)
                    <tr>
                        <td>{{ ($incidencia->empleado->num_empleado ?? '-') . ' - ' . ($incidencia->empleado->nombre ?? '-') }}</td>
                        <td>{{ ($incidencia->periodo->anio ?? '-') . '/' . ($incidencia->periodo->numero_periodo ?? '-') }}</td>
                        <td>{{ $incidencia->tipo }}</td>
                        <td>{{ number_format((float) $incidencia->cantidad, 2) }}</td>
                        <td>${{ number_format((float) $incidencia->monto, 2) }}</td>
                        <td>{{ $incidencia->descripcion ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Sin incidencias para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $incidencias->links() }}</div>
    </div>
@endsection
