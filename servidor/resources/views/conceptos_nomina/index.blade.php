@extends('layouts.app')

@section('contenido')
    <div class="tarjeta">
        <h2>Conceptos de nomina</h2>

        <form method="GET" action="{{ route('conceptos-nomina.index') }}" class="grilla">
            <div>
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    @foreach(['PERCEPCION', 'DEDUCCION', 'OTRO_PAGO'] as $tipo)
                        <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Activo</label>
                <select name="activo">
                    <option value="">Todos</option>
                    <option value="1" @selected(($filtros['activo'] ?? '') === '1')>Si</option>
                    <option value="0" @selected(($filtros['activo'] ?? '') === '0')>No</option>
                </select>
            </div>

            <div class="acciones" style="grid-column: 1 / -1;">
                <button class="boton" type="submit">Consultar</button>
                <a class="boton secundario" href="{{ route('conceptos-nomina.index') }}">Limpiar</a>
                <a class="boton" href="{{ route('conceptos-nomina.create') }}">Nuevo concepto</a>
                <a class="boton secundario" href="{{ route('catalogos-nomina.index') }}">Ir a catalogos</a>
            </div>
        </form>
    </div>

    <div class="tarjeta" style="overflow-x:auto;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Gravado</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conceptos as $concepto)
                    <tr>
                        <td>{{ $concepto->clave }}</td>
                        <td>{{ $concepto->nombre }}</td>
                        <td>{{ $concepto->tipo }}</td>
                        <td>{{ $concepto->gravado ? 'Si' : 'No' }}</td>
                        <td>{{ $concepto->activo ? 'Si' : 'No' }}</td>
                        <td>
                            <div class="acciones">
                                <a class="boton secundario" href="{{ route('conceptos-nomina.edit', $concepto->id) }}">Editar</a>
                                <form method="POST" action="{{ route('conceptos-nomina.destroy', $concepto->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton alerta" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay conceptos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 12px;">
            {{ $conceptos->links() }}
        </div>
    </div>
@endsection
