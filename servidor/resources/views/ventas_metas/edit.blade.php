@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar meta de ventas</h2>
        <form method="POST" action="{{ route('ventas.metas.update', $meta->id) }}">
            @method('PUT')
            @include('ventas_metas._form')
        </form>
    </div>
@endsection
