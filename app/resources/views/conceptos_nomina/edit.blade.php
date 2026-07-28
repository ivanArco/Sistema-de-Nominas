@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar concepto</h2>

        <form method="POST" action="{{ route('conceptos-nomina.update', $concepto->id) }}">
            @csrf
            @method('PUT')
            @include('conceptos_nomina._formulario')
        </form>
    </div>
@endsection
