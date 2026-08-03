<div class="grilla">
    <div>
        <label>Clave</label>
        <input name="clave" value="{{ old('clave', $concepto->clave ?? '') }}" required>
    </div>
    <div>
        <label>Nombre</label>
        <input name="nombre" value="{{ old('nombre', $concepto->nombre ?? '') }}" required>
    </div>
    <div>
        <label>Tipo</label>
        <select name="tipo" required>
            @foreach(['PERCEPCION', 'DEDUCCION', 'OTRO_PAGO'] as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo', $concepto->tipo ?? 'PERCEPCION') === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Gravado</label>
        <select name="gravado">
            <option value="1" @selected((string) old('gravado', $concepto->gravado ?? '1') === '1')>Si</option>
            <option value="0" @selected((string) old('gravado', $concepto->gravado ?? '1') === '0')>No</option>
        </select>
    </div>
    <div>
        <label>Activo</label>
        <select name="activo">
            <option value="1" @selected((string) old('activo', $concepto->activo ?? '1') === '1')>Si</option>
            <option value="0" @selected((string) old('activo', $concepto->activo ?? '1') === '0')>No</option>
        </select>
    </div>
</div>

<div class="acciones">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('catalogos-nomina.index') }}">Cancelar</a>
</div>
