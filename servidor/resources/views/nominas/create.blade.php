@extends('layouts.app')

@section('contenido')
    <style>
        .nomina-vista {
            background:
                radial-gradient(circle at 0% 0%, rgba(116, 167, 214, 0.12), transparent 35%),
                radial-gradient(circle at 100% 0%, rgba(94, 187, 160, 0.1), transparent 32%),
                linear-gradient(180deg, #f4f8fc 0%, #eef3f8 100%);
            border-radius: 18px;
            padding: 14px;
        }

        .nomina-vista > .tarjeta {
            margin-bottom: 0;
            border-radius: 18px;
            border: 1px solid #d5e1ed;
            background: linear-gradient(180deg, #fcfdff 0%, #f8fbff 100%);
            box-shadow: 0 16px 40px rgba(22, 44, 68, 0.08);
        }

        .nomina-encabezado {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            flex-wrap: wrap;
            padding-bottom: 10px;
            border-bottom: 1px dashed #d4e0ec;
        }

        .nomina-hero {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin: 16px 0 18px;
        }

        .nomina-hero-card {
            border: 1px solid #d3e1ee;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #f1f7ff 45%, #eef6fb 100%);
            padding: 15px;
            box-shadow: 0 6px 16px rgba(25, 48, 74, 0.05);
        }

        .nomina-hero-etiqueta {
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7d90;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .nomina-hero-valor {
            font-size: 20px;
            color: #1d3550;
            font-weight: 700;
            line-height: 1.2;
        }

        .nomina-hero-texto {
            margin-top: 6px;
            color: #5d7085;
            font-size: 13px;
            line-height: 1.45;
        }

        .nomina-layout {
            display: grid;
            grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .panel-lateral,
        .panel-principal {
            border: 1px solid #d8e4ef;
            border-radius: 16px;
            background: #ffffff;
            padding: 16px;
            box-shadow: 0 10px 28px rgba(25, 48, 74, 0.08);
        }

        .panel-lateral h3,
        .panel-principal h3 {
            margin: 0;
            color: #22374c;
        }

        .panel-lateral {
            position: sticky;
            top: 16px;
        }

        .panel-subtitulo {
            margin: 6px 0 12px;
            color: #617487;
            font-size: 13px;
            line-height: 1.45;
        }

        .panel-lateral input,
        .panel-lateral select,
        .panel-principal input,
        .panel-principal select {
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid #cfdae6;
            background: #fdfefe;
            box-shadow: inset 0 1px 2px rgba(30, 48, 66, 0.03);
        }

        .panel-lateral label,
        .panel-principal label {
            color: #35506b;
            font-size: 13px;
        }

        .resultados-lista {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 8px;
            max-height: 390px;
            overflow-y: auto;
            border-top: 1px solid #e2e9f1;
            padding-top: 10px;
        }

        .resultado-item {
            border: 1px solid #d8e1eb;
            border-radius: 12px;
            background: #f9fcff;
            padding: 10px 12px;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .resultado-item:hover {
            border-color: #9fc1df;
            background: #eef6ff;
            transform: translateY(-1px);
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

        .nomina-formulario {
            gap: 14px;
            border: 1px solid #e1eaf2;
            border-radius: 14px;
            padding: 12px;
            background: #fbfdff;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .nomina-campo-periodo {
            grid-column: 1 / -1;
        }

        .nomina-formulario .acciones {
            margin-top: 2px;
        }

        .periodos-compatibles {
            margin-top: 12px;
            border: 1px solid #d7e3ef;
            border-radius: 14px;
            background: #fafdff;
            padding: 12px;
            box-shadow: inset 0 0 0 1px rgba(218, 230, 242, 0.35);
        }

        .filtros-periodo-barra {
            margin-top: 8px;
            display: grid;
            grid-template-columns: minmax(170px, 220px) 1fr;
            gap: 10px;
            align-items: end;
        }

        .filtros-periodo-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            border-radius: 12px;
            background: #edf4fb;
            border: 1px solid #d4e2f0;
            color: #2d5d89;
            font-size: 12px;
            font-weight: 700;
            padding: 0 10px;
        }

        .periodos-compatibles-titulo {
            margin: 0 0 8px;
            color: #2a415a;
            font-size: 14px;
            font-weight: 700;
        }

        .periodos-compatibles-lista {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
        }

        .periodo-item {
            border: 1px solid #d2dfec;
            border-radius: 12px;
            background: #ffffff;
            padding: 10px 11px;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .periodo-item:hover {
            border-color: #97bddf;
            background: #eef6ff;
            transform: translateY(-1px);
        }

        .periodo-item.activo {
            border-color: #2f74ad;
            background: #e8f2fe;
            box-shadow: inset 0 0 0 1px rgba(47, 116, 173, 0.14);
        }

        .periodo-item-principal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-weight: 700;
            color: #1f3349;
            font-size: 14px;
        }

        .periodo-item-titulo {
            color: #20364e;
            line-height: 1.35;
        }

        .periodo-item-rango,
        .periodo-item-pago {
            margin-top: 4px;
            color: #4f647a;
            font-size: 13px;
            line-height: 1.35;
        }

        .periodo-item-meta {
            margin-top: 6px;
            color: #607488;
            font-size: 12px;
        }

        .periodo-item-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            background: #edf4fb;
            border: 1px solid #d6e4f2;
            color: #2b618f;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .bloque-detalle {
            margin-top: 14px;
            border-top: 1px solid #dde7f0;
            padding-top: 14px;
            border-radius: 12px;
        }

        .detalle-grid {
            gap: 12px;
        }

        .detalle-grid input[readonly] {
            background: #f5f9fe;
            border-color: #d5e0eb;
            color: #2e435b;
            font-weight: 600;
        }

        .detalle-empleado {
            margin-top: 12px;
            border-top: 1px solid #dde5ef;
            padding-top: 12px;
        }

        @media (max-width: 980px) {
            .nomina-vista {
                padding: 8px;
                border-radius: 14px;
            }

            .nomina-layout {
                grid-template-columns: 1fr;
            }

            .panel-lateral {
                position: static;
            }

            .resultados-lista {
                max-height: 240px;
            }
        }
    </style>

    <div class="nomina-vista">
    <div class="tarjeta">
        <div class="nomina-encabezado">
            <div>
                <h2 style="margin:0;">Calcular nomina</h2>
                <p style="margin:8px 0 0; color:#607284; font-size:13px; line-height:1.5; max-width:780px;">
                    Flujo asistido para buscar empleados, validar periodos compatibles y calcular nomina individual o masiva sin salir de la misma pantalla.
                </p>
            </div>
            <div class="acciones" style="margin-top:0;">
                <a class="boton secundario" href="{{ route('nominas.index') }}">Ver historial</a>
            </div>
        </div>

        <section class="nomina-hero">
            <article class="nomina-hero-card">
                <div class="nomina-hero-etiqueta">Empleados visibles</div>
                <div class="nomina-hero-valor" id="kpi_empleados_disponibles">{{ $empleados->count() }}</div>
                <div class="nomina-hero-texto">Filtrados por permisos y area del usuario actual.</div>
            </article>
            <article class="nomina-hero-card">
                <div class="nomina-hero-etiqueta">Periodos abiertos</div>
                <div class="nomina-hero-valor">{{ $periodos->count() }}</div>
                <div class="nomina-hero-texto">Solo se muestran periodos listos para calculo.</div>
            </article>
            <article class="nomina-hero-card">
                <div class="nomina-hero-etiqueta">Modo de trabajo</div>
                <div class="nomina-hero-valor">Guiado</div>
                <div class="nomina-hero-texto">Selecciona un empleado en la izquierda y revisa el detalle antes de guardar.</div>
            </article>
        </section>

        <div class="nomina-layout">
            <aside class="panel-lateral">
                <h3 style="font-size:1rem;">Buscar y seleccionar empleado</h3>
                <p class="panel-subtitulo">Localiza por CURP, RFC, nombre o puesto para continuar con el calculo.</p>
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
                <h3 style="font-size:1rem;">Configuracion de calculo</h3>
                <p class="panel-subtitulo">Completa los campos base y ejecuta calculo individual o generacion masiva.</p>

                <form method="POST" action="{{ route('nominas.store') }}" class="grilla nomina-formulario">
                    @csrf
                    <div>
                        <label>Empleado seleccionado</label>
                        <input id="empleado_seleccionado" readonly value="Selecciona un empleado en la lista" required>
                        <input id="empleado_id" type="hidden" name="empleado_id" value="{{ old('empleado_id') }}" required>
                        <input id="tipo_pago_submit" type="hidden" name="tipo_pago" value="{{ $filtros['tipo_pago'] ?? '' }}">
                    </div>
                    <div class="nomina-campo-periodo">
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
                                            $mesNumerico = optional($periodo->fecha_inicio)->format('m') ?? '';
                                            $descripcion = $periodo->tipo_periodo . ' #' . $periodo->numero_periodo
                                                . ' | ' . $fechaInicio . ' - ' . $fechaFin
                                                . ' | Pago: ' . $fechaPago;
                                        @endphp
                                        <option
                                            value="{{ $periodo->id }}"
                                            data-tipo-periodo="{{ strtoupper($periodo->tipo_periodo) }}"
                                            data-mes="{{ $mesNumerico }}"
                                            data-periodo-tipo="{{ strtoupper($periodo->tipo_periodo) }}"
                                            data-periodo-numero="{{ $periodo->numero_periodo }}"
                                            data-periodo-inicio="{{ $fechaInicio }}"
                                            data-periodo-fin="{{ $fechaFin }}"
                                            data-periodo-pago="{{ $fechaPago }}"
                                            @selected(old('periodo_nomina_id') == $periodo->id)
                                        >{{ $descripcion }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <div class="filtros-periodo-barra">
                            <div>
                                <label for="filtro_mes_periodo">Filtro de mes</label>
                                <select id="filtro_mes_periodo">
                                    <option value="">Todos</option>
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                            <div class="filtros-periodo-chip" id="chip_periodo_filtrado">Mostrando periodos de todos los meses</div>
                        </div>

                        <small id="ayuda_periodo" style="display:block; margin-top:6px; color:#54687d;">Seleccione un empleado para ver periodos compatibles.</small>

                        <div class="periodos-compatibles">
                            <p class="periodos-compatibles-titulo">Lista de periodos compatibles</p>
                            <ul id="lista_periodos_compatibles" class="periodos-compatibles-lista"></ul>
                            <p id="sin_periodos_compatibles" style="display:none; margin:8px 0 0; color:#8a3a4a; font-weight:600; font-size:12px;">
                                No hay periodos compatibles para el empleado seleccionado.
                            </p>
                        </div>
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

                <div class="bloque-detalle">
                    <h3 style="font-size:1rem;">Datos del empleado (desglosado)</h3>
                    <p class="panel-subtitulo" style="margin-top:6px;">Resumen de identidad laboral para validar que el calculo se aplique al colaborador correcto.</p>

                    <div class="grilla detalle-grid">
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
            const kpiEmpleadosDisponibles = document.getElementById('kpi_empleados_disponibles');
            const sinResultados = document.getElementById('sin_resultados');
            const btnLimpiarBusqueda = document.getElementById('btn_limpiar_busqueda');
            const filtroTipoPago = document.getElementById('filtro_tipo_pago');
            const periodoSelect = document.getElementById('periodo_nomina_id');
            const ayudaPeriodo = document.getElementById('ayuda_periodo');
            const filtroMesPeriodo = document.getElementById('filtro_mes_periodo');
            const chipPeriodoFiltrado = document.getElementById('chip_periodo_filtrado');
            const listaPeriodosCompatibles = document.getElementById('lista_periodos_compatibles');
            const sinPeriodosCompatibles = document.getElementById('sin_periodos_compatibles');
            const tipoPagoSubmit = document.getElementById('tipo_pago_submit');
            const dataNode = document.getElementById('nomina-empleados-data');

            if (!searchInput || !hiddenEmpleadoId || !empleadoSeleccionado || !listaResultados || !totalResultados || !kpiEmpleadosDisponibles || !sinResultados || !btnLimpiarBusqueda || !filtroTipoPago || !periodoSelect || !ayudaPeriodo || !filtroMesPeriodo || !chipPeriodoFiltrado || !listaPeriodosCompatibles || !sinPeriodosCompatibles || !tipoPagoSubmit || !dataNode) {
                return;
            }

            const mesesTexto = {
                '01': 'enero',
                '02': 'febrero',
                '03': 'marzo',
                '04': 'abril',
                '05': 'mayo',
                '06': 'junio',
                '07': 'julio',
                '08': 'agosto',
                '09': 'septiembre',
                '10': 'octubre',
                '11': 'noviembre',
                '12': 'diciembre',
            };

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
                const mesSeleccionado = filtroMesPeriodo.value;

                let opcionSeleccionadaSigueValida = false;
                Array.from(periodoSelect.options).forEach((opcion) => {
                    if (!opcion.value) {
                        opcion.disabled = false;
                        return;
                    }

                    const tipoPeriodo = normalizarTipo(opcion.dataset.tipoPeriodo || '');
                    const coincideMes = mesSeleccionado === '' || (opcion.dataset.mes || '') === mesSeleccionado;
                    const esValidaTipo = tiposPermitidos.length === 0 || tiposPermitidos.includes(tipoPeriodo);
                    const esValida = esValidaTipo && coincideMes;
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
                    chipPeriodoFiltrado.textContent = mesSeleccionado === ''
                        ? 'Mostrando periodos de todos los meses'
                        : `Mostrando periodos de ${mesesTexto[mesSeleccionado] || 'mes seleccionado'}`;
                    renderListaPeriodosCompatibles();
                    return;
                }

                if (tiposPermitidos.length === 0) {
                    ayudaPeriodo.textContent = 'No se pudo identificar el tipo de pago del empleado; verifique su catalogo.';
                    chipPeriodoFiltrado.textContent = mesSeleccionado === ''
                        ? 'Mostrando periodos de todos los meses'
                        : `Mostrando periodos de ${mesesTexto[mesSeleccionado] || 'mes seleccionado'}`;
                    renderListaPeriodosCompatibles();
                    return;
                }

                const textoMes = mesSeleccionado === ''
                    ? 'todos los meses'
                    : (mesesTexto[mesSeleccionado] || 'mes seleccionado');

                ayudaPeriodo.textContent = `Tipo de pago: ${tipoPago}. Periodos permitidos: ${tiposPermitidos.join(', ')}. Filtro de mes: ${textoMes}.`;
                chipPeriodoFiltrado.textContent = mesSeleccionado === ''
                    ? 'Mostrando periodos de todos los meses'
                    : `Mostrando periodos de ${textoMes}`;
                renderListaPeriodosCompatibles();
            };

            const renderListaPeriodosCompatibles = () => {
                const opcionesCompatibles = Array.from(periodoSelect.options)
                    .filter((opcion) => opcion.value && !opcion.disabled);

                listaPeriodosCompatibles.innerHTML = '';
                sinPeriodosCompatibles.style.display = opcionesCompatibles.length === 0 ? 'block' : 'none';

                opcionesCompatibles.forEach((opcion) => {
                    const item = document.createElement('li');
                    item.className = 'periodo-item' + (opcion.value === periodoSelect.value ? ' activo' : '');
                    item.tabIndex = 0;

                    const grupo = opcion.parentElement && opcion.parentElement.tagName === 'OPTGROUP'
                        ? opcion.parentElement.label
                        : 'Sin mes';

                    const tipo = opcion.dataset.periodoTipo || opcion.dataset.tipoPeriodo || 'PERIODO';
                    const numero = opcion.dataset.periodoNumero || '';
                    const inicio = opcion.dataset.periodoInicio || '-';
                    const fin = opcion.dataset.periodoFin || '-';
                    const pago = opcion.dataset.periodoPago || '-';

                    const principal = document.createElement('div');
                    principal.className = 'periodo-item-principal';
                    const titulo = document.createElement('span');
                    titulo.className = 'periodo-item-titulo';
                    titulo.textContent = `${tipo} #${numero}`;

                    const badge = document.createElement('span');
                    badge.className = 'periodo-item-badge';
                    badge.textContent = opcion.value === periodoSelect.value ? 'ACTIVO' : 'ELEGIR';

                    principal.appendChild(titulo);
                    principal.appendChild(badge);

                    const rango = document.createElement('div');
                    rango.className = 'periodo-item-rango';
                    rango.textContent = `Rango: ${inicio} - ${fin}`;

                    const pagoTexto = document.createElement('div');
                    pagoTexto.className = 'periodo-item-pago';
                    pagoTexto.textContent = `Pago: ${pago}`;

                    const meta = document.createElement('div');
                    meta.className = 'periodo-item-meta';
                    meta.textContent = `Mes de referencia: ${grupo}`;

                    item.appendChild(principal);
                    item.appendChild(rango);
                    item.appendChild(pagoTexto);
                    item.appendChild(meta);

                    item.addEventListener('click', () => {
                        periodoSelect.value = opcion.value;
                        renderListaPeriodosCompatibles();
                    });

                    item.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            periodoSelect.value = opcion.value;
                            renderListaPeriodosCompatibles();
                        }
                    });

                    listaPeriodosCompatibles.appendChild(item);
                });
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
                kpiEmpleadosDisponibles.textContent = String(filtrados.length);
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

            filtroMesPeriodo.addEventListener('change', actualizarPeriodosCompatibles);

            periodoSelect.addEventListener('change', renderListaPeriodosCompatibles);

            tipoPagoSubmit.value = filtroTipoPago.value;

            if (seleccionadoId === '' && empleados.length > 0) {
                seleccionadoId = String(empleados[0].id);
            }

            renderResultados();
            actualizarDetalleEmpleado();
            renderListaPeriodosCompatibles();

        })();
    </script>
@endsection
