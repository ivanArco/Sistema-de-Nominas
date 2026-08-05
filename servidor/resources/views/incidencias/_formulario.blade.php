@php
    $tiposIncidencia = [
        'FALTA' => 'Falta',
        'RETARDO' => 'Retardo',
        'HORA_EXTRA' => 'Hora extra',
        'BONO' => 'Bono',
        'INCAPACIDAD' => 'Incapacidad',
        'VACACIONES' => 'Vacaciones',
        'VACACIONES_PAGADAS' => 'Vacaciones pagadas',
        'DESCANSO' => 'Descanso',
        'OTRO' => 'Otro ajuste',
    ];
    $departamentoVisible = trim((string) ($alcanceDepartamento ?? ''));
@endphp

<style>
    .incidencia-formulario {
        display: grid;
        gap: 18px;
    }

    .incidencia-resumen {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid #dce5ee;
        background: linear-gradient(135deg, #f8fbff 0%, #eef4fa 100%);
    }

    .incidencia-resumen-bloque {
        display: grid;
        gap: 6px;
    }

    .incidencia-resumen-etiqueta {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6a7a8d;
    }

    .incidencia-resumen-valor {
        font-size: 20px;
        font-weight: 700;
        color: #23374d;
    }

    .incidencia-resumen-texto {
        color: #5d6d80;
        font-size: 13px;
        line-height: 1.45;
    }

    .incidencia-panel {
        border: 1px solid #dde5ee;
        border-radius: 16px;
        background: #fcfdff;
        padding: 18px;
    }

    .incidencia-panel + .incidencia-panel {
        margin-top: 2px;
    }

    .incidencia-panel-encabezado {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .incidencia-panel-titulo {
        margin: 0;
        font-size: 18px;
        color: #223549;
    }

    .incidencia-panel-subtitulo {
        margin: 4px 0 0;
        color: #677487;
        font-size: 13px;
    }

    .incidencia-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #dfeefb;
        color: #1c5788;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .incidencia-grilla {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
    }

    .incidencia-campo {
        display: grid;
        gap: 6px;
    }

    .incidencia-campo-completo {
        grid-column: 1 / -1;
    }

    .incidencia-formulario input,
    .incidencia-formulario select,
    .incidencia-formulario textarea {
        margin-top: 0;
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #ccd8e5;
        background: #fff;
        padding: 10px 12px;
        box-shadow: inset 0 1px 2px rgba(31, 41, 55, 0.03);
    }

    .incidencia-formulario textarea {
        min-height: 108px;
        resize: vertical;
    }

    .incidencia-ayuda {
        margin: 0;
        color: #677487;
        font-size: 12px;
        line-height: 1.5;
    }

    .incidencia-vacio {
        padding: 14px 16px;
        border-radius: 12px;
        background: #fff6e5;
        border: 1px solid #f2dfae;
        color: #8a6a16;
        font-size: 13px;
        line-height: 1.45;
    }
</style>

<div class="incidencia-formulario">
    <section class="incidencia-resumen">
        <div class="incidencia-resumen-bloque">
            <span class="incidencia-resumen-etiqueta">Empleados visibles</span>
            <span class="incidencia-resumen-valor">{{ $empleados->count() }}</span>
            <span class="incidencia-resumen-texto">La lista se filtra por busqueda en tiempo real para acelerar la captura.</span>
        </div>
        <div class="incidencia-resumen-bloque">
            <span class="incidencia-resumen-etiqueta">Alcance</span>
            <span class="incidencia-resumen-valor">{{ $departamentoVisible !== '' ? $departamentoVisible : 'General' }}</span>
            <span class="incidencia-resumen-texto">Supervisores y jefes de area solo ven empleados del departamento asignado.</span>
        </div>
        <div class="incidencia-resumen-bloque">
            <span class="incidencia-resumen-etiqueta">Calculo</span>
            <span class="incidencia-resumen-valor">Automatico</span>
            <span class="incidencia-resumen-texto">Si el monto es 0 en faltas o vacaciones, el sistema calcula con salario diario por cantidad.</span>
        </div>
    </section>

    <section class="incidencia-panel">
        <div class="incidencia-panel-encabezado">
            <div>
                <h3 class="incidencia-panel-titulo">Seleccion del empleado</h3>
                <p class="incidencia-panel-subtitulo">Elige primero a quien afecta la incidencia y el periodo aplicado.</p>
            </div>
            @if($departamentoVisible !== '')
                <span class="incidencia-chip">Departamento: {{ $departamentoVisible }}</span>
            @endif
        </div>

        <div class="incidencia-grilla">
            <div class="incidencia-campo incidencia-campo-completo">
                <label for="buscar_empleado_incidencia">Buscar empleado</label>
                <input id="buscar_empleado_incidencia" autocomplete="off" placeholder="Buscar por numero, nombre, CURP o RFC...">
                <p id="resultado_busqueda_empleado" class="incidencia-ayuda">Escribe para filtrar empleados disponibles.</p>
            </div>

            <div class="incidencia-campo">
                <label for="empleado_id">Empleado</label>
                <select id="empleado_id" name="empleado_id" required>
                    <option value="">Seleccione</option>
                    @foreach($empleados as $empleado)
                        @php
                            $nombreEmpleado = trim($empleado->num_empleado . ' - ' . $empleado->nombre . ' ' . $empleado->ap_paterno . ' ' . ($empleado->ap_materno ?? ''));
                            $busquedaEmpleado = implode(' ', array_filter([
                                $empleado->num_empleado,
                                $empleado->nombre,
                                $empleado->ap_paterno,
                                $empleado->ap_materno,
                                $empleado->curp,
                                $empleado->rfc,
                                optional($empleado->departamento)->nombre,
                            ]));
                            $etiquetaDepartamento = optional($empleado->departamento)->nombre;
                        @endphp
                        <option value="{{ $empleado->id }}" data-search="{{ strtoupper($busquedaEmpleado) }}" @selected(old('empleado_id', $incidencia->empleado_id ?? '') == $empleado->id)>
                            {{ $nombreEmpleado }}{{ $etiquetaDepartamento ? ' | ' . $etiquetaDepartamento : '' }}
                        </option>
                    @endforeach
                </select>
                @if($empleados->isEmpty())
                    <div class="incidencia-vacio">
                        No hay empleados disponibles para tu alcance actual. Verifica que tu usuario tenga un departamento asignado o que existan empleados activos en esa area.
                    </div>
                @endif
            </div>

            <div class="incidencia-campo">
                <label for="periodo_nomina_id">Periodo</label>
                <select id="periodo_nomina_id" name="periodo_nomina_id" required>
                    <option value="">Seleccione</option>
                    @foreach($periodos->groupBy(fn($periodo) => optional($periodo->fecha_inicio)->format('m/Y') ?? 'Sin mes') as $mes => $periodosMes)
                        <optgroup label="{{ $mes }}">
                            @foreach($periodosMes as $periodo)
                                @php($descripcion = $periodo->tipo_periodo . ' #' . $periodo->numero_periodo . ' | ' . (optional($periodo->fecha_inicio)->format('d/m/Y') ?? '-') . ' - ' . (optional($periodo->fecha_fin)->format('d/m/Y') ?? '-'))
                                <option value="{{ $periodo->id }}" @selected(old('periodo_nomina_id', $incidencia->periodo_nomina_id ?? '') == $periodo->id)>
                                    {{ $descripcion }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="incidencia-panel">
        <div class="incidencia-panel-encabezado">
            <div>
                <h3 class="incidencia-panel-titulo">Detalle de la incidencia</h3>
                <p class="incidencia-panel-subtitulo">Captura el tipo, la cantidad y el contexto del movimiento.</p>
            </div>
        </div>

        <div class="incidencia-grilla">
            <div class="incidencia-campo">
                <label for="tipo_incidencia">Tipo</label>
                <select id="tipo_incidencia" name="tipo" required>
                    @foreach($tiposIncidencia as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected(old('tipo', $incidencia->tipo ?? 'OTRO') === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <p id="ayuda_tipo_incidencia" class="incidencia-ayuda"></p>
            </div>

            <div class="incidencia-campo">
                <label for="cantidad_incidencia">Cantidad</label>
                <input id="cantidad_incidencia" type="number" step="0.01" name="cantidad" value="{{ old('cantidad', $incidencia->cantidad ?? 0) }}" required>
                <p class="incidencia-ayuda">Usa dias para faltas y vacaciones; horas para horas extra; unidades o importe para otros casos.</p>
            </div>

            <div class="incidencia-campo">
                <label for="monto_incidencia">Monto</label>
                <input id="monto_incidencia" type="number" step="0.01" name="monto" value="{{ old('monto', $incidencia->monto ?? 0) }}" required>
                <p class="incidencia-ayuda">Captura 0 para activar el calculo automatico cuando el tipo lo permita.</p>
            </div>

            <div class="incidencia-campo incidencia-campo-completo">
                <label for="descripcion_incidencia">Descripcion</label>
                <textarea id="descripcion_incidencia" name="descripcion" placeholder="Agrega contexto operativo, observaciones o referencia interna...">{{ old('descripcion', $incidencia->descripcion ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <div class="acciones">
        <button class="boton" type="submit">Guardar incidencia</button>
        <a class="boton secundario" href="{{ route('incidencias.index') }}">Cancelar</a>
    </div>
</div>

<script>
    (function () {
        const inputBuscar = document.getElementById('buscar_empleado_incidencia');
        const selectEmpleado = document.getElementById('empleado_id');
        const resultadoBusqueda = document.getElementById('resultado_busqueda_empleado');
        const selectTipo = document.getElementById('tipo_incidencia');
        const ayudaTipo = document.getElementById('ayuda_tipo_incidencia');

        if (!inputBuscar || !selectEmpleado || !resultadoBusqueda || !selectTipo || !ayudaTipo) {
            return;
        }

        const ayudasPorTipo = {
            FALTA: 'Si el monto se deja en 0, se calculara por salario diario multiplicado por la cantidad de dias.',
            RETARDO: 'Puedes capturar una deduccion fija o usar la cantidad como referencia operativa.',
            HORA_EXTRA: 'Registra el numero de horas y el importe extraordinario correspondiente.',
            BONO: 'Usa este tipo para percepciones adicionales ligadas al periodo.',
            INCAPACIDAD: 'Permite documentar descuentos o ajustes derivados de incapacidad.',
            VACACIONES: 'Si el monto es 0, el sistema calculara el ajuste usando salario diario por cantidad.',
            VACACIONES_PAGADAS: 'Si el monto es 0, el sistema calculara automaticamente el pago de vacaciones.',
            DESCANSO: 'El descanso se registra para control y normalmente no genera importe.',
            OTRO: 'Utiliza una descripcion clara para explicar el motivo del ajuste.',
        };

        const opciones = Array.from(selectEmpleado.options).map((opcion) => ({
            value: opcion.value,
            text: opcion.textContent || '',
            search: (opcion.dataset.search || opcion.textContent || '').toUpperCase(),
        }));

        const normalizar = (texto) => {
            return (texto || '')
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toUpperCase()
                .trim();
        };

        const renderOpciones = () => {
            const termino = normalizar(inputBuscar.value);
            const seleccionadoActual = selectEmpleado.value;
            let coincidencias = 0;
            let primeraCoincidencia = '';

            selectEmpleado.innerHTML = '';

            const opcionVacia = document.createElement('option');
            opcionVacia.value = '';
            opcionVacia.textContent = 'Seleccione';
            selectEmpleado.appendChild(opcionVacia);

            opciones.forEach((opcion) => {
                if (opcion.value === '') {
                    return;
                }

                if (termino !== '' && !normalizar(opcion.search).includes(termino)) {
                    return;
                }

                coincidencias++;
                if (primeraCoincidencia === '') {
                    primeraCoincidencia = opcion.value;
                }

                const nodo = document.createElement('option');
                nodo.value = opcion.value;
                nodo.textContent = opcion.text;
                if (opcion.value === seleccionadoActual) {
                    nodo.selected = true;
                }
                selectEmpleado.appendChild(nodo);
            });

            if (coincidencias > 0 && selectEmpleado.value === '') {
                selectEmpleado.value = primeraCoincidencia;
            }

            resultadoBusqueda.textContent = coincidencias > 0
                ? 'Coincidencias: ' + coincidencias
                : 'Sin coincidencias para la busqueda.';
        };

        const actualizarAyudaTipo = () => {
            ayudaTipo.textContent = ayudasPorTipo[selectTipo.value] || ayudasPorTipo.OTRO;
        };

        inputBuscar.addEventListener('input', renderOpciones);
        selectTipo.addEventListener('change', actualizarAyudaTipo);
        renderOpciones();
        actualizarAyudaTipo();
    })();
</script>
