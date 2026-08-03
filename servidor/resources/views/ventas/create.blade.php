@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nueva venta</h2>
        <form method="POST" action="{{ route('ventas.store') }}">
            @csrf
            @include('ventas._formulario')
        </form>
    </div>
@endsection
