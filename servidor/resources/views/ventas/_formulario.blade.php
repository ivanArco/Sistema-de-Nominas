<div class="grilla">
    <div>
        <label>Empleado vendedor</label>
        <select name="empleado_id" required>
            <option value="">Selecciona...</option>
            @foreach($empleados as $empleado)
                <option value="{{ $empleado->id }}" @selected(old('empleado_id', $venta->empleado_id ?? '') == $empleado->id)>
                    {{ $empleado->num_empleado }} - {{ $empleado->nombre }} {{ $empleado->ap_paterno }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Folio</label>
        <input name="folio" value="{{ old('folio', $venta->folio ?? '') }}" placeholder="Opcional, se genera automatico">
    </div>
    <div>
        <label>Fecha de venta</label>
        <input type="date" name="fecha_venta" value="{{ old('fecha_venta', isset($venta) ? optional($venta->fecha_venta)->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label>Cliente</label>
        <input name="cliente_nombre" value="{{ old('cliente_nombre', $venta->cliente_nombre ?? '') }}">
    </div>
    <div>
        <label>Monto bruto</label>
        <input type="number" min="0" step="0.01" name="monto_bruto" value="{{ old('monto_bruto', $venta->monto_bruto ?? 0) }}" required>
    </div>
    <div>
        <label>% comision</label>
        <input type="number" min="0" max="100" step="0.01" name="porcentaje_comision" value="{{ old('porcentaje_comision', $venta->porcentaje_comision ?? 0) }}" required>
    </div>
    <div>
        <label>Bono desempeno</label>
        <input type="number" min="0" step="0.01" name="bono_desempeno" value="{{ old('bono_desempeno', $venta->bono_desempeno ?? 0) }}">
    </div>
    <div>
        <label>Estatus</label>
        <select name="estatus" required>
            @foreach(['REGISTRADA','CERRADA','CANCELADA'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $venta->estatus ?? 'REGISTRADA') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>
    <div style="grid-column: 1 / -1;">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="3">{{ old('observaciones', $venta->observaciones ?? '') }}</textarea>
    </div>
    <div class="acciones" style="grid-column: 1 / -1;">
        <button class="boton" type="submit">Guardar</button>
        <a class="boton secundario" href="{{ route('ventas.index') }}">Cancelar</a>
    </div>
</div>
