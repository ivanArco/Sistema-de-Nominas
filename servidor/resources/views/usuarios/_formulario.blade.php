@php
    $esEdicion = isset($usuario);
    $rolActual = \App\Models\User::normalizarRol(old('rol', $usuario->rol ?? 'EMPLEADO'));
    $rolesConEmpleado = $rolesConEmpleado ?? ['EMPLEADO', 'VENDEDOR', 'CONTADOR', 'SECRETARIA'];
    $claseBloqueEmpleado = in_array($rolActual, $rolesConEmpleado, true) ? '' : 'bloque-empleado-oculto';
@endphp

<style>
    .bloque-empleado-oculto {
        display: none;
    }

    .usuarios-form-grid {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(220px, 1fr));
    }

    .usuarios-form-grid > div {
        background: #fcfdff;
        border: 1px solid #e3eaf2;
        border-radius: 12px;
        padding: 10px;
    }

    .usuarios-banner {
        margin: 0 0 12px;
        border: 1px solid #b8d8bf;
        border-radius: 12px;
        background: #effbf1;
        color: #214a2b;
        padding: 10px 12px;
        font-size: 13px;
    }

    @media (max-width: 760px) {
        .usuarios-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@if(!$esEdicion)
    <p class="usuarios-banner">
        Contrasena inicial automatica: se asignara la CURP capturada para este usuario.
    </p>
@endif

<div class="usuarios-form-grid">
    <div>
        <label>Nombre de usuario</label>
        <input name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario ?? '') }}" required>
    </div>
    @if($esEdicion)
        <div>
            <label>Contrasena (dejar vacia para conservar)</label>
            <input type="password" name="contrasena">
        </div>
        <div>
            <label>Confirmacion de contrasena</label>
            <input type="password" name="contrasena_confirmation">
        </div>
    @endif
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
        <select name="rol" id="rol_select" required>
            @foreach(\App\Models\User::rolesDisponibles() as $rol)
                <option value="{{ $rol }}" data-requiere-empleado="{{ in_array($rol, $rolesConEmpleado, true) ? '1' : '0' }}" @selected($rolActual === $rol)>
                    {{ $rol }}
                </option>
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

