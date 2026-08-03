@extends('layouts.app')

@section('contenido')
<div class="tarjeta">
        <form action="{{ route('permisos.store') }}" method="POST">
            @csrf
            @include('permisos._form')
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{ route('permisos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
</div>
@endsection
