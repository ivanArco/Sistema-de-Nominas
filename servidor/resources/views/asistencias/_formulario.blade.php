<div class="grilla">
    <div>
        <label>Empleado</label>
        <select name="empleado_id" required>
            <option value="">Selecciona...</option>
            @foreach($empleados as $empleado)
                <option value="{{ $empleado->id }}" @selected(old('empleado_id', $asistencia->empleado_id ?? '') == $empleado->id)>
                    {{ $empleado->num_empleado }} - {{ $empleado->nombre }} {{ $empleado->ap_paterno }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Fecha</label>
        <input type="date" name="fecha" value="{{ old('fecha', isset($asistencia) ? optional($asistencia->fecha)->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label>Estado</label>
        <select name="estado" required>
            @foreach(['ASISTENCIA','RETARDO','FALTA','PERMISO','VACACIONES'] as $estado)
                <option value="{{ $estado }}" @selected(old('estado', $asistencia->estado ?? 'ASISTENCIA') === $estado)>{{ $estado }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Horas trabajadas</label>
        <input type="number" min="0" max="24" step="0.25" name="horas_trabajadas" value="{{ old('horas_trabajadas', $asistencia->horas_trabajadas ?? 8) }}" required>
    </div>
    <div>
        <label>Origen</label>
        <input name="origen" value="{{ old('origen', $asistencia->origen ?? 'CAPTURA_MANUAL') }}">
    </div>
    <div style="grid-column: 1 / -1;">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="3">{{ old('observaciones', $asistencia->observaciones ?? '') }}</textarea>
    </div>
    <div class="acciones" style="grid-column: 1 / -1;">
        <button class="boton" type="submit">Guardar</button>
        <a class="boton secundario" href="{{ route('asistencias.index') }}">Cancelar</a>
    </div>
</div>
