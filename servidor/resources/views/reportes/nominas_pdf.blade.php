<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibos de Nomina</title>
    <style>
        @page {
            margin: 5mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #111;
        }

        .hoja {
            page-break-inside: avoid;
            page-break-after: always;
        }

        .hoja:last-child {
            page-break-after: auto;
        }

        .recibo {
            border: 1px solid #111;
            padding: 4px;
            box-sizing: border-box;
            position: relative;
            min-height: 126mm;
        }

        .linea-corte {
            border-top: 1px dashed #555;
            margin: 1.5mm 0;
            text-align: center;
            font-size: 7px;
            color: #555;
        }

        .linea-corte span {
            background: #fff;
            padding: 0 6px;
            position: relative;
            top: -6px;
        }

        .titulo-principal {
            text-align: center;
            font-weight: 700;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .subtitulo {
            text-align: center;
            margin-bottom: 4px;
            font-size: 7px;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla th,
        .tabla td {
            border: 1px solid #555;
            padding: 1px 2px;
            vertical-align: top;
        }

        .tabla th {
            background: #efefef;
            text-align: left;
            font-size: 7px;
        }

        .texto-derecha {
            text-align: right;
        }

        .firma {
            margin-top: 7mm;
            width: 64%;
            border-top: 1px solid #333;
            text-align: center;
            font-size: 8px;
            padding-top: 2px;
        }

        .nota {
            font-size: 7px;
            margin-top: 2px;
        }

        .totales {
            margin-top: 2px;
        }
    </style>
</head>
<body>
    @php
        $recibos = collect($nominas)->flatMap(function ($nomina) {
            return [
                ['nomina' => $nomina, 'copia' => 'COPIA EMPLEADO'],
                ['nomina' => $nomina, 'copia' => 'COPIA PATRON'],
            ];
        });
    @endphp

    @forelse($recibos->chunk(2) as $grupo)
        <div class="hoja">
            @foreach($grupo as $indice => $item)
                @php
                    $nomina = $item['nomina'];
                    $tipoCopia = $item['copia'];
                    $empleado = $nomina->empleado;
                    $periodo = $nomina->periodo;
                    $nombreEmpleado = trim(($empleado->nombre ?? '') . ' ' . ($empleado->ap_paterno ?? '') . ' ' . ($empleado->ap_materno ?? ''));
                    $periodoTexto = ($periodo->anio ?? '-') . ' / ' . ($periodo->numero_periodo ?? '-') . ' / ' . ($periodo->tipo_periodo ?? '-');
                    $fechaInicio = optional($periodo->fecha_inicio)->format('d/m/Y') ?? '-';
                    $fechaFin = optional($periodo->fecha_fin)->format('d/m/Y') ?? '-';
                    $fechaPago = optional($periodo->fecha_pago)->format('d/m/Y') ?? '-';

                    $percepciones = $nomina->detalles
                        ->filter(fn ($detalle) => (($detalle->concepto->tipo ?? '') === 'PERCEPCION') && ((float) $detalle->importe > 0))
                        ->values();

                    $deducciones = $nomina->detalles
                        ->filter(fn ($detalle) => (($detalle->concepto->tipo ?? '') === 'DEDUCCION') && ((float) $detalle->importe > 0))
                        ->values();

                    // Limitar filas mantiene una altura estable para asegurar 2 recibos por hoja.
                    $filasDetalle = min(6, max(4, $percepciones->count(), $deducciones->count()));
                @endphp

                <section class="recibo">
                    <div class="titulo-principal">{{ $empresaNombre }}</div>
                    <div class="subtitulo">Recibo de nomina | {{ $tipoCopia }} | Generado: {{ $fechaGeneracion }}</div>

                    <table class="tabla">
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
                            <td colspan="3">{{ $nombreEmpleado !== '' ? $nombreEmpleado : '-' }}</td>
                            <th>NSS</th>
                            <td>{{ $empleado->nss ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Puesto</th>
                            <td>{{ $empleado->puesto->nombre ?? '-' }}</td>
                            <th>Departamento</th>
                            <td>{{ $empleado->departamento->nombre ?? '-' }}</td>
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

                    <table class="tabla" style="margin-top:4px;">
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
                                    $per = $percepciones->get($i);
                                    $ded = $deducciones->get($i);
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

                    <table class="tabla totales">
                        <tr>
                            <th style="width:20%;">Dias pagados</th>
                            <td style="width:13%;" class="texto-derecha">{{ number_format((float) $nomina->dias_pagados, 2) }}</td>
                            <th style="width:20%;">Total percepciones</th>
                            <td style="width:15%;" class="texto-derecha">${{ number_format((float) $nomina->total_percepciones, 2) }}</td>
                            <th style="width:17%;">Total deducciones</th>
                            <td style="width:15%;" class="texto-derecha">${{ number_format((float) $nomina->total_deducciones, 2) }}</td>
                        </tr>
                        <tr>
                            <th colspan="5">Neto a pagar</th>
                            <td class="texto-derecha"><strong>${{ number_format((float) $nomina->neto_pagado, 2) }}</strong></td>
                        </tr>
                    </table>

                    <div class="nota">Recibi de conformidad el importe neto indicado en este recibo de nomina.</div>
                    <div class="firma">Firma del empleado</div>
                </section>

                @if($indice === 0 && $grupo->count() > 1)
                    <div class="linea-corte"><span>corte</span></div>
                @endif
            @endforeach
        </div>
    @empty
        <p>No hay nominas para los filtros seleccionados.</p>
    @endforelse
</body>
</html>
