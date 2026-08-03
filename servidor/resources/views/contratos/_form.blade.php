@csrf

<div class="grilla">
    <div>
        <label>Empleado</label>
        <select name="empleado_id" required>
            <option value="">Selecciona</option>
            @foreach($empleados as $empleado)
                <option value="{{ $empleado->id }}" @selected(old('empleado_id', $contrato->empleado_id ?? '') == $empleado->id)>
                    {{ $empleado->num_empleado }} - {{ $empleado->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Tipo</label>
        <select name="tipo" required>
            @foreach(['INDEFINIDO','TEMPORAL','PRACTICAS','OUTSOURCING'] as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo', $contrato->tipo ?? 'INDEFINIDO') === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Fecha inicio</label>
        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', optional($contrato->fecha_inicio ?? null)->format('Y-m-d')) }}" required>
    </div>
    <div>
        <label>Fecha fin</label>
        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', optional($contrato->fecha_fin ?? null)->format('Y-m-d')) }}">
    </div>
    <div>
        <label>Sueldo mensual</label>
        <input type="number" step="0.01" min="0" name="sueldo_mensual" value="{{ old('sueldo_mensual', $contrato->sueldo_mensual ?? 0) }}" required>
    </div>
    <div>
        <label>Jornada</label>
        <select name="jornada" required>
            @foreach(['COMPLETA','MEDIO_TIEMPO','NOCTURNA','MIXTA'] as $jornada)
                <option value="{{ $jornada }}" @selected(old('jornada', $contrato->jornada ?? 'COMPLETA') === $jornada)>{{ $jornada }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Estatus</label>
        <select name="estatus" required>
            @foreach(['ACTIVO','VENCIDO','CANCELADO'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $contrato->estatus ?? 'ACTIVO') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>
    <div style="grid-column: 1 / -1;">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="3">{{ old('observaciones', $contrato->observaciones ?? '') }}</textarea>
    </div>
</div>

<div class="acciones" style="margin-top: 12px;">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('contratos.index') }}">Cancelar</a>
</div>
