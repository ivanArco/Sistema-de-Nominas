@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Editar mis datos</h2>

        <form method="POST" action="{{ route('mi-cuenta.update') }}" class="grilla">
            @csrf
            @method('PUT')

            <div>
                <label>Correo</label>
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required>
            </div>
            <div>
                <label>Telefono contacto 1</label>
                <input name="telefono_contacto_1" value="{{ old('telefono_contacto_1', $usuario->telefono_contacto_1) }}" required>
            </div>
            <div>
                <label>Telefono contacto 2</label>
                <input name="telefono_contacto_2" value="{{ old('telefono_contacto_2', $usuario->telefono_contacto_2) }}">
            </div>
            <div>
                <label>Direccion</label>
                <input name="direccion" value="{{ old('direccion', $usuario->direccion) }}" required>
            </div>
            <div>
                <label>Colonia</label>
                <input name="colonia" value="{{ old('colonia', $usuario->colonia) }}" required>
            </div>
            <div>
                <label>Codigo postal</label>
                <input name="codigo_postal" value="{{ old('codigo_postal', $usuario->codigo_postal) }}" required>
            </div>
            <div>
                <label>Ciudad</label>
                <input name="ciudad" value="{{ old('ciudad', $usuario->ciudad) }}" required>
            </div>
            <div>
                <label>Estado</label>
                <input name="estado" value="{{ old('estado', $usuario->estado) }}" required>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Guardar cambios</button>
                <a class="boton secundario" href="{{ route('mi-cuenta.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
