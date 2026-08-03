@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nuevo contrato</h2>
        <form method="POST" action="{{ route('contratos.store') }}">
            @include('contratos._form')
        </form>
    </div>
@endsection
