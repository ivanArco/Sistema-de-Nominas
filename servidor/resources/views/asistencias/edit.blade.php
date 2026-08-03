@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar asistencia</h2>
        <form method="POST" action="{{ route('asistencias.update', $asistencia->id) }}">
            @csrf
            @method('PUT')
            @include('asistencias._formulario')
        </form>
    </div>
@endsection
