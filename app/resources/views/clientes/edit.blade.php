@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Edicion de Cliente</h2>
        <form method="POST" action="{{ route('clientes.update', $cliente->id) }}">
            @csrf
            @method('PUT')
            @include('clientes._formulario')
        </form>
    </div>
@endsection
