@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Mi cuenta</h2>
        <div class="grilla">
            <div>
                <label>Usuario</label>
                <input value="{{ $usuario->nombre_usuario ?? '-' }}" disabled>
            </div>
            <div>
                <label>Rol</label>
                <input value="{{ $usuario->rolNormalizado() }}" disabled>
            </div>
            <div>
                <label>Nombre</label>
                <input value="{{ trim(($usuario->nombre ?? '').' '.($usuario->apellido_paterno ?? '').' '.($usuario->apellido_materno ?? '')) }}" disabled>
            </div>
            <div>
                <label>Correo</label>
                <input value="{{ $usuario->email ?? '-' }}" disabled>
            </div>
            <div>
                <label>CURP</label>
                <input value="{{ $usuario->curp ?? '-' }}" disabled>
            </div>
            <div>
                <label>NSS</label>
                <input value="{{ $usuario->numero_seguro_social ?? '-' }}" disabled>
            </div>
            <div>
                <label>Vacaciones disponibles (dias)</label>
                <input value="{{ number_format((float) ($vacacionesDisponibles ?? 0), 2) }}" disabled>
            </div>
        </div>

        <div class="acciones" style="margin-top: 12px;">
            <a class="boton" href="{{ route('mi-cuenta.edit') }}">Editar mis datos</a>
            <a class="boton" href="{{ route('mi-cuenta.recibos') }}">Mis recibos</a>
            <a class="boton secundario" href="{{ route('mi-cuenta.password.form') }}">Cambiar contrasena</a>
        </div>
    </div>

    @if($empleado)
        <div class="tarjeta" style="overflow-x:auto;">
            <h3 style="margin-top:0;">Ultimas nominas</h3>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Estatus</th>
                        <th>Neto</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasNominas as $nomina)
                        <tr>
                            <td>{{ $nomina->periodo->anio ?? '-' }}/{{ $nomina->periodo->numero_periodo ?? '-' }}</td>
                            <td>{{ $nomina->estatus }}</td>
                            <td>${{ number_format((float) $nomina->neto_pagado, 2) }}</td>
                            <td><a class="boton secundario" href="{{ route('mi-cuenta.recibo.pdf', $nomina->id) }}">PDF</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sin nominas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="tarjeta" style="overflow-x:auto;">
            <h3 style="margin-top:0;">Historial laboral reciente</h3>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Movimiento</th>
                        <th>Puesto</th>
                        <th>Salario diario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historialLaboral as $movimiento)
                        <tr>
                            <td>{{ optional($movimiento->fecha_movimiento)->format('d/m/Y') }}</td>
                            <td>{{ $movimiento->tipo_movimiento }}</td>
                            <td>{{ $movimiento->puesto->nombre ?? '-' }}</td>
                            <td>${{ number_format((float) $movimiento->salario_diario, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sin movimientos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
