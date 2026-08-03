@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar venta</h2>
        <form method="POST" action="{{ route('ventas.update', $venta->id) }}">
            @csrf
            @method('PUT')
            @include('ventas._formulario')
        </form>
    </div>
@endsection
