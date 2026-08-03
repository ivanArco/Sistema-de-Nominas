@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nuevo concepto</h2>

        <form method="POST" action="{{ route('conceptos-nomina.store') }}">
            @csrf
            @include('conceptos_nomina._formulario')
        </form>
    </div>
@endsection
