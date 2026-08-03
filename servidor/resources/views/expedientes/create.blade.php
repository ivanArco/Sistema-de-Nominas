@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Subir documento a expediente</h2>

        <form method="POST" action="{{ route('expedientes.store') }}" enctype="multipart/form-data" class="grilla">
            @csrf

            <div>
                <label>Empleado</label>
                <select name="empleado_id" required>
                    <option value="">Selecciona...</option>
                    @foreach($empleados as $empleado)
                        <option value="{{ $empleado->id }}" @selected(old('empleado_id') == $empleado->id)>
                            {{ $empleado->num_empleado }} - {{ $empleado->nombre }} {{ $empleado->ap_paterno }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Tipo de documento</label>
                <input name="tipo_documento" value="{{ old('tipo_documento') }}" required>
            </div>
            <div>
                <label>Fecha del documento</label>
                <input type="date" name="fecha_documento" value="{{ old('fecha_documento') }}">
            </div>
            <div>
                <label>Archivo (PDF/JPG/PNG/DOC/DOCX, max 5MB)</label>
                <input type="file" name="archivo" required>
            </div>
            <div style="grid-column: 1 / -1;">
                <label>Observaciones</label>
                <textarea name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Guardar documento</button>
                <a class="boton secundario" href="{{ route('expedientes.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
