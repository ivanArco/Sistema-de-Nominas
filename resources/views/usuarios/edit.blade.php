@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Edicion de Usuario</h2>
        <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
            @csrf
            @method('PUT')
            @include('usuarios._formulario')
        </form>
    </div>
@endsection
