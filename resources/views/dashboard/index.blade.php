@extends('layouts.app')

@section('contenido')
    <div class="tarjeta" style="background: linear-gradient(120deg, #0a315f 0%, #124a89 56%, #2d77b8 100%); color:white; border:0;">
        <h1 style="margin-top:0; font-size:28px;">Inicio</h1>
        <p style="max-width: 760px; margin-bottom:0; opacity:0.9; line-height:1.7;">
            Resumen de operación y acceso rápido a las funciones principales del sistema. Aquí encontrarás la información clave para comenzar.
        </p>
    </div>

    <div class="grilla" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 18px;">
        <div class="tarjeta">
            <small>Usuarios activos</small>
            <h2 style="margin:4px 0 0 0;">{{ number_format($kpis['usuarios_activos']) }}</h2>
        </div>
        <div class="tarjeta">
            <small>Clientes activos</small>
            <h2 style="margin:4px 0 0 0;">{{ number_format($kpis['clientes_activos']) }}</h2>
        </div>
        <div class="tarjeta">
            <small>Empleados activos</small>
            <h2 style="margin:4px 0 0 0;">{{ number_format($kpis['empleados_activos']) }}</h2>
        </div>
        <div class="tarjeta">
            <small>Incidencias del mes</small>
            <h2 style="margin:4px 0 0 0;">{{ number_format($kpis['incidencias_mes']) }}</h2>
        </div>
        <div class="tarjeta">
            <small>Nominas del mes</small>
            <h2 style="margin:4px 0 0 0;">{{ number_format($kpis['nominas_mes']) }}</h2>
        </div>
        <div class="tarjeta">
            <small>Neto pagado del mes (MXN)</small>
            <h2 style="margin:4px 0 0 0;">${{ number_format($kpis['neto_pagado_mes'], 2) }}</h2>
        </div>
    </div>

    <div class="grilla" style="grid-template-columns: 1.2fr 1fr; align-items:start;">
        <div class="tarjeta" style="overflow-x:auto;">
            <h3 style="margin-top:0;">Nominas recientes</h3>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Empleado ID</th>
                        <th>Neto pagado</th>
                        <th>Estatus</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nominasRecientes as $nomina)
                        <tr>
                            <td>#{{ $nomina->id }}</td>
                            <td>{{ $nomina->empleado_id }}</td>
                            <td>${{ number_format((float) $nomina->neto_pagado, 2) }}</td>
                            <td>{{ $nomina->estatus }}</td>
                            <td>{{ $nomina->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Sin movimientos de nomina registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="tarjeta">
            <h3 style="margin-top:0;">Incidencias por tipo</h3>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidenciasPorTipo as $fila)
                        <tr>
                            <td>{{ $fila->tipo }}</td>
                            <td>{{ $fila->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Sin incidencias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h3 style="margin-top:16px;">Distribucion de empleados</h3>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Estatus</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empleadosPorEstatus as $fila)
                        <tr>
                            <td>{{ $fila->estatus }}</td>
                            <td>{{ $fila->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">Sin empleados registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="acciones" style="margin-top:14px;">
                <a class="boton" href="{{ route('usuarios.index') }}">Gestionar usuarios</a>
                <a class="boton secundario" href="{{ route('clientes.index') }}">Gestionar clientes</a>
                <a class="boton secundario" href="{{ route('nominas.index') }}">Ver nominas</a>
            </div>
        </div>
    </div>
@endsection
