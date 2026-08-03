@extends('layouts.app')

@section('contenido')
    <style>
        .dash-shell {
            --d-bg: #f6f8fb;
            --d-card: #ffffff;
            --d-ink: #1f2c3b;
            --d-muted: #6b7a8b;
            --d-accent: #2f74ad;
            --d-accent-soft: #eef4fb;
            --d-border: #d9e2ec;
            --d-shadow: 0 8px 22px rgba(16, 30, 52, 0.08);
            color: var(--d-ink);
            background:
                radial-gradient(circle at 12% 0%, #ffffff 0%, var(--d-bg) 65%, #edf2f8 100%),
                linear-gradient(120deg, rgba(47, 116, 173, 0.06) 0%, rgba(47, 125, 82, 0.04) 100%);
            border-radius: 14px;
            padding: 12px;
        }

        .dash-hero {
            background: var(--d-card);
            color: var(--d-ink);
            border-radius: 14px;
            padding: 18px;
            box-shadow: var(--d-shadow);
            margin-bottom: 14px;
            border: 1px solid var(--d-border);
            position: relative;
            overflow: hidden;
        }

        .dash-hero::after {
            content: "";
            position: absolute;
            right: -90px;
            top: -90px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(47, 116, 173, 0.12) 0%, rgba(47, 116, 173, 0) 70%);
            pointer-events: none;
        }

        .dash-hero h1 {
            margin: 0;
            font-size: clamp(22px, 2.6vw, 30px);
            letter-spacing: 0.1px;
            color: #2a3a4d;
        }

        .dash-hero p {
            margin: 8px 0 0;
            max-width: 760px;
            line-height: 1.5;
            color: var(--d-muted);
            position: relative;
            z-index: 1;
        }

        .hero-meta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 12px;
            color: #50657b;
            border: 1px solid #d8e2ec;
            background: #f7fbff;
            border-radius: 999px;
            padding: 6px 11px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .dash-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            align-items: start;
        }

        .dash-analytics-top {
            background: var(--d-card);
            border-radius: 14px;
            border: 1px solid var(--d-border);
            box-shadow: var(--d-shadow);
            padding: 14px;
            margin-bottom: 14px;
        }

        .dash-nav,
        .dash-panel {
            background: var(--d-card);
            border-radius: 14px;
            border: 1px solid var(--d-border);
            box-shadow: var(--d-shadow);
        }

        .dash-nav {
            padding: 14px;
            position: sticky;
            top: 10px;
        }

        .dash-nav h3 {
            margin: 2px 0 10px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: var(--d-muted);
        }

        .dash-nav a {
            display: block;
            text-decoration: none;
            color: var(--d-ink);
            font-weight: 700;
            background: #f9fbfe;
            border: 1px solid #d8e1eb;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 8px;
            transition: transform 0.18s ease, background 0.2s ease;
        }

        .dash-nav a:hover {
            transform: translateX(2px);
            background: var(--d-accent-soft);
        }

        .dash-panel {
            padding: 14px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(160px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .metric {
            background: #f9fbfe;
            border: 1px solid #dbe3ec;
            border-radius: 12px;
            padding: 12px;
        }

        .metric .label {
            color: var(--d-muted);
            font-size: 12px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .metric .value {
            margin: 4px 0 0;
            font-weight: 800;
            font-size: 26px;
            color: #2a3a4d;
        }

        .metric .hint {
            margin-top: 4px;
            font-size: 12px;
            color: var(--d-muted);
        }

        .dash-two {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 12px;
        }

        .box {
            background: #ffffff;
            border: 1px solid #dbe3ec;
            border-radius: 12px;
            padding: 12px;
        }

        .box h3 {
            margin: 0 0 10px;
            color: #2a3a4d;
        }

        .status-chip {
            display: inline-block;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            letter-spacing: 0.4px;
        }

        .chip-pagada { background: #edf8f2; color: #2f7d52; }
        .chip-calculada { background: #edf4fb; color: #2f74ad; }
        .chip-borrador { background: #f8f3e9; color: #8b6b2d; }
        .chip-cancelada { background: #fbeff2; color: #9a3346; }

        .chart-grid {
            display: flex;
            align-items: stretch;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 4px;
            scrollbar-width: thin;
        }

        .insight-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(140px, 1fr));
            gap: 8px;
            margin-bottom: 10px;
        }

        .insight-item {
            border: 1px solid #dbe4ef;
            background: #f6faff;
            border-radius: 10px;
            padding: 8px 10px;
        }

        .insight-item .k {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #60758a;
            font-weight: 700;
        }

        .insight-item .v {
            margin-top: 3px;
            font-size: 15px;
            font-weight: 800;
            color: #27435f;
        }

        .chart-card {
            border: 1px solid #dfe6ee;
            border-radius: 10px;
            padding: 10px;
            background: #fcfdff;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 0 0 360px;
        }

        .chart-card.wide {
            flex-basis: 620px;
        }

        .chart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .chart-card h4 {
            margin: 0;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #3f5469;
        }

        .chart-chip {
            font-size: 11px;
            color: #4d647c;
            background: #eef4fb;
            border: 1px solid #d7e4f2;
            border-radius: 999px;
            padding: 3px 8px;
            font-weight: 700;
            white-space: nowrap;
        }

        .chart-note {
            margin: -2px 0 8px;
            color: #607488;
            font-size: 12px;
            line-height: 1.35;
        }

        .chart-wrap {
            position: relative;
            min-height: 220px;
        }

        .chart-wrap.tall {
            min-height: 250px;
        }

        .chart-legend {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 6px;
        }

        .legend-item {
            border: 1px solid #dfe7f0;
            border-radius: 8px;
            background: #f7fbff;
            padding: 7px 8px;
        }

        .legend-item .k {
            font-size: 11px;
            color: #5f7388;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
        }

        .legend-item .v {
            margin-top: 2px;
            font-size: 14px;
            color: #29425d;
            font-weight: 800;
        }

        .chart-empty {
            border: 1px dashed #d4deea;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: #5f7084;
            font-weight: 600;
            background: #f8fbff;
        }

        .mini-item,
        .box,
        .dash-hero,
        .dash-nav a,
        .metric,
        .dash-panel {
            color: var(--d-ink);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(130px, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .quick-actions a {
            text-decoration: none;
            border: 1px solid #dbe3ec;
            background: #f7faff;
            color: #2b3c4f;
            font-weight: 700;
            border-radius: 10px;
            padding: 9px 10px;
            text-align: center;
        }

        .quick-actions a:hover {
            background: #ecf4fb;
        }

        .tabla th,
        .tabla td {
            border-bottom-color: #e1e8f0;
        }

        .tabla th {
            color: #6a7a8c;
        }

        .tabla td {
            color: #2e3d50;
        }

        @media (max-width: 1040px) {
            .dash-layout {
                grid-template-columns: 1fr;
            }

            .dash-nav {
                position: static;
            }

            .metric-grid {
                grid-template-columns: repeat(2, minmax(160px, 1fr));
            }

            .dash-two {
                grid-template-columns: 1fr;
            }

            .chart-card {
                flex-basis: 320px;
            }

            .chart-card.wide {
                flex-basis: 500px;
            }

            .chart-wrap,
            .chart-wrap.tall {
                min-height: 220px;
            }
        }

        @media (max-width: 640px) {
            .metric-grid,
            .quick-actions {
                grid-template-columns: 1fr;
            }

            .insight-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="dash-shell">
        <section class="dash-hero">
            <h1>Centro de Control Operativo</h1>
            <p>
                Seguimiento ejecutivo de nomina, personal e incidencias con una vista clara,
                enfocada en productividad y control de cierre por periodo.
            </p>
            <div class="hero-meta">Actualizado: {{ now()->format('d/m/Y H:i') }} | Vision ejecutiva de 6 meses</div>
        </section>

        <section class="dash-analytics-top">
            <h3 style="margin:0 0 10px; color:#2a3a4d;">Analitica rapida</h3>
            <div class="insight-grid">
                <div class="insight-item">
                    <div class="k">Incidencia principal</div>
                    <div class="v" id="insight-incidencia">Sin datos</div>
                </div>
                <div class="insight-item">
                    <div class="k">Plantilla activa</div>
                    <div class="v" id="insight-activos">Sin datos</div>
                </div>
                <div class="insight-item">
                    <div class="k">Neto promedio mensual</div>
                    <div class="v" id="insight-neto">$0.00</div>
                </div>
            </div>

            <div class="chart-grid">
                <div class="chart-card">
                    <div class="chart-head">
                        <h4>Incidencias por tipo</h4>
                        <span class="chart-chip">Frecuencia</span>
                    </div>
                    <p class="chart-note">Comparativo por frecuencia. Muestra que tipo de incidencia concentra mas eventos.</p>
                    @if($incidenciasData->isNotEmpty())
                        <div class="chart-wrap"><canvas id="graficaIncidencias"></canvas></div>
                    @else
                        <div class="chart-empty">Sin incidencias registradas para graficar.</div>
                    @endif
                </div>

                <div class="chart-card wide">
                    <div class="chart-head">
                        <h4>Tendencia de nomina (ultimos 6 meses)</h4>
                        <span class="chart-chip">Volumen + monto</span>
                    </div>
                    <p class="chart-note">Volumen de nominas y neto pagado por mes, con promedio movil para identificar tendencia.</p>
                    <div class="chart-wrap tall"><canvas id="graficaTendenciaNomina"></canvas></div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="k">Nominas mes actual</div>
                            <div class="v">{{ number_format($kpis['nominas_mes']) }}</div>
                        </div>
                        <div class="legend-item">
                            <div class="k">Incidencias mes actual</div>
                            <div class="v">{{ number_format($kpis['incidencias_mes']) }}</div>
                        </div>
                        <div class="legend-item">
                            <div class="k">Neto pagado mes</div>
                            <div class="v">${{ number_format($kpis['neto_pagado_mes'], 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="chart-card wide">
                    <div class="chart-head">
                        <h4>Estatus de nominas recientes</h4>
                        <span class="chart-chip">Ultimos registros</span>
                    </div>
                    <p class="chart-note">Permite identificar si la operacion esta concentrada en borradores, calculadas, pagadas o canceladas.</p>
                    <div class="chart-wrap"><canvas id="graficaEstatusNomina"></canvas></div>
                </div>
            </div>
        </section>

        <div class="dash-layout">
            <main class="dash-panel">
                <section class="metric-grid">
                    <article class="metric">
                        <div class="label">Usuarios activos</div>
                        <div class="value">{{ number_format($kpis['usuarios_activos']) }}</div>
                        <div class="hint">Control de cuentas habilitadas</div>
                    </article>
                    <article class="metric">
                        <div class="label">Clientes activos</div>
                        <div class="value">{{ number_format($kpis['clientes_activos']) }}</div>
                        <div class="hint">Base comercial vigente</div>
                    </article>
                    <article class="metric">
                        <div class="label">Empleados activos</div>
                        <div class="value">{{ number_format($kpis['empleados_activos']) }}</div>
                        <div class="hint">Plantilla en operacion</div>
                    </article>
                    <article class="metric">
                        <div class="label">Incidencias del mes</div>
                        <div class="value">{{ number_format($kpis['incidencias_mes']) }}</div>
                        <div class="hint">Eventos administrativos acumulados</div>
                    </article>
                    <article class="metric">
                        <div class="label">Nominas del mes</div>
                        <div class="value">{{ number_format($kpis['nominas_mes']) }}</div>
                        <div class="hint">Procesos de calculo registrados</div>
                    </article>
                    <article class="metric">
                        <div class="label">Neto pagado (MXN)</div>
                        <div class="value">${{ number_format($kpis['neto_pagado_mes'], 2) }}</div>
                        <div class="hint">Dispersion acumulada del periodo mensual</div>
                    </article>
                </section>

                <section class="dash-two">
                    <div class="box" style="overflow-x:auto;">
                        <h3>Nominas recientes</h3>
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Empleado ID</th>
                                    <th>Neto pagado</th>
                                    <th>Estatus</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nominasRecientes as $nomina)
                                    @php
                                        $claseEstatus = match ($nomina->estatus) {
                                            'PAGADA' => 'chip-pagada',
                                            'CALCULADA' => 'chip-calculada',
                                            'BORRADOR' => 'chip-borrador',
                                            default => 'chip-cancelada',
                                        };
                                    @endphp
                                    <tr>
                                        <td>#{{ $nomina->id }}</td>
                                        <td>{{ $nomina->empleado_id }}</td>
                                        <td>${{ number_format((float) $nomina->neto_pagado, 2) }}</td>
                                        <td><span class="status-chip {{ $claseEstatus }}">{{ $nomina->estatus }}</span></td>
                                        <td>{{ $nomina->created_at?->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">Sin movimientos de nomina registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="quick-actions">
                            <a href="{{ route('nominas.create') }}">Nueva nomina</a>
                            <a href="{{ route('incidencias.create') }}">Capturar incidencia</a>
                            <a href="{{ route('catalogos-nomina.index') }}">Ir a catalogos</a>
                        </div>
                    </div>

                </section>
            </main>
        </div>
    </div>

    <script id="dashboard-incidencias-data" type="application/json">@json($incidenciasData)</script>
    <script id="dashboard-empleados-data" type="application/json">@json($empleadosData)</script>
    <script id="dashboard-series-data" type="application/json">@json($seriesMensuales)</script>
    <script id="dashboard-nominas-recientes-data" type="application/json">@json($nominasRecientes->map(fn ($nomina) => ['estatus' => $nomina->estatus]))</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            const leerJson = (id) => {
                const nodo = document.getElementById(id);
                if (!nodo) {
                    return [];
                }

                try {
                    return JSON.parse(nodo.textContent || '[]');
                } catch (error) {
                    return [];
                }
            };

            const incidencias = leerJson('dashboard-incidencias-data');
            const empleados = leerJson('dashboard-empleados-data');
            const seriesMensuales = leerJson('dashboard-series-data');
            const nominasRecientes = leerJson('dashboard-nominas-recientes-data');

            const fmtNumero = new Intl.NumberFormat('es-MX');
            const fmtMoneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

            const totalIncidencias = incidencias.reduce((acc, item) => acc + Number(item.total || 0), 0);
            const totalEmpleados = empleados.reduce((acc, item) => acc + Number(item.total || 0), 0);
            const topIncidencia = incidencias.length > 0 ? incidencias[0] : null;
            const totalActivos = empleados.find(item => String(item.estatus).toUpperCase() === 'ACTIVO')?.total || 0;
            const netoSeries = Array.isArray(seriesMensuales.neto) ? seriesMensuales.neto.map(v => Number(v || 0)) : [];
            const promedioNeto = netoSeries.length > 0
                ? netoSeries.reduce((acc, v) => acc + v, 0) / netoSeries.length
                : 0;

            const nodoInsightIncidencia = document.getElementById('insight-incidencia');
            if (nodoInsightIncidencia) {
                if (topIncidencia && totalIncidencias > 0) {
                    const p = ((Number(topIncidencia.total) / totalIncidencias) * 100).toFixed(1);
                    nodoInsightIncidencia.textContent = `${topIncidencia.tipo} (${p}%)`;
                } else {
                    nodoInsightIncidencia.textContent = 'Sin incidencias';
                }
            }

            const nodoInsightActivos = document.getElementById('insight-activos');
            if (nodoInsightActivos) {
                if (totalEmpleados > 0) {
                    const p = ((Number(totalActivos) / totalEmpleados) * 100).toFixed(1);
                    nodoInsightActivos.textContent = `${fmtNumero.format(totalActivos)} de ${fmtNumero.format(totalEmpleados)} (${p}%)`;
                } else {
                    nodoInsightActivos.textContent = 'Sin empleados';
                }
            }

            const nodoInsightNeto = document.getElementById('insight-neto');
            if (nodoInsightNeto) {
                nodoInsightNeto.textContent = fmtMoneda.format(promedioNeto);
            }

            const fontFamily = "'Source Sans Pro','Segoe UI',Tahoma,Geneva,Verdana,sans-serif";
            Chart.defaults.font.family = fontFamily;
            Chart.defaults.color = '#465a70';

            if (incidencias.length > 0) {
                const ctxIncidencias = document.getElementById('graficaIncidencias');
                if (ctxIncidencias) {
                    new Chart(ctxIncidencias, {
                        type: 'bar',
                        data: {
                            labels: incidencias.map(item => item.tipo),
                            datasets: [{
                                label: 'Registros',
                                data: incidencias.map(item => item.total),
                                borderRadius: 8,
                                backgroundColor: '#b9892f',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => {
                                            const valor = Number(context.raw || 0);
                                            const p = totalIncidencias > 0 ? ((valor / totalIncidencias) * 100).toFixed(1) : '0.0';
                                            return `${fmtNumero.format(valor)} registros (${p}%)`;
                                        },
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    grid: { display: false },
                                },
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0,
                                        callback: (value) => fmtNumero.format(value),
                                    },
                                    grid: { color: '#edf2f7' },
                                },
                            },
                        },
                    });
                }
            }

            const ctxTendencia = document.getElementById('graficaTendenciaNomina');
            if (ctxTendencia) {
                const promedioMovil = (Array.isArray(seriesMensuales.neto) ? seriesMensuales.neto : []).map((_, index, arr) => {
                    const inicio = Math.max(0, index - 2);
                    const ventana = arr.slice(inicio, index + 1).map(v => Number(v || 0));
                    const suma = ventana.reduce((acc, valor) => acc + valor, 0);
                    return ventana.length > 0 ? +(suma / ventana.length).toFixed(2) : 0;
                });

                new Chart(ctxTendencia, {
                    type: 'line',
                    data: {
                        labels: seriesMensuales.labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Nominas procesadas',
                                data: seriesMensuales.nominas,
                                yAxisID: 'yNominas',
                                borderRadius: 6,
                                backgroundColor: 'rgba(47, 116, 173, 0.25)',
                                borderColor: '#2f74ad',
                                borderWidth: 1,
                            },
                            {
                                type: 'line',
                                label: 'Neto pagado (MXN)',
                                data: seriesMensuales.neto,
                                yAxisID: 'yNeto',
                                tension: 0.35,
                                borderColor: '#2f7d52',
                                backgroundColor: 'rgba(47, 125, 82, 0.14)',
                                fill: true,
                                pointRadius: 3,
                            },
                            {
                                type: 'line',
                                label: 'Promedio movil neto (3 meses)',
                                data: promedioMovil,
                                yAxisID: 'yNeto',
                                tension: 0.35,
                                borderColor: '#1f4d7d',
                                borderDash: [6, 4],
                                pointRadius: 0,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const serie = context.dataset.label || '';
                                        const valor = Number(context.raw || 0);
                                        if (serie.toLowerCase().includes('nominas')) {
                                            return `${serie}: ${fmtNumero.format(valor)}`;
                                        }

                                        return `${serie}: ${fmtMoneda.format(valor)}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            yNominas: {
                                beginAtZero: true,
                                position: 'left',
                                ticks: {
                                    precision: 0,
                                    callback: (value) => fmtNumero.format(value),
                                },
                                grid: { color: '#edf2f7' },
                                title: { display: true, text: 'Nominas' },
                            },
                            yNeto: {
                                beginAtZero: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    callback: (value) => fmtMoneda.format(value),
                                },
                                title: { display: true, text: 'MXN' },
                            },
                        },
                    },
                });
            }

            const ctxEstatus = document.getElementById('graficaEstatusNomina');
            if (ctxEstatus && Array.isArray(nominasRecientes)) {
                const mapa = nominasRecientes.reduce((acc, item) => {
                    const estatus = String(item.estatus || 'SIN_ESTATUS');
                    acc[estatus] = (acc[estatus] || 0) + 1;
                    return acc;
                }, {});

                const etiquetas = Object.keys(mapa);
                const valores = etiquetas.map(etiqueta => mapa[etiqueta]);

                if (etiquetas.length > 0) {
                    new Chart(ctxEstatus, {
                        type: 'pie',
                        data: {
                            labels: etiquetas,
                            datasets: [{
                                data: valores,
                                backgroundColor: ['#2f74ad', '#2f7d52', '#8b6b2d', '#9a3346'],
                                borderColor: '#ffffff',
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { boxWidth: 12, padding: 14 },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => {
                                            const valor = Number(context.raw || 0);
                                            const total = valores.reduce((acc, n) => acc + n, 0);
                                            const p = total > 0 ? ((valor / total) * 100).toFixed(1) : '0.0';
                                            return `${context.label}: ${fmtNumero.format(valor)} (${p}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }
            }
        })();
    </script>
@endsection
