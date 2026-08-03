@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Nueva solicitud</h2>

        <form method="POST" action="{{ route('solicitudes.store') }}" class="grilla">
            @csrf

            <div>
                <label>Tipo</label>
                <select name="tipo" required>
                    <option value="VACACIONES" @selected(old('tipo') === 'VACACIONES')>VACACIONES</option>
                    <option value="PERMISO" @selected(old('tipo') === 'PERMISO')>PERMISO</option>
                </select>
            </div>
            <div></div>
            <div>
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
            </div>
            <div>
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
            </div>
            <div style="grid-column: 1 / -1;">
                <label>Motivo</label>
                <textarea name="motivo" rows="4">{{ old('motivo') }}</textarea>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Enviar solicitud</button>
                <a class="boton secundario" href="{{ route('solicitudes.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
