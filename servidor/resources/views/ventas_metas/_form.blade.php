@csrf

<div class="grilla">
    <div>
        <label>Empleado</label>
        <select name="empleado_id" required>
            <option value="">Selecciona</option>
            @foreach($empleados as $empleado)
                <option value="{{ $empleado->id }}" @selected(old('empleado_id', $meta->empleado_id ?? '') == $empleado->id)>
                    {{ $empleado->num_empleado }} - {{ $empleado->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Periodo (YYYY-MM)</label>
        <input name="periodo" value="{{ old('periodo', $meta->periodo ?? now()->format('Y-m')) }}" required>
    </div>
    <div>
        <label>Monto meta</label>
        <input type="number" step="0.01" min="0" name="monto_meta" value="{{ old('monto_meta', $meta->monto_meta ?? 0) }}" required>
    </div>
    <div>
        <label>Comision objetivo</label>
        <input type="number" step="0.01" min="0" name="comision_objetivo" value="{{ old('comision_objetivo', $meta->comision_objetivo ?? 0) }}">
    </div>
    <div>
        <label>Bono objetivo</label>
        <input type="number" step="0.01" min="0" name="bono_objetivo" value="{{ old('bono_objetivo', $meta->bono_objetivo ?? 0) }}">
    </div>
    <div>
        <label>Estatus</label>
        <select name="estatus" required>
            @foreach(['ACTIVA','CERRADA','CANCELADA'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $meta->estatus ?? 'ACTIVA') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>
    <div style="grid-column: 1 / -1;">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="3">{{ old('observaciones', $meta->observaciones ?? '') }}</textarea>
    </div>
</div>

<div class="acciones" style="margin-top: 12px;">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('ventas.metas.index') }}">Cancelar</a>
</div>
