<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
        th { background: #efefef; }
    </style>
</head>
<body>
    <h2>Reporte de Clientes</h2>
    <p>Fecha de generacion: {{ $fechaGeneracion }}</p>

    <table>
        <thead>
            <tr>
                <th>Nombre comercial</th>
                <th>Razon social</th>
                <th>RFC</th>
                <th>Contacto</th>
                <th>Correo</th>
                <th>Telefono 1</th>
                <th>Ciudad</th>
                <th>Estado</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre_comercial }}</td>
                    <td>{{ $cliente->razon_social }}</td>
                    <td>{{ $cliente->rfc }}</td>
                    <td>{{ $cliente->nombre_contacto }}</td>
                    <td>{{ $cliente->correo_electronico }}</td>
                    <td>{{ $cliente->telefono_contacto_1 }}</td>
                    <td>{{ $cliente->ciudad }}</td>
                    <td>{{ $cliente->estado }}</td>
                    <td>{{ $cliente->estatus }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
