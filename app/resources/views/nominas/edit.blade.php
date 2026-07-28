@extends('layouts.app')

@section('contenido')
    @php
        $empleado = $nomina->empleado;
        $diasPagados = (float) $nomina->dias_pagados;
        $sueldoBruto = (float) $nomina->total_percepciones;
        $totalDeducciones = (float) $nomina->total_deducciones;
        $netoPagar = (float) $nomina->neto_pagado;
        $pagoDiario = $diasPagados > 0 ? $sueldoBruto / $diasPagados : 0;
        $diasFalta = (float) ($dias_falta ?? 0);
        $diasTrabajados = (float) ($dias_trabajados ?? $diasPagados);
        $descuentosPorIncidencias = (float) ($descuentos_por_incidencias ?? 0);
        $totalDescuentoFaltas = (float) ($total_descuento_faltas ?? 0);
        $nombreCompleto = trim(collect([
            $empleado->nombre ?? null,
            $empleado->ap_paterno ?? null,
            $empleado->ap_materno ?? null,
        ])->filter()->implode(' '));
        $fechaIngreso = $empleado?->f_ingreso ? $empleado->f_ingreso->format('d/m/Y') : '-';
        $puesto = $empleado?->puesto?->nombre ?? '-';
        $detallesPercepcion = $nomina->detalles->filter(function ($detalle) {
            return ($detalle->concepto->tipo ?? '') === 'PERCEPCION';
        });
        $detallesDeduccion = $nomina->detalles->filter(function ($detalle) {
            return ($detalle->concepto->tipo ?? '') === 'DEDUCCION';
        });
        $periodo = $nomina->periodo;
        $periodoTexto = ($periodo->anio ?? '-') . ' / ' . ($periodo->numero_periodo ?? '-') . ' / ' . ($periodo->tipo_periodo ?? '-');
        $fechaInicio = optional($periodo->fecha_inicio)->format('d/m/Y') ?? '-';
        $fechaFin = optional($periodo->fecha_fin)->format('d/m/Y') ?? '-';
        $fechaPago = optional($periodo->fecha_pago)->format('d/m/Y') ?? '-';
        $filasDetalle = max(8, $detallesPercepcion->count(), $detallesDeduccion->count());
    @endphp

    <style>
        .recibo-vista {
            border: 1px solid #0f172a;
            border-radius: 12px;
            padding: 12px;
            background: #ffffff;
        }

        .recibo-vista .titulo-principal {
            text-align: center;
            font-weight: 800;
            font-size: 1.12rem;
            margin-bottom: 2px;
        }

        .recibo-vista .subtitulo {
            text-align: center;
            margin-bottom: 10px;
            color: #475569;
            font-size: 0.92rem;
        }

        .recibo-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
        }

        .recibo-tabla th,
        .recibo-tabla td {
            border: 1px solid #475569;
            padding: 6px 8px;
            vertical-align: top;
        }

        .recibo-tabla th {
            background: #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }

        .texto-derecha {
            text-align: right;
        }

        .firma-panel {
            margin-top: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px;
            background: #f8fafc;
        }

        .firma-linea {
            margin-top: 34px;
            width: 55%;
            border-top: 1px solid #0f172a;
            text-align: center;
            font-size: 0.9rem;
            padding-top: 4px;
        }
    </style>

    <div class="tarjeta">
        <h2>Detalle de nomina</h2>

        <section class="recibo-vista" style="margin-top:10px;">
            <div class="titulo-principal">Recibo de nomina</div>
            <div class="subtitulo">Vista homologada al formato de reporte</div>

            <table class="recibo-tabla">
                <tr>
                    <th style="width:14%;">No. empleado</th>
                    <td style="width:18%;">{{ $empleado->num_empleado ?? '-' }}</td>
                    <th style="width:10%;">RFC</th>
                    <td style="width:18%;">{{ $empleado->rfc ?? '-' }}</td>
                    <th style="width:10%;">CURP</th>
                    <td style="width:30%;">{{ $empleado->curp ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Empleado</th>
                    <td colspan="3">{{ $nombreCompleto !== '' ? $nombreCompleto : '-' }}</td>
                    <th>NSS</th>
                    <td>{{ $empleado->nss ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Puesto</th>
                    <td>{{ $puesto }}</td>
                    <th>Fecha ingreso</th>
                    <td>{{ $fechaIngreso }}</td>
                    <th>Salario diario</th>
                    <td class="texto-derecha">${{ number_format((float) ($empleado->sal_dia ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <th>Periodo</th>
                    <td>{{ $periodoTexto }}</td>
                    <th>Del</th>
                    <td>{{ $fechaInicio }} al {{ $fechaFin }}</td>
                    <th>Fecha pago</th>
                    <td>{{ $fechaPago }}</td>
                </tr>
            </table>

            <table class="recibo-tabla" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th style="width:9%;">Clave</th>
                        <th style="width:27%;">Percepciones</th>
                        <th style="width:14%;" class="texto-derecha">Importe</th>
                        <th style="width:9%;">Clave</th>
                        <th style="width:27%;">Deducciones</th>
                        <th style="width:14%;" class="texto-derecha">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < $filasDetalle; $i++)
                        @php
                            $per = $detallesPercepcion->values()->get($i);
                            $ded = $detallesDeduccion->values()->get($i);
                        @endphp
                        <tr>
                            <td>{{ $per->concepto->clave ?? '' }}</td>
                            <td>{{ $per->concepto->nombre ?? '' }}</td>
                            <td class="texto-derecha">{{ $per ? '$' . number_format((float) $per->importe, 2) : '' }}</td>
                            <td>{{ $ded->concepto->clave ?? '' }}</td>
                            <td>{{ $ded->concepto->nombre ?? '' }}</td>
                            <td class="texto-derecha">{{ $ded ? '$' . number_format((float) $ded->importe, 2) : '' }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <table class="recibo-tabla" style="margin-top:10px;">
                <tr>
                    <th style="width:20%;">Dias pagados</th>
                    <td style="width:13%;" class="texto-derecha">{{ number_format($diasPagados, 2) }}</td>
                    <th style="width:20%;">Dias trabajados</th>
                    <td style="width:15%;" class="texto-derecha">{{ number_format($diasTrabajados, 2) }}</td>
                    <th style="width:17%;">Dias de falta</th>
                    <td style="width:15%;" class="texto-derecha">{{ number_format($diasFalta, 2) }}</td>
                </tr>
                <tr>
                    <th>Pago diario estimado</th>
                    <td class="texto-derecha">${{ number_format($pagoDiario, 2) }}</td>
                    <th>Total percepciones</th>
                    <td class="texto-derecha">${{ number_format($sueldoBruto, 2) }}</td>
                    <th>Total deducciones</th>
                    <td class="texto-derecha">${{ number_format($totalDeducciones, 2) }}</td>
                </tr>
                <tr>
                    <th>Descuento incidencias (D090)</th>
                    <td class="texto-derecha">${{ number_format($descuentosPorIncidencias, 2) }}</td>
                    <th>Descuento por faltas</th>
                    <td class="texto-derecha">${{ number_format($totalDescuentoFaltas, 2) }}</td>
                    <th>Neto a pagar</th>
                    <td class="texto-derecha"><strong>${{ number_format($netoPagar, 2) }}</strong></td>
                </tr>
            </table>

            <div class="firma-panel">
                <div>Recibi de conformidad el importe neto indicado en este recibo de nomina.</div>
                <div class="firma-linea">Firma del empleado</div>
            </div>
        </section>

        <form method="POST" action="{{ route('nominas.update', $nomina->id) }}" class="grilla" style="margin-top: 12px;">
            @csrf
            @method('PUT')
            <div>
                <label>Estatus</label>
                <select name="estatus" required>
                    @foreach(['BORRADOR', 'CALCULADA', 'PAGADA', 'CANCELADA'] as $estatus)
                        <option value="{{ $estatus }}" @selected($nomina->estatus === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>
            <div class="acciones" style="align-items:end;">
                <button class="boton" type="submit">Actualizar estatus</button>
                <a class="boton secundario" href="{{ route('nominas.index') }}">Regresar</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <h3 style="margin-top:0;">Apartado de faltas</h3>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Cantidad de dias</th>
                    <th>Monto descontado</th>
                    <th>Descripcion</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidencias_falta as $falta)
                    <tr>
                        <td>{{ number_format((float) $falta->cantidad, 2) }}</td>
                        <td>${{ number_format((float) $falta->monto, 2) }}</td>
                        <td>{{ $falta->descripcion ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No hay faltas registradas para este empleado en este periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
