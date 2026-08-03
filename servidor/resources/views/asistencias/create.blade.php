@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nueva asistencia</h2>
        <form method="POST" action="{{ route('asistencias.store') }}">
            @csrf
            @include('asistencias._formulario')
        </form>
    </div>
@endsection
