<div class="grilla">
    <div>
        <label>Anio</label>
        <input type="number" name="anio" min="2020" max="2100" value="{{ old('anio', $periodo->anio ?? now()->year) }}" required>
    </div>
    <div>
        <label>Numero de periodo</label>
        <input type="number" name="numero_periodo" min="1" max="60" value="{{ old('numero_periodo', $periodo->numero_periodo ?? 1) }}" required>
    </div>
    <div>
        <label>Tipo de periodo</label>
        <select name="tipo_periodo" required>
            @foreach(['SEMANAL', 'QUINCENAL', 'MENSUAL'] as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo_periodo', $periodo->tipo_periodo ?? 'QUINCENAL') === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Fecha inicio</label>
        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', isset($periodo) ? $periodo->fecha_inicio?->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label>Fecha fin</label>
        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', isset($periodo) ? $periodo->fecha_fin?->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label>Fecha pago</label>
        <input type="date" name="fecha_pago" value="{{ old('fecha_pago', isset($periodo) ? $periodo->fecha_pago?->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label>Estatus</label>
        <select name="estatus" required>
            @foreach(['ABIERTO', 'CALCULADO', 'CERRADO', 'TIMBRADO'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $periodo->estatus ?? 'ABIERTO') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="acciones">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('catalogos-nomina.index') }}">Cancelar</a>
</div>
