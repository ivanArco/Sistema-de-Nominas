@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nominas</h2>

        <form method="GET" action="{{ route('nominas.index') }}" class="grilla">
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
            <div>
                <label>Estatus</label>
                <select name="estatus">
                    <option value="">Todos</option>
                    @foreach(['BORRADOR', 'CALCULADA', 'PAGADA', 'CANCELADA'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filtros['estatus'] ?? '') === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('nominas.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('nominas.create') }}">Calcular nomina</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Periodo</th>
                    <th>Dias</th>
                    <th>Percepciones</th>
                    <th>Deducciones</th>
                    <th>Neto</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($nominas as $nomina)
                    <tr>
                        <td>{{ $nomina->empleado->num_empleado ?? '-' }}</td>
                        <td>{{ $nomina->periodo->anio ?? '' }}-{{ $nomina->periodo->numero_periodo ?? '' }}</td>
                        <td>{{ number_format((float) $nomina->dias_pagados, 2) }}</td>
                        <td>${{ number_format((float) $nomina->total_percepciones, 2) }}</td>
                        <td>${{ number_format((float) $nomina->total_deducciones, 2) }}</td>
                        <td>${{ number_format((float) $nomina->neto_pagado, 2) }}</td>
                        <td>{{ $nomina->estatus }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('nominas.edit', $nomina->id) }}">Ver</a>
                                <form method="POST" action="{{ route('nominas.destroy', $nomina->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No hay nominas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $nominas->links() }}
        </div>
    </div>
@endsection
