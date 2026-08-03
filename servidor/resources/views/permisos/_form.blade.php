@php
    /** @var \App\Models\Permiso $permiso */
@endphp

<div class="mb-4">
    <label class="form-label" for="clave">Clave</label>
    <input id="clave" name="clave" type="text" class="form-control @error('clave') is-invalid @enderror"
           value="{{ old('clave', $permiso->clave) }}" required maxlength="120" placeholder="ejemplo.modulo.accion">
    @error('clave')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="nombre">Nombre</label>
    <input id="nombre" name="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $permiso->nombre) }}" required maxlength="150" placeholder="Descripcion del permiso">
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
