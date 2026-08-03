@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nueva meta de ventas</h2>
        <form method="POST" action="{{ route('ventas.metas.store') }}">
            @include('ventas_metas._form')
        </form>
    </div>
@endsection
