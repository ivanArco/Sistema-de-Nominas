@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Reporte de Empleados</h2>
        <form method="GET" action="{{ route('reportes.empleados') }}" class="grilla">
            <div>
                <label>Busqueda</label>
                <input name="texto" value="{{ $filtros['texto'] ?? '' }}" placeholder="ID, nombre, CURP o RFC">
            </div>
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    @foreach(['ACTIVO', 'BAJA'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filtros['estatus'] ?? '') === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>
            <div class="acciones" style="grid-column:1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('reportes.empleados') }}">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Puesto</th>
                    <th>Semanas cotizadas</th>
                    <th>Fondo retiro</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empleados as $empleado)
                    <tr>
                        <td>{{ $empleado->num_empleado }}</td>
                        <td>{{ trim($empleado->nombre . ' ' . $empleado->ap_paterno . ' ' . ($empleado->ap_materno ?? '')) }}</td>
                        <td>{{ $empleado->puesto->nombre ?? '-' }}</td>
                        <td>{{ number_format((float) ($empleado->semanas_cotizadas ?? 0), 2) }}</td>
                        <td>${{ number_format((float) ($empleado->fondo_retiro_acumulado ?? 0), 2) }}</td>
                        <td>{{ $empleado->estatus }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Sin empleados para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $empleados->links() }}</div>
    </div>
@endsection
