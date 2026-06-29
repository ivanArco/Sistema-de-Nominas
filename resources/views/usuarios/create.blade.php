@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Alta de Usuario</h2>
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf
            @include('usuarios._formulario')
        </form>
    </div>
@endsection
