@extends('layouts.app')

@section('contenido')
<div class="tarjeta">
        <form action="{{ route('roles.update', $rol) }}" method="POST">
            @csrf
            @method('PUT')
            @include('roles._form')
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Actualizar</button>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
</div>
@endsection
