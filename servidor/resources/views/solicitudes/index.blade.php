@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Mis solicitudes</h2>

        @if(!empty($avisoSinEmpleado))
            <div class="mensaje error" style="margin-top:10px;">{{ $avisoSinEmpleado }}</div>
        @endif

        <form method="GET" action="{{ route('solicitudes.index') }}" class="grilla">
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    <option value="PENDIENTE" @selected(($filtros['estatus'] ?? '') === 'PENDIENTE')>PENDIENTE</option>
                    <option value="APROBADA" @selected(($filtros['estatus'] ?? '') === 'APROBADA')>APROBADA</option>
                    <option value="RECHAZADA" @selected(($filtros['estatus'] ?? '') === 'RECHAZADA')>RECHAZADA</option>
                </select>
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('solicitudes.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('solicitudes.create') }}">Nueva solicitud</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estatus</th>
                    <th>Motivo</th>
                    <th>Revision</th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitudes as $solicitud)
                    <tr>
                        <td>{{ $solicitud->tipo }}</td>
                        <td>{{ optional($solicitud->fecha_inicio)->format('d/m/Y') }}</td>
                        <td>{{ optional($solicitud->fecha_fin)->format('d/m/Y') }}</td>
                        <td>{{ $solicitud->estatus }}</td>
                        <td>{{ $solicitud->motivo ?: '-' }}</td>
                        <td>{{ $solicitud->comentario_revision ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Sin solicitudes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $solicitudes->links() }}</div>
    </div>
@endsection
