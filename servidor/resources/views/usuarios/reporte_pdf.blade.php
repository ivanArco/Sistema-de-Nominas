<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
        th { background: #efefef; }
    </style>
</head>
<body>
    <h2>Reporte de Usuarios</h2>
    <p>Fecha de generacion: {{ $fechaGeneracion }}</p>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>CURP</th>
                <th>Correo</th>
                <th>Telefono 1</th>
                <th>Area</th>
                <th>NSS</th>
                <th>Ciudad</th>
                <th>Estado</th>
                <th>Rol</th>
                <th>Activo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nombre_usuario }}</td>
                    <td>{{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}</td>
                    <td>{{ $usuario->curp }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->telefono_contacto_1 }}</td>
                    <td>{{ $usuario->area_contratacion }}</td>
                    <td>{{ $usuario->numero_seguro_social }}</td>
                    <td>{{ $usuario->ciudad }}</td>
                    <td>{{ $usuario->estado }}</td>
                    <td>{{ $usuario->rol }}</td>
                    <td>{{ $usuario->activo ? 'Si' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
