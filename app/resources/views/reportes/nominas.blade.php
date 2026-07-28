@extends('layouts.app')

@section('contenido')
    @php
        $isPrint = (bool) ($modoImpresion ?? false);
    @endphp

    <div class="tarjeta">
        <h2>Reporte de Nominas</h2>

        <form method="GET" action="{{ route('reportes.nominas') }}" class="grilla">
            <div>
                <label>Tipo de periodo</label>
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
                    @foreach(['BORRADOR', 'CALCULADA', 'PAGADA', 'CANCELADA'] as $estatus)
                        <option value="{{ $estatus }}" @selected(($filtros['estatus'] ?? '') === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Empleado (ID, nombre, CURP o RFC)</label>
                <input name="empleado" value="{{ $filtros['empleado'] ?? '' }}" placeholder="Buscar empleado">
            </div>

            <div class="acciones" style="grid-column:1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('reportes.nominas') }}">Limpiar</a>
                <a class="boton" href="{{ route('reportes.nominas.pdf', request()->query()) }}">Exportar PDF (2 copias por hoja)</a>
                <a class="boton" href="{{ route('reportes.nominas.csv', request()->query()) }}">Exportar CSV</a>
                <a class="boton secundario" href="{{ route('reportes.nominas', array_merge(request()->query(), ['imprimir' => 1])) }}" target="_blank">Imprimir</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <form method="GET" action="{{ route('reportes.nominas.pdf') }}" id="form_nominas_seleccionadas">
            <input type="hidden" name="tipo_periodo" value="{{ $filtros['tipo_periodo'] ?? '' }}">
            <input type="hidden" name="estatus" value="{{ $filtros['estatus'] ?? '' }}">
            <input type="hidden" name="empleado" value="{{ $filtros['empleado'] ?? '' }}">

            <div class="acciones" style="margin-bottom:10px; justify-content:space-between; align-items:center;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                    <input type="checkbox" id="seleccionar_todas_nominas">
                    Seleccionar todas las mostradas
                </label>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button class="boton secundario" type="submit" formaction="{{ route('reportes.nominas') }}" formtarget="_blank" name="imprimir" value="1">Imprimir seleccionadas</button>
                    <button class="boton" type="submit" formtarget="_blank">PDF seleccionadas</button>
                </div>
            </div>

            <table class="tabla">
                <thead>
                    <tr>
                        <th style="width:36px; text-align:center;">Sel.</th>
                        <th>Empleado</th>
                        <th>Periodo</th>
                        <th>Tipo</th>
                        <th>Clasificacion</th>
                        <th>Bruto</th>
                        <th>Credito salario</th>
                        <th>ISR</th>
                        <th>IMSS</th>
                        <th>Infonavit</th>
                        <th>Otras deducciones</th>
                        <th>Deducciones</th>
                        <th>Neto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nominas as $nomina)
                        @php
                            $sumarClave = static function ($detalles, string $clave): float {
                                return (float) $detalles
                                    ->filter(fn ($detalle) => ($detalle->concepto->clave ?? null) === $clave)
                                    ->sum(fn ($detalle) => (float) $detalle->importe);
                            };
                            $isr = $sumarClave($nomina->detalles, 'D001');
                            $imss = $sumarClave($nomina->detalles, 'D002');
                            $infonavit = $sumarClave($nomina->detalles, 'D003');
                            $afore = $sumarClave($nomina->detalles, 'D004');
                            $fondo = $sumarClave($nomina->detalles, 'D005');
                            $descInc = $sumarClave($nomina->detalles, 'D090');
                            $otras = max(0, $afore + $fondo + $descInc);
                            $tipo = strtoupper((string) ($nomina->periodo->tipo_periodo ?? ''));
                            if ($tipo === 'SEMANAL') {
                                $diaInicio = (int) optional($nomina->periodo->fecha_inicio)->format('d');
                                $clasificacion = 'Semana ' . max(1, (int) ceil($diaInicio / 7));
                            } elseif (in_array($tipo, ['QUINCENAL', 'CATORCENAL'], true)) {
                                $diaInicio = (int) optional($nomina->periodo->fecha_inicio)->format('d');
                                $clasificacion = 'Quincena ' . ($diaInicio <= 15 ? '1' : '2');
                            } elseif ($tipo === 'MENSUAL') {
                                $clasificacion = 'Mensual';
                            } else {
                                $clasificacion = 'Sin clasificar';
                            }
                        @endphp
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="nomina-check" name="nomina_ids[]" value="{{ $nomina->id }}">
                            </td>
                            <td>{{ ($nomina->empleado->num_empleado ?? '-') . ' - ' . trim(($nomina->empleado->nombre ?? '') . ' ' . ($nomina->empleado->ap_paterno ?? '')) }}</td>
                            <td>{{ ($nomina->periodo->anio ?? '-') . '/' . ($nomina->periodo->numero_periodo ?? '-') }}</td>
                            <td>{{ $nomina->periodo->tipo_periodo ?? '-' }}</td>
                            <td>{{ $clasificacion }}</td>
                            <td>${{ number_format((float) $nomina->total_percepciones, 2) }}</td>
                            <td>${{ number_format(0, 2) }}</td>
                            <td>${{ number_format($isr, 2) }}</td>
                            <td>${{ number_format($imss, 2) }}</td>
                            <td>${{ number_format($infonavit, 2) }}</td>
                            <td>${{ number_format($otras, 2) }}</td>
                            <td>${{ number_format((float) $nomina->total_deducciones, 2) }}</td>
                            <td>${{ number_format((float) $nomina->neto_pagado, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13">No hay nominas para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>

        @if(!$isPrint)
            <div style="margin-top:12px;">{{ $nominas->links() }}</div>
        @endif
    </div>

    <script>
        (function () {
            const form = document.getElementById('form_nominas_seleccionadas');
            const selectorGeneral = document.getElementById('seleccionar_todas_nominas');

            if (!form || !selectorGeneral) {
                return;
            }

            const checks = Array.from(form.querySelectorAll('.nomina-check'));

            selectorGeneral.addEventListener('change', function () {
                checks.forEach((check) => {
                    check.checked = selectorGeneral.checked;
                });
            });

            checks.forEach((check) => {
                check.addEventListener('change', function () {
                    selectorGeneral.checked = checks.length > 0 && checks.every((item) => item.checked);
                });
            });

            form.addEventListener('submit', function (event) {
                const algunSeleccionado = checks.some((check) => check.checked);

                if (!algunSeleccionado) {
                    event.preventDefault();
                    alert('Selecciona al menos una nomina para imprimir o exportar.');
                }
            });
        })();
    </script>

    @if($isPrint)
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
@endsection
