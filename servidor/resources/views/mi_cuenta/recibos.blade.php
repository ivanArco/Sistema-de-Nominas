@extends('layouts.app')

@section('contenido')
    <div class="tarjeta" style="overflow-x:auto;">
        <h2>Mis recibos de nomina</h2>
        <p style="margin-top:0; color:#5f6f82;">Empleado: {{ $empleado->num_empleado }} - {{ $empleado->nombre }} {{ $empleado->ap_paterno }}</p>

        <table class="tabla">
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Dias pagados</th>
                    <th>Percepciones</th>
                    <th>Deducciones</th>
                    <th>Neto</th>
                    <th>Estatus</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse($nominas as $nomina)
                    <tr>
                        <td>{{ $nomina->periodo->anio ?? '-' }}/{{ $nomina->periodo->numero_periodo ?? '-' }}</td>
                        <td>{{ number_format((float) $nomina->dias_pagados, 2) }}</td>
                        <td>${{ number_format((float) $nomina->total_percepciones, 2) }}</td>
                        <td>${{ number_format((float) $nomina->total_deducciones, 2) }}</td>
                        <td>${{ number_format((float) $nomina->neto_pagado, 2) }}</td>
                        <td>{{ $nomina->estatus }}</td>
                        <td><a class="boton secundario" href="{{ route('mi-cuenta.recibo.pdf', $nomina->id) }}">Descargar PDF</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">No hay recibos disponibles.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $nominas->links() }}</div>
    </div>
@endsection
