@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nuevo periodo de nomina</h2>

        <form method="POST" action="{{ route('periodos-nomina.store') }}">
            @csrf
            @include('periodos_nomina._formulario')
        </form>
    </div>
@endsection
