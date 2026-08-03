@extends('layouts.app')

@section('contenido')
    <style>
        .nomina-layout {
            display: grid;
            grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
            gap: 14px;
            align-items: start;
        }

        .panel-lateral,
        .panel-principal {
            border: 1px solid #d8e1eb;
            border-radius: 12px;
            background: #f9fbfe;
            padding: 12px;
        }

        .panel-lateral h3,
        .panel-principal h3 {
            margin: 0 0 8px;
        }

        .resultados-lista {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 8px;
            max-height: 360px;
            overflow-y: auto;
        }

        .resultado-item {
            border: 1px solid #d8e1eb;
            border-radius: 10px;
            background: #f9fbfe;
            padding: 10px 12px;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .resultado-item:hover {
            border-color: #9fc1df;
            background: #eef6ff;
        }

        .resultado-item.activo {
            border-color: #2f74ad;
            background: #e9f3ff;
            box-shadow: inset 0 0 0 1px rgba(47, 116, 173, 0.15);
        }

        .resultado-principal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-weight: 700;
            color: #1f3349;
        }

        .resultado-meta {
            margin-top: 4px;
            color: #54687d;
            font-size: 13px;
        }

        .badge-seleccion {
            display: inline-block;
            background: #edf4fb;
            border: 1px solid #d5e4f2;
            color: #2f5f8c;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .detalle-empleado {
            margin-top: 12px;
            border-top: 1px solid #dde5ef;
            padding-top: 12px;
        }

        @media (max-width: 980px) {
            .nomina-layout {
                grid-template-columns: 1fr;
            }

            .resultados-lista {
                max-height: 240px;
            }
        }
    </style>

    <div class="tarjeta">
        <h2 style="margin-top:0;">Calcular nomina</h2>
        <p style="margin: 0 0 10px; color:#607284; font-size:13px;">Todo integrado: busca, selecciona en la lista y calcula sin cambiar de bloque.</p>

        <div class="nomina-layout">
            <aside class="panel-lateral">
                <h3 style="font-size:1rem;">Buscar y seleccionar empleado</h3>
                <div>
                    <label>Tipo de pago</label>
                    <select id="filtro_tipo_pago" name="tipo_pago">
                        <option value="">Todos</option>
                        @foreach(['SEMANAL', 'QUINCENAL', 'MENSUAL'] as $tipoPago)
                            <option value="{{ $tipoPago }}" @selected(($filtros['tipo_pago'] ?? '') === $tipoPago)>{{ $tipoPago }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Busqueda (CURP, RFC o nombre)</label>
                    <input id="busqueda_empleado" value="{{ $filtros['buscar_empleado'] ?? '' }}" placeholder="Ejemplo: XEXX010101HNEXXXA4 o ABCD850101XX1">
                </div>

                <div class="acciones" style="margin-top:8px;">
                    <button id="btn_limpiar_busqueda" class="boton secundario" type="button">Limpiar</button>
                </div>

                <p style="margin:8px 0 10px;color:#607284;font-size:13px;">
                    Resultados: <strong id="total_resultados">{{ $empleados->count() }}</strong>
                </p>

                <ul id="lista_resultados" class="resultados-lista"></ul>
                <p id="sin_resultados" style="display:none; margin:6px 0 0; color:#8a3a4a; font-weight:600;">No se encontraron empleados con ese criterio.</p>
            </aside>

            <section class="panel-principal">
                <form method="POST" action="{{ route('nominas.store') }}" class="grilla">
                    @csrf
                    <div>
                        <label>Empleado seleccionado</label>
                        <input id="empleado_seleccionado" readonly value="Selecciona un empleado en la lista" required>
                        <input id="empleado_id" type="hidden" name="empleado_id" value="{{ old('empleado_id') }}" required>
                        <input id="tipo_pago_submit" type="hidden" name="tipo_pago" value="{{ $filtros['tipo_pago'] ?? '' }}">
                    </div>
                    <div>
                        <label>Periodo</label>
                        <select id="periodo_nomina_id" name="periodo_nomina_id" required>
                            <option value="">Seleccione</option>
                            @foreach($periodos->groupBy(fn($periodo) => optional($periodo->fecha_inicio)->format('m/Y') ?? 'Sin mes') as $mes => $periodosMes)
                                <optgroup label="{{ $mes }}">
                                    @foreach($periodosMes as $periodo)
                                        @php
                                            $fechaInicio = optional($periodo->fecha_inicio)->format('d/m/Y') ?? '-';
                                            $fechaFin = optional($periodo->fecha_fin)->format('d/m/Y') ?? '-';
                                            $fechaPago = optional($periodo->fecha_pago)->format('d/m/Y') ?? '-';
                                            $descripcion = $periodo->tipo_periodo . ' #' . $periodo->numero_periodo
                                                . ' | ' . $fechaInicio . ' - ' . $fechaFin
                                                . ' | Pago: ' . $fechaPago;
                                        @endphp
                                        <option value="{{ $periodo->id }}" data-tipo-periodo="{{ strtoupper($periodo->tipo_periodo) }}" @selected(old('periodo_nomina_id') == $periodo->id)>{{ $descripcion }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small id="ayuda_periodo" style="display:block; margin-top:6px; color:#54687d;">Seleccione un empleado para ver periodos compatibles.</small>
                    </div>
                    <div>
                        <label>Estatus inicial</label>
                        <select name="estatus">
                            @foreach(['CALCULADA', 'BORRADOR', 'PAGADA'] as $estatus)
                                <option value="{{ $estatus }}" @selected(old('estatus', 'CALCULADA') === $estatus)>{{ $estatus }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="acciones" style="grid-column: 1 / -1;">
                        <button class="boton" type="submit">Calcular y guardar</button>
                        <button class="boton" type="submit" formaction="{{ route('nominas.generar-masivo') }}" formnovalidate>
                            Generar nominas del periodo
                        </button>
                        <a class="boton secundario" href="{{ route('nominas.index') }}">Cancelar</a>
                    </div>
                </form>

                <div class="detalle-empleado">
                    <h3 style="font-size:1rem;">Datos del empleado (desglosado)</h3>

                    <div class="grilla">
                        <div>
                            <label>Numero de empleado</label>
                            <input id="detalle_num_empleado" readonly value="-">
                        </div>
                        <div>
                            <label>Nombre completo</label>
                            <input id="detalle_nombre_completo" readonly value="-">
                        </div>
                        <div>
                            <label>CURP</label>
                            <input id="detalle_curp" readonly value="-">
                        </div>
                        <div>
                            <label>RFC</label>
                            <input id="detalle_rfc" readonly value="-">
                        </div>
                        <div>
                            <label>NSS</label>
                            <input id="detalle_nss" readonly value="-">
                        </div>
                        <div>
                            <label>Fecha de ingreso</label>
                            <input id="detalle_f_ingreso" readonly value="-">
                        </div>
                        <div>
                            <label>Departamento</label>
                            <input id="detalle_departamento" readonly value="-">
                        </div>
                        <div>
                            <label>Puesto</label>
                            <input id="detalle_puesto" readonly value="-">
                        </div>
                        <div>
                            <label>Salario diario</label>
                            <input id="detalle_sal_dia" readonly value="-">
                        </div>
                        <div>
                            <label>Salario integrado</label>
                            <input id="detalle_sal_int" readonly value="-">
                        </div>
                        <div>
                            <label>Tipo de pago</label>
                            <input id="detalle_tipo_pago" readonly value="-">
                        </div>
                        <div>
                            <label>Semanas cotizadas</label>
                            <input id="detalle_semanas" readonly value="-">
                        </div>
                        <div>
                            <label>Fondo retiro acumulado</label>
                            <input id="detalle_fondo_retiro" readonly value="-">
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script id="nomina-empleados-data" type="application/json">
        @json($empleadosData)
    </script>

    <script>
        (function () {
            const searchInput = document.getElementById('busqueda_empleado');
            const hiddenEmpleadoId = document.getElementById('empleado_id');
            const empleadoSeleccionado = document.getElementById('empleado_seleccionado');
            const listaResultados = document.getElementById('lista_resultados');
            const totalResultados = document.getElementById('total_resultados');
            const sinResultados = document.getElementById('sin_resultados');
            const btnLimpiarBusqueda = document.getElementById('btn_limpiar_busqueda');
            const filtroTipoPago = document.getElementById('filtro_tipo_pago');
            const periodoSelect = document.getElementById('periodo_nomina_id');
            const ayudaPeriodo = document.getElementById('ayuda_periodo');
            const tipoPagoSubmit = document.getElementById('tipo_pago_submit');
            const dataNode = document.getElementById('nomina-empleados-data');

            if (!searchInput || !hiddenEmpleadoId || !empleadoSeleccionado || !listaResultados || !totalResultados || !sinResultados || !btnLimpiarBusqueda || !filtroTipoPago || !periodoSelect || !ayudaPeriodo || !tipoPagoSubmit || !dataNode) {
                return;
            }

            let empleados = [];
            try {
                empleados = JSON.parse(dataNode.textContent || '[]');
            } catch (error) {
                empleados = [];
            }

            const mapaEmpleados = new Map(empleados.map((empleado) => [String(empleado.id), empleado]));
            let seleccionadoId = hiddenEmpleadoId.value ? String(hiddenEmpleadoId.value) : '';

            const normalizarTipo = (valor) => {
                const texto = (valor || '')
                    .toString()
                    .trim()
                    .toUpperCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[-_]/g, ' ')
                    .replace(/\s+/g, ' ');

                if (['SEMANAL'].includes(texto)) {
                    return 'SEMANAL';
                }

                if (['QUINCENAL', 'CATORCENAL', '14 DIAS', 'CADA 14 DIAS'].includes(texto)) {
                    return 'QUINCENAL';
                }

                if (['MENSUAL', 'MENSUALIDAD'].includes(texto)) {
                    return 'MENSUAL';
                }

                return '';
            };

            const periodosPermitidosPorTipoPago = (tipoPago) => {
                if (tipoPago === 'SEMANAL') {
                    return ['SEMANAL'];
                }

                if (tipoPago === 'QUINCENAL') {
                    return ['QUINCENAL', 'CATORCENAL'];
                }

                if (tipoPago === 'MENSUAL') {
                    return ['MENSUAL'];
                }

                return [];
            };

            const actualizarPeriodosCompatibles = () => {
                const empleado = mapaEmpleados.get(seleccionadoId);
                const tipoPago = normalizarTipo(empleado?.tipo_pago || '');
                const tiposPermitidos = periodosPermitidosPorTipoPago(tipoPago);

                let opcionSeleccionadaSigueValida = false;
                Array.from(periodoSelect.options).forEach((opcion) => {
                    if (!opcion.value) {
                        opcion.disabled = false;
                        return;
                    }

                    const tipoPeriodo = normalizarTipo(opcion.dataset.tipoPeriodo || '');
                    const esValida = tiposPermitidos.length === 0 || tiposPermitidos.includes(tipoPeriodo);
                    opcion.disabled = !esValida;

                    if (esValida && opcion.value === periodoSelect.value) {
                        opcionSeleccionadaSigueValida = true;
                    }
                });

                if (!opcionSeleccionadaSigueValida) {
                    periodoSelect.value = '';
                }

                if (empleado && periodoSelect.value === '') {
                    const primeraCompatible = Array.from(periodoSelect.options).find((opcion) => opcion.value && !opcion.disabled);
                    if (primeraCompatible) {
                        periodoSelect.value = primeraCompatible.value;
                    }
                }

                if (!empleado) {
                    ayudaPeriodo.textContent = 'Seleccione un empleado para ver periodos compatibles.';
                    return;
                }

                if (tiposPermitidos.length === 0) {
                    ayudaPeriodo.textContent = 'No se pudo identificar el tipo de pago del empleado; verifique su catalogo.';
                    return;
                }

                ayudaPeriodo.textContent = `Tipo de pago: ${tipoPago}. Periodos permitidos: ${tiposPermitidos.join(', ')}.`;
            };

            const setValor = (id, valor) => {
                const input = document.getElementById(id);
                if (!input) {
                    return;
                }

                input.value = valor && String(valor).trim() !== '' ? String(valor) : '-';
            };

            const actualizarDetalleEmpleado = () => {
                const empleado = mapaEmpleados.get(seleccionadoId);

                empleadoSeleccionado.value = empleado
                    ? `${empleado.num_empleado} - ${empleado.nombre_completo}`
                    : 'Selecciona un empleado en la lista';

                hiddenEmpleadoId.value = empleado ? String(empleado.id) : '';

                setValor('detalle_num_empleado', empleado?.num_empleado);
                setValor('detalle_nombre_completo', empleado?.nombre_completo);
                setValor('detalle_curp', empleado?.curp);
                setValor('detalle_rfc', empleado?.rfc);
                setValor('detalle_nss', empleado?.nss);
                setValor('detalle_f_ingreso', empleado?.f_ingreso);
                setValor('detalle_departamento', empleado?.departamento);
                setValor('detalle_puesto', empleado?.puesto);
                setValor('detalle_sal_dia', empleado ? `$${empleado.sal_dia}` : '-');
                setValor('detalle_sal_int', empleado ? `$${empleado.sal_int}` : '-');
                setValor('detalle_tipo_pago', empleado?.tipo_pago);
                setValor('detalle_semanas', empleado?.semanas_cotizadas);
                setValor('detalle_fondo_retiro', empleado ? `$${empleado.fondo_retiro}` : '-');

                actualizarPeriodosCompatibles();
            };

            const textoBusquedaEmpleado = (empleado) => {
                return [
                    empleado.num_empleado,
                    empleado.nombre_completo,
                    empleado.curp,
                    empleado.rfc,
                    empleado.nss,
                    empleado.departamento,
                    empleado.puesto,
                ].join(' ').toLowerCase();
            };

            const seleccionarEmpleado = (id) => {
                seleccionadoId = String(id);
                actualizarDetalleEmpleado();
                renderResultados();
            };

            const renderResultados = () => {
                const termino = searchInput.value.trim().toLowerCase();
                const tipoPagoSeleccionado = normalizarTipo(filtroTipoPago.value || '');
                const filtrados = empleados.filter((empleado) => {
                    const tipoPagoEmpleado = normalizarTipo(empleado.tipo_pago || '');
                    if (tipoPagoSeleccionado !== '' && tipoPagoEmpleado !== tipoPagoSeleccionado) {
                        return false;
                    }

                    if (termino === '') {
                        return true;
                    }

                    return textoBusquedaEmpleado(empleado).includes(termino);
                });

                const seleccionadoVisible = filtrados.some((empleado) => String(empleado.id) === seleccionadoId);
                if (!seleccionadoVisible) {
                    seleccionadoId = filtrados.length > 0 ? String(filtrados[0].id) : '';
                    actualizarDetalleEmpleado();
                }

                totalResultados.textContent = String(filtrados.length);
                sinResultados.style.display = filtrados.length === 0 ? 'block' : 'none';

                listaResultados.innerHTML = '';

                filtrados.forEach((empleado) => {
                    const item = document.createElement('li');
                    item.className = 'resultado-item' + (String(empleado.id) === seleccionadoId ? ' activo' : '');
                    item.tabIndex = 0;

                    const principal = document.createElement('div');
                    principal.className = 'resultado-principal';
                    principal.innerHTML = `
                        <span>${empleado.num_empleado} - ${empleado.nombre_completo}</span>
                        <span class="badge-seleccion">${String(empleado.id) === seleccionadoId ? 'SELECCIONADO' : 'SELECCIONAR'}</span>
                    `;

                    const meta = document.createElement('div');
                    meta.className = 'resultado-meta';
                    meta.textContent = `CURP: ${empleado.curp} | RFC: ${empleado.rfc} | Puesto: ${empleado.puesto}`;

                    item.appendChild(principal);
                    item.appendChild(meta);

                    item.addEventListener('click', () => seleccionarEmpleado(empleado.id));
                    item.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            seleccionarEmpleado(empleado.id);
                        }
                    });

                    listaResultados.appendChild(item);
                });
            };

            btnLimpiarBusqueda.addEventListener('click', () => {
                searchInput.value = '';
                renderResultados();
                searchInput.focus();
            });

            searchInput.addEventListener('input', renderResultados);
            filtroTipoPago.addEventListener('change', () => {
                tipoPagoSubmit.value = filtroTipoPago.value;
                renderResultados();
            });

            tipoPagoSubmit.value = filtroTipoPago.value;

            if (seleccionadoId === '' && empleados.length > 0) {
                seleccionadoId = String(empleados[0].id);
            }

            renderResultados();
            actualizarDetalleEmpleado();

        })();
    </script>
@endsection
