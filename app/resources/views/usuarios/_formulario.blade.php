@php
    $esEdicion = isset($usuario);
    $rolActual = \App\Models\User::normalizarRol(old('rol', $usuario->rol ?? 'EMPLEADO'));
@endphp

<div class="grilla">
    <div>
        <label>Nombre de usuario</label>
        <input name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario ?? '') }}" required>
    </div>
    <div>
        <label>Contrasena {{ $esEdicion ? '(dejar vacia para conservar)' : '' }}</label>
        <input type="password" name="contrasena" {{ $esEdicion ? '' : 'required' }}>
    </div>
    <div>
        <label>Confirmacion de contrasena</label>
        <input type="password" name="contrasena_confirmation" {{ $esEdicion ? '' : 'required' }}>
    </div>
    <div>
        <label>Nombre</label>
        <input name="nombre" value="{{ old('nombre', $usuario->nombre ?? '') }}" required>
    </div>
    <div>
        <label>Apellido paterno</label>
        <input name="apellido_paterno" value="{{ old('apellido_paterno', $usuario->apellido_paterno ?? '') }}" required>
    </div>
    <div>
        <label>Apellido materno</label>
        <input name="apellido_materno" value="{{ old('apellido_materno', $usuario->apellido_materno ?? '') }}">
    </div>
    <div>
        <label>CURP</label>
        <input name="curp" maxlength="18" value="{{ old('curp', $usuario->curp ?? '') }}" required>
    </div>
    <div>
        <label>Correo electronico</label>
        <input type="email" name="correo_electronico" value="{{ old('correo_electronico', $usuario->email ?? '') }}" required>
    </div>
    <div>
        <label>Telefono contacto 1</label>
        <input name="telefono_contacto_1" value="{{ old('telefono_contacto_1', $usuario->telefono_contacto_1 ?? '') }}" required>
    </div>
    <div>
        <label>Telefono contacto 2</label>
        <input name="telefono_contacto_2" value="{{ old('telefono_contacto_2', $usuario->telefono_contacto_2 ?? '') }}">
    </div>
    <div>
        <label>Fecha de contratacion</label>
        <input type="date" name="fecha_contratacion" value="{{ old('fecha_contratacion', isset($usuario->fecha_contratacion) ? $usuario->fecha_contratacion->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label>Area de contratacion</label>
        <input name="area_contratacion" value="{{ old('area_contratacion', $usuario->area_contratacion ?? '') }}" required>
    </div>
    <div>
        <label>Numero de seguro social</label>
        <input name="numero_seguro_social" value="{{ old('numero_seguro_social', $usuario->numero_seguro_social ?? '') }}" required>
    </div>
    <div>
        <label>Fecha alta servicio salud</label>
        <input type="date" name="fecha_alta_servicio_salud" value="{{ old('fecha_alta_servicio_salud', isset($usuario->fecha_alta_servicio_salud) ? $usuario->fecha_alta_servicio_salud->format('Y-m-d') : '') }}">
    </div>
    <div>
        <label>Direccion</label>
        <input name="direccion" value="{{ old('direccion', $usuario->direccion ?? '') }}" required>
    </div>
    <div>
        <label>Colonia</label>
        <input name="colonia" value="{{ old('colonia', $usuario->colonia ?? '') }}" required>
    </div>
    <div>
        <label>Codigo postal</label>
        <input name="codigo_postal" value="{{ old('codigo_postal', $usuario->codigo_postal ?? '') }}" required>
    </div>
    <div>
        <label>Ciudad</label>
        <input name="ciudad" value="{{ old('ciudad', $usuario->ciudad ?? '') }}" required>
    </div>
    <div>
        <label>Estado</label>
        <input name="estado" value="{{ old('estado', $usuario->estado ?? '') }}" required>
    </div>
    <div>
        <label>Rol</label>
        <select name="rol" required>
            @foreach(\App\Models\User::rolesDisponibles() as $rol)
                <option value="{{ $rol }}" @selected($rolActual === $rol)>{{ $rol }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Activo</label>
        <select name="activo">
            <option value="1" @selected((string) old('activo', isset($usuario) ? (int) $usuario->activo : 1) === '1')>Si</option>
            <option value="0" @selected((string) old('activo', isset($usuario) ? (int) $usuario->activo : 1) === '0')>No</option>
        </select>
    </div>
</div>

<div class="acciones">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('usuarios.index') }}">Cancelar</a>
</div>
