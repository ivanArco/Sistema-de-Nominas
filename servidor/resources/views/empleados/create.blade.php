@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nuevo empleado</h2>

        <form method="POST" action="{{ route('empleados.store') }}">
            @csrf
            @include('empleados._formulario')
        </form>
    </div>
@endsection
