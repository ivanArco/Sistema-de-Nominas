@csrf

<div class="grilla">
    <div>
        <label>Empleado</label>
        <select name="empleado_id" required>
            <option value="">Selecciona</option>
            @foreach($empleados as $empleado)
                <option value="{{ $empleado->id }}" @selected(old('empleado_id', $evaluacion->empleado_id ?? '') == $empleado->id)>
                    {{ $empleado->num_empleado }} - {{ $empleado->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Periodo (YYYY-MM)</label>
        <input name="periodo" value="{{ old('periodo', $evaluacion->periodo ?? now()->format('Y-m')) }}" required>
    </div>
    <div>
        <label>Fecha evaluacion</label>
        <input type="date" name="fecha_evaluacion" value="{{ old('fecha_evaluacion', optional($evaluacion->fecha_evaluacion ?? null)->format('Y-m-d')) }}" required>
    </div>
    <div>
        <label>Puntaje (0-100)</label>
        <input type="number" min="0" max="100" name="puntaje" value="{{ old('puntaje', $evaluacion->puntaje ?? 0) }}" required>
    </div>
    <div>
        <label>Resultado</label>
        <select name="resultado" required>
            @foreach(['EXCELENTE','BUENO','REGULAR','BAJO'] as $resultado)
                <option value="{{ $resultado }}" @selected(old('resultado', $evaluacion->resultado ?? 'BUENO') === $resultado)>{{ $resultado }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Estatus</label>
        <select name="estatus" required>
            @foreach(['ABIERTA','CERRADA'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $evaluacion->estatus ?? 'ABIERTA') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>
    <div style="grid-column: 1 / -1;">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="3">{{ old('observaciones', $evaluacion->observaciones ?? '') }}</textarea>
    </div>
    <div style="grid-column: 1 / -1;">
        <label>Plan de accion</label>
        <textarea name="plan_accion" rows="3">{{ old('plan_accion', $evaluacion->plan_accion ?? '') }}</textarea>
    </div>
</div>

<div class="acciones" style="margin-top: 12px;">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('evaluaciones.index') }}">Cancelar</a>
</div>
