<div class="grilla">
    <div>
        <label>Buscar empleado</label>
        <input id="buscar_empleado_incidencia" placeholder="Buscar por numero o nombre...">
        <small id="resultado_busqueda_empleado" style="display:block; margin-top:6px; color:#677487;">Escribe para filtrar empleados.</small>
    </div>
    <div>
        <label>Empleado</label>
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
                    ]));
                @endphp
                <option value="{{ $empleado->id }}" data-search="{{ strtoupper($busquedaEmpleado) }}" @selected(old('empleado_id', $incidencia->empleado_id ?? '') == $empleado->id)>
                    {{ $nombreEmpleado }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Periodo</label>
        <select name="periodo_nomina_id" required>
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
    <div>
        <label>Tipo</label>
        <select name="tipo" required>
            @foreach(['FALTA', 'RETARDO', 'HORA_EXTRA', 'BONO', 'INCAPACIDAD', 'VACACIONES', 'VACACIONES_PAGADAS', 'DESCANSO', 'OTRO'] as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo', $incidencia->tipo ?? 'OTRO') === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Cantidad (dias u horas)</label>
        <input type="number" step="0.01" name="cantidad" value="{{ old('cantidad', $incidencia->cantidad ?? 0) }}" required>
    </div>
    <div>
        <label>Monto</label>
        <input type="number" step="0.01" name="monto" value="{{ old('monto', $incidencia->monto ?? 0) }}" required>
        <small style="display:block; margin-top:6px; color:#677487;">
            Si capturas 0 en FALTA, VACACIONES o VACACIONES_PAGADAS, el sistema calcula automaticamente por salario diario x cantidad.
        </small>
    </div>
    <div>
        <label>Descripcion</label>
        <input name="descripcion" value="{{ old('descripcion', $incidencia->descripcion ?? '') }}">
    </div>
</div>

<div class="acciones">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('incidencias.index') }}">Cancelar</a>
</div>

<script>
    (function () {
        const inputBuscar = document.getElementById('buscar_empleado_incidencia');
        const selectEmpleado = document.getElementById('empleado_id');
        const resultadoBusqueda = document.getElementById('resultado_busqueda_empleado');

        if (!inputBuscar || !selectEmpleado || !resultadoBusqueda) {
            return;
        }

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

        inputBuscar.addEventListener('input', renderOpciones);
        renderOpciones();
    })();
</script>
