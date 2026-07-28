@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar empleado</h2>

        <form method="POST" action="{{ route('empleados.update', $empleado->id) }}">
            @csrf
            @method('PUT')
            @include('empleados._formulario')
        </form>
    </div>
@endsection
