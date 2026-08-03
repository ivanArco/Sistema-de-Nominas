@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar incidencia</h2>

        <form method="POST" action="{{ route('incidencias.update', $incidencia->id) }}">
            @csrf
            @method('PUT')
            @include('incidencias._formulario')
        </form>
    </div>
@endsection
