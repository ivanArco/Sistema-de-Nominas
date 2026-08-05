@php
    $tiposPeriodo = [
        'SEMANAL' => 'Semanal',
        'QUINCENAL' => 'Quincenal',
        'MENSUAL' => 'Mensual',
    ];

    $estatusPeriodo = [
        'ABIERTO' => 'Abierto',
        'CALCULADO' => 'Calculado',
        'CERRADO' => 'Cerrado',
        'TIMBRADO' => 'Timbrado',
    ];

    $esEdicion = isset($periodo);
    $mesesDisponibles = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];
    $mesReferenciaInicial = (int) old('mes_referencia', $periodo->fecha_inicio?->month ?? now()->month);
    $segmentoInicial = old('segmento_periodo');

    if ($segmentoInicial === null && isset($periodo)) {
        $diaInicio = (int) ($periodo->fecha_inicio?->day ?? 1);
        $tipoInicial = old('tipo_periodo', $periodo->tipo_periodo ?? 'QUINCENAL');

        if ($tipoInicial === 'QUINCENAL') {
            $segmentoInicial = $diaInicio <= 15 ? 'Q1' : 'Q2';
        } elseif ($tipoInicial === 'MENSUAL') {
            $segmentoInicial = 'M1';
        } else {
            $segmentoInicial = 'S'.(string) max(1, (int) ceil($diaInicio / 7));
        }
    }

    $segmentoInicial = $segmentoInicial ?? 'Q1';
@endphp

