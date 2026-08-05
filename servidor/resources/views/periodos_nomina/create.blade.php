@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0;">Nuevo periodo de nomina</h2>
                <p style="margin:8px 0 0;color:#677487;max-width:760px;line-height:1.55;">
                    Configura el periodo de pago con fechas, tipo y estatus inicial. La vista agrupa la captura para que sea mas facil revisar datos antes de guardar.
                </p>
            </div>
            <div class="acciones" style="margin-top:0;">
                <a class="boton secundario" href="{{ route('periodos-nomina.index') }}">Ver periodos</a>
                <a class="boton secundario" href="{{ route('catalogos-nomina.index') }}">Volver a catalogos</a>
            </div>
        </div>

        <form method="POST" action="{{ route('periodos-nomina.store') }}">
            @csrf
            @include('periodos_nomina._formulario')
        </form>
    </div>
@endsection
