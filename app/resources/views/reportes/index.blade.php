@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Centro de Reportes</h2>
        <p style="margin-top:0; color:#5f6f82;">Consulta, exporta y genera reportes administrativos y de nomina.</p>

        <div class="grilla">
            <a class="boton" href="{{ route('reportes.nominas') }}">Reporte de nominas</a>
            <a class="boton" href="{{ route('reportes.empleados') }}">Reporte de empleados</a>
            <a class="boton" href="{{ route('reportes.incidencias') }}">Reporte de incidencias</a>
            <a class="boton secundario" href="{{ route('usuarios.reporte.pdf') }}">Usuarios en PDF</a>
            <a class="boton secundario" href="{{ route('clientes.reporte.pdf') }}">Clientes en PDF</a>
        </div>
    </div>
@endsection
