@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Periodos de nomina</h2>

        <form method="GET" action="{{ route('periodos-nomina.index') }}" class="grilla">
            <div>
                <label>Anio</label>
                <input type="number" name="anio" min="2020" max="2100" value="{{ $filtros['anio'] ?? '' }}" placeholder="Todos">
            </div>
            <div>
                <label>Mes</label>
                <select name="mes">
                    <option value="">Todos</option>
                    @for($mes = 1; $mes <= 12; $mes++)
                        <option value="{{ $mes }}" @selected((int) ($filtros['mes'] ?? 0) === $mes)>{{ str_pad((string) $mes, 2, '0', STR_PAD_LEFT) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label>Tipo</label>
                <select name="tipo_periodo">
                    <option value="">Todos</option>
                    @foreach(['SEMANAL', 'QUINCENAL', 'MENSUAL'] as $tipo)
                        <option value="{{ $tipo }}" @selected(($filtros['tipo_periodo'] ?? '') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    @foreach(['ABIERTO', 'CALCULADO', 'CERRADO', 'TIMBRADO'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filtros['estatus'] ?? '') === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('periodos-nomina.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('periodos-nomina.create') }}">Nuevo periodo</a>
                <a class="boton secundario" href="{{ route('catalogos-nomina.index') }}">Ir a catalogos</a>
            </div>
        </form>

        <form method="POST" action="{{ route('periodos-nomina.generar-automaticos') }}" class="acciones" style="margin-top:10px;">
            @csrf
            <input type="hidden" name="anio" value="{{ $filtros['anio'] ?? now()->year }}">
            <input type="hidden" name="mes" value="{{ $filtros['mes'] ?? now()->month }}">
            <button class="boton" type="submit">Generar periodos automaticos del mes (Semanal/Quincenal/Mensual)</button>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Anio</th>
                    <th>No. periodo</th>
                    <th>Tipo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Pago</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $bloqueMes = null;
                @endphp
                @forelse($periodos as $periodo)
                    @php
                        $mesFila = optional($periodo->fecha_inicio)->format('m/Y') ?? '-';
                    @endphp
                    @if($bloqueMes !== $mesFila)
                        @php
                            $bloqueMes = $mesFila;
                        @endphp
                        <tr>
                            <td colspan="9" style="background:#eef4fb; font-weight:700;">Mes: {{ $bloqueMes }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>{{ $mesFila }}</td>
                        <td>{{ $periodo->anio }}</td>
                        <td>{{ $periodo->numero_periodo }}</td>
                        <td>{{ $periodo->tipo_periodo }}</td>
                        <td>{{ $periodo->fecha_inicio?->format('d/m/Y') }}</td>
                        <td>{{ $periodo->fecha_fin?->format('d/m/Y') }}</td>
                        <td>{{ $periodo->fecha_pago?->format('d/m/Y') }}</td>
                        <td>{{ $periodo->estatus }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('periodos-nomina.edit', $periodo->id) }}">Editar</a>
                                <form method="POST" action="{{ route('periodos-nomina.destroy', $periodo->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">No hay periodos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $periodos->links() }}
        </div>
    </div>
@endsection