<div class="tarjeta {{ $claseBloqueEmpleado }}" id="bloque_empleado_vinculado" style="margin-top: 14px;">
    <h3 style="margin-top: 0;">Empleado vinculado</h3>
    <p style="margin: 0 0 12px; font-size: 13px; color: #607488;">
        Este bloque se requiere para roles operativos con nomina.
    </p>

    <div class="grilla">
        <div>
            <label>Numero de empleado</label>
            <input name="num_empleado" value="{{ old('num_empleado', $empleadoVinculado->num_empleado ?? '') }}">
        </div>
        <div>
            <label>RFC empleado</label>
            <input name="rfc_empleado" maxlength="13" value="{{ old('rfc_empleado', $empleadoVinculado->rfc ?? '') }}">
        </div>
        <div>
            <label>Departamento</label>
            <select name="depto_id_empleado">
                <option value="">Seleccione</option>
                @foreach(($departamentos ?? []) as $departamento)
                    <option value="{{ $departamento->id }}" @selected((string) old('depto_id_empleado', $empleadoVinculado->depto_id ?? '') === (string) $departamento->id)>
                        {{ $departamento->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Puesto</label>
            <select name="puesto_id_empleado">
                <option value="">Seleccione</option>
                @foreach(($puestos ?? []) as $puesto)
                    <option value="{{ $puesto->id }}" @selected((string) old('puesto_id_empleado', $empleadoVinculado->puesto_id ?? '') === (string) $puesto->id)>
                        {{ $puesto->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Tipo contrato</label>
            <input name="tipo_cont_empleado" value="{{ old('tipo_cont_empleado', $empleadoVinculado->tipo_cont ?? 'INDEFINIDO') }}">
        </div>
        <div>
            <label>Jornada</label>
            <input name="jornada_empleado" value="{{ old('jornada_empleado', $empleadoVinculado->jornada ?? 'COMPLETA') }}">
        </div>
        <div>
            <label>Tipo de pago</label>
            <select name="tipo_pago_empleado">
                @foreach(['SEMANAL', 'QUINCENAL', 'MENSUAL'] as $tipoPago)
                    <option value="{{ $tipoPago }}" @selected(old('tipo_pago_empleado', $empleadoVinculado->tipo_pago ?? 'QUINCENAL') === $tipoPago)>
                        {{ $tipoPago }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Salario diario</label>
            <input type="number" step="0.01" name="sal_dia_empleado" value="{{ old('sal_dia_empleado', $empleadoVinculado->sal_dia ?? 0) }}">
        </div>
        <div>
            <label>Salario integrado</label>
            <input type="number" step="0.01" name="sal_int_empleado" value="{{ old('sal_int_empleado', $empleadoVinculado->sal_int ?? 0) }}" readonly>
            <small style="display:block; margin-top:4px; color:#6b7280;">Se calcula automaticamente con salario diario y antiguedad.</small>
        </div>
        <div>
            <label>% INFONAVIT</label>
            <input type="number" step="0.001" name="porcentaje_infonavit_empleado" value="{{ old('porcentaje_infonavit_empleado', $empleadoVinculado->porcentaje_infonavit ?? 0) }}">
        </div>
        <div>
            <label>% AFORE</label>
            <input type="number" step="0.001" name="porcentaje_afore_empleado" value="{{ old('porcentaje_afore_empleado', $empleadoVinculado->porcentaje_afore ?? 1.125) }}">
        </div>
        <div>
            <label>Usa fondo ahorro</label>
            <select name="usa_fondo_ahorro_empleado">
                <option value="0" @selected((string) old('usa_fondo_ahorro_empleado', isset($empleadoVinculado) ? (int) $empleadoVinculado->usa_fondo_ahorro : 0) === '0')>No</option>
                <option value="1" @selected((string) old('usa_fondo_ahorro_empleado', isset($empleadoVinculado) ? (int) $empleadoVinculado->usa_fondo_ahorro : 0) === '1')>Si</option>
            </select>
        </div>
        <div>
            <label>% Fondo ahorro</label>
            <input type="number" step="0.001" name="porcentaje_fondo_ahorro_empleado" value="{{ old('porcentaje_fondo_ahorro_empleado', $empleadoVinculado->porcentaje_fondo_ahorro ?? 0) }}">
        </div>
        <div>
            <label>Semanas cotizadas</label>
            <input type="number" step="0.01" name="semanas_cotizadas_empleado" value="{{ old('semanas_cotizadas_empleado', $empleadoVinculado->semanas_cotizadas ?? 0) }}">
        </div>
        <div>
            <label>Fondo retiro acumulado</label>
            <input type="number" step="0.01" name="fondo_retiro_acumulado_empleado" value="{{ old('fondo_retiro_acumulado_empleado', $empleadoVinculado->fondo_retiro_acumulado ?? 0) }}">
        </div>
    </div>
</div>

<div class="acciones">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('usuarios.index') }}">Cancelar</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rolSelect = document.getElementById('rol_select');
    const bloqueEmpleado = document.getElementById('bloque_empleado_vinculado');

    if (!rolSelect || !bloqueEmpleado) {
        return;
    }

    function actualizarVisibilidad() {
        const opcionSeleccionada = rolSelect.options[rolSelect.selectedIndex];
        const requiereEmpleado = opcionSeleccionada?.dataset.requiereEmpleado === '1';
        bloqueEmpleado.style.display = requiereEmpleado ? '' : 'none';

        bloqueEmpleado.querySelectorAll('input, select').forEach((campo) => {
            campo.disabled = !requiereEmpleado;
        });
    }

    rolSelect.addEventListener('change', actualizarVisibilidad);
    actualizarVisibilidad();
});
</script>