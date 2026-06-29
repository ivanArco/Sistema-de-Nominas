<div class="grilla">
    <div>
        <label>Nombre comercial</label>
        <input name="nombre_comercial" value="{{ old('nombre_comercial', $cliente->nombre_comercial ?? '') }}" required>
    </div>
    <div>
        <label>Razon social</label>
        <input name="razon_social" value="{{ old('razon_social', $cliente->razon_social ?? '') }}">
    </div>
    <div>
        <label>RFC</label>
        <input name="rfc" value="{{ old('rfc', $cliente->rfc ?? '') }}">
    </div>
    <div>
        <label>Nombre de contacto</label>
        <input name="nombre_contacto" value="{{ old('nombre_contacto', $cliente->nombre_contacto ?? '') }}" required>
    </div>
    <div>
        <label>Correo electronico</label>
        <input type="email" name="correo_electronico" value="{{ old('correo_electronico', $cliente->correo_electronico ?? '') }}">
    </div>
    <div>
        <label>Telefono contacto 1</label>
        <input name="telefono_contacto_1" value="{{ old('telefono_contacto_1', $cliente->telefono_contacto_1 ?? '') }}" required>
    </div>
    <div>
        <label>Telefono contacto 2</label>
        <input name="telefono_contacto_2" value="{{ old('telefono_contacto_2', $cliente->telefono_contacto_2 ?? '') }}">
    </div>
    <div>
        <label>Direccion</label>
        <input name="direccion" value="{{ old('direccion', $cliente->direccion ?? '') }}">
    </div>
    <div>
        <label>Colonia</label>
        <input name="colonia" value="{{ old('colonia', $cliente->colonia ?? '') }}">
    </div>
    <div>
        <label>Codigo postal</label>
        <input name="codigo_postal" value="{{ old('codigo_postal', $cliente->codigo_postal ?? '') }}">
    </div>
    <div>
        <label>Ciudad</label>
        <input name="ciudad" value="{{ old('ciudad', $cliente->ciudad ?? '') }}">
    </div>
    <div>
        <label>Estado</label>
        <input name="estado" value="{{ old('estado', $cliente->estado ?? '') }}">
    </div>
    <div>
        <label>Estatus</label>
        <select name="estatus" required>
            @foreach(['ACTIVO', 'INACTIVO'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $cliente->estatus ?? 'ACTIVO') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="acciones">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('clientes.index') }}">Cancelar</a>
</div>
