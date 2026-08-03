@php
    /** @var \App\Models\Rol $rol */
    /** @var \Illuminate\Support\Collection|\App\Models\Permiso[] $permisos */
    $seleccionados = old('permisos', $seleccionados ?? []);
@endphp

<div class="mb-4">
    <label class="form-label" for="clave">Clave</label>
    <input id="clave" name="clave" type="text" class="form-control @error('clave') is-invalid @enderror"
           value="{{ old('clave', $rol->clave) }}" required maxlength="60" placeholder="EJEMPLO: JEFE_AREA">
    @error('clave')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label" for="nombre">Nombre</label>
    <input id="nombre" name="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $rol->nombre) }}" required maxlength="120" placeholder="Nombre visible del rol">
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-4">
    <label class="form-label d-block">Permisos asignados</label>
    <div class="card">
        <div class="card-body" style="max-height: 360px; overflow: auto;">
            <div class="row g-2">
                @forelse($permisos as $permiso)
                    <div class="col-md-6">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                   {{ in_array($permiso->id, $seleccionados, true) ? 'checked' : '' }}>
                            <span class="form-check-label">
                                <strong>{{ $permiso->clave }}</strong>
                                <small class="text-muted d-block">{{ $permiso->nombre }}</small>
                            </span>
                        </label>
                    </div>
                @empty
                    <div class="col-12 text-muted">No hay permisos disponibles.</div>
                @endforelse
            </div>
        </div>
    </div>
    @error('permisos')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
