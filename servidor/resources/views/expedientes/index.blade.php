@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Expedientes y documentos</h2>

        <form method="GET" action="{{ route('expedientes.index') }}" class="grilla">
            <div>
                <label>Empleado</label>
                <input name="empleado" value="{{ $filtros['empleado'] ?? '' }}" placeholder="Numero o nombre">
            </div>
            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Filtrar</button>
                <a class="boton secundario" href="{{ route('expedientes.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('expedientes.create') }}">Subir documento</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>Fecha doc</th>
                    <th>Cargado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expedientes as $expediente)
                    <tr>
                        <td>{{ $expediente->empleado->num_empleado ?? '-' }} - {{ $expediente->empleado->nombre ?? '-' }}</td>
                        <td>{{ $expediente->tipo_documento }}</td>
                        <td>{{ $expediente->nombre_archivo }}</td>
                        <td>{{ optional($expediente->fecha_documento)->format('d/m/Y') ?: '-' }}</td>
                        <td>{{ $expediente->cargador->nombre_usuario ?? '-' }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('expedientes.show', $expediente->id) }}">Descargar</a>
                                <form method="POST" action="{{ route('expedientes.destroy', $expediente->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Sin documentos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $expedientes->links() }}</div>
    </div>
@endsection
