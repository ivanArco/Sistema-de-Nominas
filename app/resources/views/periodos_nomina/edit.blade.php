@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar periodo de nomina</h2>

        <form method="POST" action="{{ route('periodos-nomina.update', $periodo->id) }}">
            @csrf
            @method('PUT')
            @include('periodos_nomina._formulario')
        </form>
    </div>
@endsection
