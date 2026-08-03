@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Cambiar contrasena</h2>

        <form method="POST" action="{{ route('mi-cuenta.password.update') }}" class="grilla">
            @csrf
            @method('PUT')

            <div>
                <label>Contrasena actual</label>
                <input type="password" name="contrasena_actual" required>
            </div>
            <div></div>
            <div>
                <label>Nueva contrasena</label>
                <input type="password" name="contrasena_nueva" required>
            </div>
            <div>
                <label>Confirmar nueva contrasena</label>
                <input type="password" name="contrasena_nueva_confirmation" required>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Actualizar contrasena</button>
                <a class="boton secundario" href="{{ route('mi-cuenta.index') }}">Volver</a>
            </div>
        </form>
    </div>
@endsection
