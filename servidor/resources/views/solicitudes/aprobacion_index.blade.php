@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Aprobacion de solicitudes</h2>

        <form method="GET" action="{{ route('solicitudes.aprobacion.index') }}" class="grilla">
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    <option value="PENDIENTE" @selected(($filtros['estatus'] ?? '') === 'PENDIENTE')>PENDIENTE</option>
                    <option value="APROBADA" @selected(($filtros['estatus'] ?? '') === 'APROBADA')>APROBADA</option>
                    <option value="RECHAZADA" @selected(($filtros['estatus'] ?? '') === 'RECHAZADA')>RECHAZADA</option>
                </select>
            </div>
            <div>
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="VACACIONES" @selected(($filtros['tipo'] ?? '') === 'VACACIONES')>VACACIONES</option>
                    <option value="PERMISO" @selected(($filtros['tipo'] ?? '') === 'PERMISO')>PERMISO</option>
                </select>
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('solicitudes.aprobacion.index') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Departamento</th>
                    <th>Tipo</th>
                    <th>Fechas</th>
                    <th>Etapa Supervisor</th>
                    <th>Etapa Jefe</th>
                    <th>Estatus</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitudes as $solicitud)
                    <tr>
                        <td>{{ $solicitud->empleado->num_empleado ?? '-' }} - {{ $solicitud->empleado->nombre ?? '-' }} {{ $solicitud->empleado->ap_paterno ?? '' }}</td>
                        <td>{{ $solicitud->empleado->departamento->nombre ?? '-' }}</td>
                        <td>{{ $solicitud->tipo }}</td>
                        <td>{{ optional($solicitud->fecha_inicio)->format('d/m/Y') }} al {{ optional($solicitud->fecha_fin)->format('d/m/Y') }}</td>
                        <td>{{ $solicitud->etapa_supervisor_estatus }}</td>
                        <td>{{ $solicitud->etapa_jefe_estatus }}</td>
                        <td>{{ $solicitud->estatus }}</td>
                        <td>
                            @if($solicitud->estatus === 'PENDIENTE')
                                <div class="acciones">
                                    <form method="POST" action="{{ route('solicitudes.aprobacion.update', $solicitud->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estatus" value="APROBADA">
                                        <button class="boton" type="submit">Aprobar</button>
                                    </form>
                                    <form method="POST" action="{{ route('solicitudes.aprobacion.update', $solicitud->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estatus" value="RECHAZADA">
                                        <button class="boton alerta" type="submit">Rechazar</button>
                                    </form>
                                </div>
                            @else
                                {{ $solicitud->revisor->nombre_usuario ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No hay solicitudes para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $solicitudes->links() }}</div>
    </div>
@endsection
