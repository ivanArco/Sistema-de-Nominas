@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0;">Catalogos de nomina</h2>
                <p style="margin:6px 0 0;color:#677487;">Gestion integrada para periodos de pago y conceptos de percepcion/deduccion.</p>
            </div>
            <div class="acciones" style="margin-top:0;">
                <a class="boton" href="{{ route('periodos-nomina.create') }}">Nuevo periodo</a>
                <a class="boton secundario" href="{{ route('conceptos-nomina.create') }}">Nuevo concepto</a>
            </div>
        </div>
    </div>

    <div class="grilla" style="grid-template-columns: 1fr 1fr; align-items:start;">
        <div class="tarjeta" style="overflow-x:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                <h3 style="margin:0;">Periodos de nomina</h3>
                <a class="boton secundario" href="{{ route('periodos-nomina.index') }}">Ver modulo</a>
            </div>

            <table class="tabla" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th>Anio</th>
                        <th>No.</th>
                        <th>Tipo</th>
                        <th>Pago</th>
                        <th>Estatus</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodos as $periodo)
                        <tr>
                            <td>{{ $periodo->anio }}</td>
                            <td>{{ $periodo->numero_periodo }}</td>
                            <td>{{ $periodo->tipo_periodo }}</td>
                            <td>{{ $periodo->fecha_pago?->format('d/m/Y') }}</td>
                            <td>{{ $periodo->estatus }}</td>
                            <td>
                                <a class="boton secundario" href="{{ route('periodos-nomina.edit', $periodo->id) }}">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No hay periodos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:10px;">
                {{ $periodos->appends(['conceptos_page' => request('conceptos_page')])->links() }}
            </div>
        </div>

        <div class="tarjeta" style="overflow-x:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                <h3 style="margin:0;">Conceptos de nomina</h3>
                <a class="boton secundario" href="{{ route('conceptos-nomina.index') }}">Ver modulo</a>
            </div>

            <table class="tabla" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Gravado</th>
                        <th>Activo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conceptos as $concepto)
                        <tr>
                            <td>{{ $concepto->clave }}</td>
                            <td>{{ $concepto->nombre }}</td>
                            <td>{{ $concepto->tipo }}</td>
                            <td>{{ $concepto->gravado ? 'Si' : 'No' }}</td>
                            <td>{{ $concepto->activo ? 'Si' : 'No' }}</td>
                            <td>
                                <a class="boton secundario" href="{{ route('conceptos-nomina.edit', $concepto->id) }}">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No hay conceptos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:10px;">
                {{ $conceptos->appends(['periodos_page' => request('periodos_page')])->links() }}
            </div>
        </div>
    </div>
@endsection
