@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar contrato</h2>
        <form method="POST" action="{{ route('contratos.update', $contrato->id) }}">
            @method('PUT')
            @include('contratos._form')
        </form>
    </div>
@endsection
