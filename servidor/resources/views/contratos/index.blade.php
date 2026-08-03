@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Contratos</h2>

        <form method="GET" action="{{ route('contratos.index') }}" class="grilla">
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    @foreach(['ACTIVO','VENCIDO','CANCELADO'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filtros['estatus'] ?? '') === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('contratos.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('contratos.create') }}">Nuevo contrato</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Sueldo</th>
                    <th>Jornada</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contratos as $contrato)
                    <tr>
                        <td>{{ $contrato->empleado->num_empleado ?? '-' }} - {{ $contrato->empleado->nombre ?? '-' }}</td>
                        <td>{{ $contrato->tipo }}</td>
                        <td>{{ optional($contrato->fecha_inicio)->format('d/m/Y') }}</td>
                        <td>{{ optional($contrato->fecha_fin)->format('d/m/Y') ?? '-' }}</td>
                        <td>${{ number_format((float) $contrato->sueldo_mensual, 2) }}</td>
                        <td>{{ $contrato->jornada }}</td>
                        <td>{{ $contrato->estatus }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('contratos.edit', $contrato->id) }}">Editar</a>
                                <form method="POST" action="{{ route('contratos.destroy', $contrato->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No hay contratos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $contratos->links() }}</div>
    </div>
@endsection