<style>
    .periodo-formulario {
        display: grid;
        gap: 18px;
        margin-top: 18px;
    }

    .periodo-hero {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid #dce6f0;
        background: linear-gradient(135deg, #f8fbff 0%, #eef4fa 52%, #fdfefe 100%);
    }

    .periodo-kpi {
        display: grid;
        gap: 6px;
    }

    .periodo-kpi-etiqueta {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7a8d;
    }

    .periodo-kpi-valor {
        font-size: 20px;
        font-weight: 700;
        color: #203649;
    }

    .periodo-kpi-texto {
        color: #627285;
        font-size: 13px;
        line-height: 1.45;
    }

    .periodo-panel {
        border: 1px solid #dde5ee;
        border-radius: 16px;
        background: #fcfdff;
        padding: 18px;
    }

    .periodo-panel-encabezado {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .periodo-panel-titulo {
        margin: 0;
        font-size: 18px;
        color: #223549;
    }

    .periodo-panel-subtitulo {
        margin: 4px 0 0;
        color: #677487;
        font-size: 13px;
    }

    .periodo-chip {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: #e6f1fb;
        color: #1e5a8d;
        font-size: 12px;
        font-weight: 700;
    }

    .periodo-grilla {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .periodo-campo {
        display: grid;
        gap: 6px;
    }

    .periodo-campo-completo {
        grid-column: 1 / -1;
    }

    .periodo-campo-destacado {
        padding: 14px;
        border-radius: 14px;
        background: #f8fbff;
        border: 1px solid #d9e5f1;
    }

    .periodo-formulario input,
    .periodo-formulario select {
        margin-top: 0;
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #ccd8e5;
        background: #fff;
        padding: 10px 12px;
        box-shadow: inset 0 1px 2px rgba(31, 41, 55, 0.03);
    }

    .periodo-ayuda {
        margin: 0;
        color: #677487;
        font-size: 12px;
        line-height: 1.5;
    }

    .periodo-resumen-fechas {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }

    .periodo-fecha-box {
        padding: 14px;
        border-radius: 14px;
        background: #f6f9fc;
        border: 1px solid #e1e8f0;
    }

    .periodo-fecha-box strong {
        display: block;
        margin-bottom: 4px;
        color: #203649;
    }

    .periodo-fecha-box span {
        color: #627285;
        font-size: 13px;
    }
</style>

<div class="periodo-formulario">
    <section class="periodo-hero">
        <div class="periodo-kpi">
            <span class="periodo-kpi-etiqueta">Modo</span>
            <span class="periodo-kpi-valor">{{ $esEdicion ? 'Edicion' : 'Alta' }}</span>
            <span class="periodo-kpi-texto">Captura manual de periodos para control operativo y ajustes fuera de la generacion automatica.</span>
        </div>
        <div class="periodo-kpi">
            <span class="periodo-kpi-etiqueta">Tipo sugerido</span>
            <span class="periodo-kpi-valor">{{ $tiposPeriodo[old('tipo_periodo', $periodo->tipo_periodo ?? 'QUINCENAL')] ?? 'Quincenal' }}</span>
            <span class="periodo-kpi-texto">Puedes cambiar entre periodos semanales, quincenales o mensuales segun el esquema de pago.</span>
        </div>
        <div class="periodo-kpi">
            <span class="periodo-kpi-etiqueta">Estatus inicial</span>
            <span class="periodo-kpi-valor">{{ $estatusPeriodo[old('estatus', $periodo->estatus ?? 'ABIERTO')] ?? 'Abierto' }}</span>
            <span class="periodo-kpi-texto">Se recomienda iniciar en abierto para permitir calculo y validacion posterior.</span>
        </div>
    </section>

    <section class="periodo-panel">
        <div class="periodo-panel-encabezado">
            <div>
                <h3 class="periodo-panel-titulo">Configuracion general</h3>
                <p class="periodo-panel-subtitulo">Define el anio, consecutivo y tipo del periodo antes de capturar las fechas.</p>
            </div>
            <span class="periodo-chip">Maximo 60 periodos por anio</span>
        </div>

        <div class="periodo-grilla">
            <div class="periodo-campo">
                <label for="anio_periodo">Anio</label>
                <input id="anio_periodo" type="number" name="anio" min="2020" max="2100" value="{{ old('anio', $periodo->anio ?? now()->year) }}" required>
                <p class="periodo-ayuda">Usa el anio fiscal en el que inicia el periodo.</p>
            </div>

            <div class="periodo-campo">
                <label for="numero_periodo">Numero de periodo</label>
                <input id="numero_periodo" type="number" name="numero_periodo" min="1" max="60" value="{{ old('numero_periodo', $periodo->numero_periodo ?? 1) }}" required>
                <p class="periodo-ayuda">Mantiene el consecutivo operativo dentro del tipo de pago definido.</p>
            </div>

            <div class="periodo-campo">
                <label for="tipo_periodo">Tipo de periodo</label>
                <select id="tipo_periodo" name="tipo_periodo" required>
                    @foreach($tiposPeriodo as $tipo => $etiqueta)
                        <option value="{{ $tipo }}" @selected(old('tipo_periodo', $periodo->tipo_periodo ?? 'QUINCENAL') === $tipo)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <p id="ayuda_tipo_periodo" class="periodo-ayuda"></p>
            </div>

            <div class="periodo-campo periodo-campo-destacado">
                <label for="mes_referencia">Mes</label>
                <select id="mes_referencia" name="mes_referencia">
                    @foreach($mesesDisponibles as $numeroMes => $nombreMes)
                        <option value="{{ $numeroMes }}" @selected($mesReferenciaInicial === $numeroMes)>{{ $nombreMes }}</option>
                    @endforeach
                </select>
                <p class="periodo-ayuda">Selecciona el mes base para que el sistema proponga automaticamente las fechas.</p>
            </div>

            <div class="periodo-campo periodo-campo-destacado">
                <label for="segmento_periodo">Bloque del mes</label>
                <select id="segmento_periodo" name="segmento_periodo" data-selected="{{ $segmentoInicial }}"></select>
                <p id="ayuda_segmento_periodo" class="periodo-ayuda">El bloque disponible cambia segun el tipo de periodo.</p>
            </div>

            <div class="periodo-campo">
                <label for="estatus_periodo">Estatus</label>
                <select id="estatus_periodo" name="estatus" required>
                    @foreach($estatusPeriodo as $estatus => $etiqueta)
                        <option value="{{ $estatus }}" @selected(old('estatus', $periodo->estatus ?? 'ABIERTO') === $estatus)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <p class="periodo-ayuda">El estatus controla el ciclo operativo del periodo dentro del modulo de nominas.</p>
            </div>
        </div>
    </section>

    <section class="periodo-panel">
        <div class="periodo-panel-encabezado">
            <div>
                <h3 class="periodo-panel-titulo">Calendario del periodo</h3>
                <p class="periodo-panel-subtitulo">Registra el rango trabajado y la fecha estimada de pago.</p>
            </div>
        </div>

        <div class="periodo-grilla">
            <div class="periodo-campo">
                <label for="fecha_inicio">Fecha inicio</label>
                <input id="fecha_inicio" type="date" name="fecha_inicio" value="{{ old('fecha_inicio', isset($periodo) ? $periodo->fecha_inicio?->format('Y-m-d') : '') }}" required>
                <p class="periodo-ayuda">Corresponde al primer dia cubierto por el periodo.</p>
            </div>

            <div class="periodo-campo">
                <label for="fecha_fin">Fecha fin</label>
                <input id="fecha_fin" type="date" name="fecha_fin" value="{{ old('fecha_fin', isset($periodo) ? $periodo->fecha_fin?->format('Y-m-d') : '') }}" required>
                <p class="periodo-ayuda">Debe ser igual o posterior a la fecha de inicio.</p>
            </div>

            <div class="periodo-campo">
                <label for="fecha_pago">Fecha pago</label>
                <input id="fecha_pago" type="date" name="fecha_pago" value="{{ old('fecha_pago', isset($periodo) ? $periodo->fecha_pago?->format('Y-m-d') : '') }}" required>
                <p class="periodo-ayuda">Usa la fecha programada para dispersion o entrega del pago.</p>
            </div>

            <div class="periodo-campo periodo-campo-completo">
                <div class="periodo-resumen-fechas">
                    <div class="periodo-fecha-box">
                        <strong>Inicio</strong>
                        <span>Marca cuando comienza el rango que se va a calcular.</span>
                    </div>
                    <div class="periodo-fecha-box">
                        <strong>Fin</strong>
                        <span>Delimita el cierre del periodo para incidencias y calculos.</span>
                    </div>
                    <div class="periodo-fecha-box">
                        <strong>Pago</strong>
                        <span>Ayuda a reportes, recibos y trazabilidad del proceso.</span>
                    </div>
                </div>
                <p id="resumen_periodo_generado" class="periodo-ayuda" style="margin-top:12px;"></p>
            </div>
        </div>
    </section>

    <div class="acciones">
        <button class="boton" type="submit">Guardar periodo</button>
        <a class="boton secundario" href="{{ route('catalogos-nomina.index') }}">Cancelar</a>
    </div>
</div>

<script>
    (function () {
        const inputAnio = document.getElementById('anio_periodo');
        const inputNumeroPeriodo = document.getElementById('numero_periodo');
        const selectTipo = document.getElementById('tipo_periodo');
        const selectMes = document.getElementById('mes_referencia');
        const selectSegmento = document.getElementById('segmento_periodo');
        const ayudaTipo = document.getElementById('ayuda_tipo_periodo');
        const ayudaSegmento = document.getElementById('ayuda_segmento_periodo');
        const resumenPeriodo = document.getElementById('resumen_periodo_generado');
        const inputFechaInicio = document.getElementById('fecha_inicio');
        const inputFechaFin = document.getElementById('fecha_fin');
        const inputFechaPago = document.getElementById('fecha_pago');

        if (!inputAnio || !inputNumeroPeriodo || !selectTipo || !selectMes || !selectSegmento || !ayudaTipo || !ayudaSegmento || !resumenPeriodo || !inputFechaInicio || !inputFechaFin || !inputFechaPago) {
            return;
        }

        const ayudas = {
            SEMANAL: 'Ideal para operaciones con corte corto y revision continua de incidencias.',
            QUINCENAL: 'Adecuado para la mayoria de esquemas administrativos y de oficina.',
            MENSUAL: 'Conviene para puestos administrativos o tecnicos con pago mensual consolidado.',
        };

        const etiquetasSegmento = {
            S1: 'Primera semana',
            S2: 'Segunda semana',
            S3: 'Tercera semana',
            S4: 'Cuarta semana',
            S5: 'Quinta semana',
            Q1: 'Primera quincena',
            Q2: 'Segunda quincena',
            M1: 'Mes completo',
        };

        const formatoIso = (fecha) => {
            const anio = fecha.getFullYear();
            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
            const dia = String(fecha.getDate()).padStart(2, '0');
            return anio + '-' + mes + '-' + dia;
        };

        const formatoHumano = (fecha) => {
            return fecha.toLocaleDateString('es-MX', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
            });
        };

        const finDeMes = (anio, mesIndexadoDesdeUno) => {
            return new Date(anio, mesIndexadoDesdeUno, 0);
        };

        const numeroIsoSemana = (fecha) => {
            const copia = new Date(Date.UTC(fecha.getFullYear(), fecha.getMonth(), fecha.getDate()));
            const dia = copia.getUTCDay() || 7;
            copia.setUTCDate(copia.getUTCDate() + 4 - dia);
            const inicioAnio = new Date(Date.UTC(copia.getUTCFullYear(), 0, 1));
            return Math.ceil((((copia - inicioAnio) / 86400000) + 1) / 7);
        };

        const construirOpcionesSegmento = () => {
            const tipo = selectTipo.value;
            const anio = Number(inputAnio.value || new Date().getFullYear());
            const mes = Number(selectMes.value || 1);
            const ultimoDia = finDeMes(anio, mes).getDate();
            const opciones = [];

            if (tipo === 'SEMANAL') {
                const totalSemanas = Math.min(4, Math.ceil(ultimoDia / 7));
                for (let semana = 1; semana <= totalSemanas; semana++) {
                    opciones.push({
                        value: 'S' + semana,
                        label: etiquetasSegmento['S' + semana] || ('Semana ' + semana),
                    });
                }
            } else if (tipo === 'QUINCENAL') {
                opciones.push({ value: 'Q1', label: etiquetasSegmento.Q1 });
                opciones.push({ value: 'Q2', label: etiquetasSegmento.Q2 });
            } else {
                opciones.push({ value: 'M1', label: etiquetasSegmento.M1 });
            }

            const seleccionadoPrevio = selectSegmento.value || selectSegmento.dataset.selected || '';
            selectSegmento.innerHTML = '';

            opciones.forEach((opcion, indice) => {
                const nodo = document.createElement('option');
                nodo.value = opcion.value;
                nodo.textContent = opcion.label;
                if (opcion.value === seleccionadoPrevio || (seleccionadoPrevio === '' && indice === 0)) {
                    nodo.selected = true;
                }
                selectSegmento.appendChild(nodo);
            });

            selectSegmento.dataset.selected = selectSegmento.value;
        };

        const calcularRango = () => {
            const anio = Number(inputAnio.value || new Date().getFullYear());
            const mes = Number(selectMes.value || 1);
            const tipo = selectTipo.value;
            const segmento = selectSegmento.value;
            const ultimoDiaMes = finDeMes(anio, mes).getDate();
            let diaInicio = 1;
            let diaFin = ultimoDiaMes;

            if (tipo === 'SEMANAL') {
                const numeroSemana = Math.max(1, Number((segmento || 'S1').replace('S', '')));
                diaInicio = ((numeroSemana - 1) * 7) + 1;
                diaFin = Math.min(diaInicio + 6, ultimoDiaMes);
            } else if (tipo === 'QUINCENAL') {
                if (segmento === 'Q2') {
                    diaInicio = 16;
                    diaFin = ultimoDiaMes;
                } else {
                    diaInicio = 1;
                    diaFin = Math.min(15, ultimoDiaMes);
                }
            }

            const fechaInicio = new Date(anio, mes - 1, diaInicio);
            const fechaFin = new Date(anio, mes - 1, diaFin);
            const fechaPago = new Date(anio, mes - 1, diaFin);

            inputFechaInicio.value = formatoIso(fechaInicio);
            inputFechaFin.value = formatoIso(fechaFin);
            inputFechaPago.value = formatoIso(fechaPago);

            if (tipo === 'SEMANAL') {
                inputNumeroPeriodo.value = numeroIsoSemana(fechaInicio);
            } else if (tipo === 'QUINCENAL') {
                inputNumeroPeriodo.value = ((mes - 1) * 2) + (segmento === 'Q2' ? 2 : 1);
            } else {
                inputNumeroPeriodo.value = mes;
            }

            ayudaSegmento.textContent = 'Bloque seleccionado: ' + (etiquetasSegmento[segmento] || 'Personalizado') + '.';
            resumenPeriodo.textContent = 'Periodo sugerido: del ' + formatoHumano(fechaInicio) + ' al ' + formatoHumano(fechaFin) + '. Fecha de pago sugerida: ' + formatoHumano(fechaPago) + '.';
        };

        const actualizarAyuda = () => {
            ayudaTipo.textContent = ayudas[selectTipo.value] || ayudas.QUINCENAL;
        };

        const sincronizarPeriodo = () => {
            construirOpcionesSegmento();
            actualizarAyuda();
            calcularRango();
        };

        selectTipo.addEventListener('change', actualizarAyuda);
        selectTipo.addEventListener('change', sincronizarPeriodo);
        selectMes.addEventListener('change', sincronizarPeriodo);
        inputAnio.addEventListener('input', sincronizarPeriodo);
        selectSegmento.addEventListener('change', () => {
            selectSegmento.dataset.selected = selectSegmento.value;
            calcularRango();
        });

        sincronizarPeriodo();
    })();
</script>
