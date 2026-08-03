@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar evaluacion de desempeno</h2>
        <form method="POST" action="{{ route('evaluaciones.update', $evaluacion->id) }}">
            @method('PUT')
            @include('evaluaciones._form')
        </form>
    </div>
@endsection
