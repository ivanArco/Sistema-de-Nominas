@extends('layouts.app')

@section('contenido')
<div class="tarjeta">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            @include('roles._form')
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
</div>
@endsection
