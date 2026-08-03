@php
    $esSecretariaEditando = auth()->check()
        && auth()->user()?->rolNormalizado() === 'SECRETARIA'
        && isset($empleado);
@endphp

<div class="grilla">
    <div>
        <label>Numero de empleado</label>
        <input name="num_empleado" value="{{ old('num_empleado', $empleado->num_empleado ?? '') }}" required>
    </div>
    <div>
        <label>Nombre</label>
        <input name="nombre" value="{{ old('nombre', $empleado->nombre ?? '') }}" required>
    </div>
    <div>
        <label>Apellido paterno</label>
        <input name="ap_paterno" value="{{ old('ap_paterno', $empleado->ap_paterno ?? '') }}" required>
    </div>
    <div>
        <label>Apellido materno</label>
        <input name="ap_materno" value="{{ old('ap_materno', $empleado->ap_materno ?? '') }}">
    </div>
    <div>
        <label>CURP</label>
        <input name="curp" value="{{ old('curp', $empleado->curp ?? '') }}" maxlength="18" required>
    </div>
    <div>
        <label>RFC</label>
        <input name="rfc" value="{{ old('rfc', $empleado->rfc ?? '') }}" maxlength="13" required>
    </div>
    <div>
        <label>NSS</label>
        <input name="nss" value="{{ old('nss', $empleado->nss ?? '') }}" required>
    </div>
    <div>
        <label>Correo</label>
        <input type="email" name="correo" value="{{ old('correo', $empleado->correo ?? '') }}">
    </div>
    <div>
        <label>Telefono</label>
        <input name="telefono" value="{{ old('telefono', $empleado->telefono ?? '') }}">
    </div>
    <div>
        <label>Fecha ingreso</label>
        <input type="date" name="f_ingreso" value="{{ old('f_ingreso', isset($empleado) ? $empleado->f_ingreso?->format('Y-m-d') : '') }}" required>
    </div>
    <div>
        <label>Fecha baja</label>
        <input type="date" name="f_baja" value="{{ old('f_baja', isset($empleado) ? $empleado->f_baja?->format('Y-m-d') : '') }}">
    </div>
    <div>
        <label>Tipo contrato</label>
        <input name="tipo_cont" value="{{ old('tipo_cont', $empleado->tipo_cont ?? '') }}" required>
    </div>
    <div>
        <label>Jornada</label>
        <input name="jornada" value="{{ old('jornada', $empleado->jornada ?? '') }}" required>
    </div>
    <div>
        <label>Tipo de pago</label>
        <select name="tipo_pago" required>
            @foreach(['SEMANAL', 'QUINCENAL', 'MENSUAL'] as $tipoPago)
                <option value="{{ $tipoPago }}" @selected(old('tipo_pago', $empleado->tipo_pago ?? 'QUINCENAL') === $tipoPago)>{{ $tipoPago }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Salario diario</label>
        <input type="number" step="0.01" name="sal_dia" value="{{ old('sal_dia', $empleado->sal_dia ?? '') }}" required {{ $esSecretariaEditando ? 'readonly' : '' }}>
    </div>
    <div>
        <label>Salario integrado</label>
        <input type="number" step="0.01" name="sal_int" value="{{ old('sal_int', $empleado->sal_int ?? '') }}" required {{ $esSecretariaEditando ? 'readonly' : '' }}>
    </div>
    <div>
        <label>Departamento</label>
        <select name="depto_id" required>
            <option value="">Seleccione</option>
            @foreach($departamentos as $departamento)
                <option value="{{ $departamento->id }}" @selected(old('depto_id', $empleado->depto_id ?? '') == $departamento->id)>
                    {{ $departamento->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Puesto</label>
        <select name="puesto_id" required>
            <option value="">Seleccione</option>
            @foreach($puestos as $puesto)
                <option value="{{ $puesto->id }}" @selected(old('puesto_id', $empleado->puesto_id ?? '') == $puesto->id)>
                    {{ $puesto->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label>% INFONAVIT</label>
        <input type="number" step="0.001" name="porcentaje_infonavit" value="{{ old('porcentaje_infonavit', $empleado->porcentaje_infonavit ?? 0) }}">
    </div>
    <div>
        <label>% AFORE</label>
        <input type="number" step="0.001" name="porcentaje_afore" value="{{ old('porcentaje_afore', $empleado->porcentaje_afore ?? 1.125) }}">
    </div>
    <div>
        <label>Usa fondo ahorro</label>
        <select name="usa_fondo_ahorro">
            <option value="0" @selected((string) old('usa_fondo_ahorro', $empleado->usa_fondo_ahorro ?? '0') === '0')>No</option>
            <option value="1" @selected((string) old('usa_fondo_ahorro', $empleado->usa_fondo_ahorro ?? '0') === '1')>Si</option>
        </select>
    </div>
    <div>
        <label>% Fondo ahorro</label>
        <input type="number" step="0.001" name="porcentaje_fondo_ahorro" value="{{ old('porcentaje_fondo_ahorro', $empleado->porcentaje_fondo_ahorro ?? 0) }}">
    </div>
    <div>
        <label>Semanas cotizadas</label>
        <input type="number" step="0.01" name="semanas_cotizadas" value="{{ old('semanas_cotizadas', $empleado->semanas_cotizadas ?? 0) }}">
    </div>
    <div>
        <label>Fondo de retiro acumulado</label>
        <input type="number" step="0.01" name="fondo_retiro_acumulado" value="{{ old('fondo_retiro_acumulado', $empleado->fondo_retiro_acumulado ?? 0) }}">
    </div>
    <div>
        <label>Estatus</label>
        <select name="estatus" required>
            @foreach(['ACTIVO', 'BAJA'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $empleado->estatus ?? 'ACTIVO') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>
</div>

@if($esSecretariaEditando)
    <div class="mensaje error" style="margin-top: 10px;">
        Perfil SECRETARIA: puedes actualizar informacion administrativa del empleado, pero no modificar salarios.
    </div>
@endif

@if(!isset($empleado))
    <div class="tarjeta" style="margin-top: 14px;">
        <h3 style="margin-top: 0;">Acceso al sistema</h3>
        <p style="margin: 0 0 12px; color: #607488; font-size: 13px;">
            Al guardar el empleado, el sistema crea automaticamente su cuenta de acceso y genera contrasena temporal.
        </p>

        <div class="grilla">
            <div>
                <label>Rol del usuario</label>
                <select name="usuario_rol" required>
                    @foreach(\App\Models\User::rolesDisponibles() as $rol)
                        <option value="{{ $rol }}" @selected(old('usuario_rol', 'EMPLEADO') === $rol)>{{ $rol }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@endif

<div class="acciones">
    <button class="boton" type="submit">Guardar</button>
    <a class="boton secundario" href="{{ route('empleados.index') }}">Cancelar</a>
</div>
