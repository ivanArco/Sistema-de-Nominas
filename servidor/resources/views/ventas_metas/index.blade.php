@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Metas de ventas</h2>

        <form method="GET" action="{{ route('ventas.metas.index') }}" class="grilla">
            <div>
                <label>Periodo</label>
                <input name="periodo" value="{{ $filtros['periodo'] ?? '' }}" placeholder="YYYY-MM">
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('ventas.metas.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('ventas.metas.create') }}">Nueva meta</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Empleado</th>
                    <th>Meta</th>
                    <th>Venta real</th>
                    <th>Avance</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($metas as $meta)
                    @php $avance = $avancePorMeta[$meta->id] ?? ['monto_real' => 0, 'porcentaje' => 0]; @endphp
                    <tr>
                        <td>{{ $meta->periodo }}</td>
                        <td>{{ $meta->empleado->num_empleado ?? '-' }} - {{ $meta->empleado->nombre ?? '-' }}</td>
                        <td>${{ number_format((float) $meta->monto_meta, 2) }}</td>
                        <td>${{ number_format((float) $avance['monto_real'], 2) }}</td>
                        <td>{{ number_format((float) $avance['porcentaje'], 2) }}%</td>
                        <td>{{ $meta->estatus }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('ventas.metas.edit', $meta->id) }}">Editar</a>
                                <form method="POST" action="{{ route('ventas.metas.destroy', $meta->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No hay metas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $metas->links() }}</div>
    </div>
@endsection
