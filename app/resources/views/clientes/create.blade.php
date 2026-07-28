@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Alta de Cliente</h2>
        <form method="POST" action="{{ route('clientes.store') }}">
            @csrf
            @include('clientes._formulario')
        </form>
    </div>
@endsection
