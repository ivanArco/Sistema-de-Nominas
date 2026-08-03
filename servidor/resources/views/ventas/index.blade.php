@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Ventas y comisiones</h2>

        <form method="GET" action="{{ route('ventas.index') }}" class="grilla">
            <div>
                <label>Empleado</label>
                <input name="empleado" value="{{ $filtros['empleado'] ?? '' }}" placeholder="Numero o nombre">
            </div>
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    @foreach(['REGISTRADA','CERRADA','CANCELADA'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filtros['estatus'] ?? '') === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('ventas.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('ventas.create') }}">Nueva venta</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Empleado</th>
                    <th>Monto</th>
                    <th>%</th>
                    <th>Comision</th>
                    <th>Bono</th>
                    <th>Estatus bono</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalComisiones = 0;
                    $totalBonos = 0;
                @endphp
                @forelse($ventas as $venta)
                    @php
                        $totalComisiones += (float) $venta->comision_calculada;
                        $totalBonos += (float) $venta->bono_desempeno;
                    @endphp
                    <tr>
                        <td>{{ $venta->folio }}</td>
                        <td>{{ optional($venta->fecha_venta)->format('d/m/Y') }}</td>
                        <td>{{ $venta->empleado->num_empleado ?? '-' }} - {{ $venta->empleado->nombre ?? '-' }}</td>
                        <td>${{ number_format((float) $venta->monto_bruto, 2) }}</td>
                        <td>{{ number_format((float) $venta->porcentaje_comision, 2) }}%</td>
                        <td>${{ number_format((float) $venta->comision_calculada, 2) }}</td>
                        <td>${{ number_format((float) $venta->bono_desempeno, 2) }}</td>
                        <td>{{ $venta->bono_estatus ?? 'PENDIENTE' }}</td>
                        <td>{{ $venta->estatus }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('ventas.edit', $venta->id) }}">Editar</a>
                                @if(auth()->user()?->tienePermiso('bonos.autorizar') && (float) $venta->bono_desempeno > 0 && ($venta->bono_estatus ?? 'PENDIENTE') === 'PENDIENTE')
                                    <form method="POST" action="{{ route('ventas.autorizar-bono', $venta->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="bono_estatus" value="APROBADO">
                                        <button class="boton" type="submit">Aprobar bono</button>
                                    </form>
                                    <form method="POST" action="{{ route('ventas.autorizar-bono', $venta->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="bono_estatus" value="RECHAZADO">
                                        <button class="boton alerta" type="submit">Rechazar bono</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('ventas.destroy', $venta->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">No hay ventas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="grilla" style="margin-top: 12px;">
            <div><strong>Total comisiones:</strong> ${{ number_format($totalComisiones, 2) }}</div>
            <div><strong>Total bonos:</strong> ${{ number_format($totalBonos, 2) }}</div>
        </div>

        <div style="margin-top:12px;">{{ $ventas->links() }}</div>
    </div>
@endsection
