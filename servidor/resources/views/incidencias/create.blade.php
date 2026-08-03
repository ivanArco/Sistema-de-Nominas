@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nueva incidencia</h2>

        <form method="POST" action="{{ route('incidencias.store') }}">
            @csrf
            @include('incidencias._formulario')
        </form>
    </div>
@endsection
