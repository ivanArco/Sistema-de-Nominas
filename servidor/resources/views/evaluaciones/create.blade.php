@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nueva evaluacion de desempeno</h2>
        <form method="POST" action="{{ route('evaluaciones.store') }}">
            @include('evaluaciones._form')
        </form>
    </div>
@endsection
