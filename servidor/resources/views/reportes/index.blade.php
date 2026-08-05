@extends('layouts.app')

@section('contenido')
    <style>
        .reportes-home {
            display: grid;
            gap: 16px;
        }

        .reportes-intro {
            border-radius: 16px;
            padding: 18px;
            background:
                radial-gradient(circle at 12% 20%, rgba(255, 255, 255, 0.65) 0, rgba(255, 255, 255, 0) 40%),
                linear-gradient(120deg, #eaf4ff 0%, #f3fff5 50%, #fff6e9 100%);
            border: 1px solid #d5e4ef;
        }

        .reportes-intro h2 {
            margin: 0;
            font-size: 28px;
            color: #163147;
        }

        .reportes-intro p {
            margin: 6px 0 0;
            color: #45617a;
        }

        .reportes-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 12px;
        }

        .reporte-card {
            display: block;
            border: 1px solid #d5e4ef;
            border-radius: 14px;
            padding: 14px;
            text-decoration: none;
            color: #163147;
            background: #fff;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .reporte-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(17, 41, 63, 0.1);
        }

        .reporte-card h3 {
            margin: 0;
            font-size: 16px;
        }

        .reporte-card p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #557089;
        }

        @media (max-width: 900px) {
            .reportes-grid {
                grid-template-columns: repeat(2, minmax(160px, 1fr));
            }
        }

        @media (max-width: 560px) {
            .reportes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="reportes-home">
        <section class="reportes-intro">
            <h2>Centro de Reportes</h2>
            <p>Consulta, exporta y genera reportes administrativos y de nomina.</p>
        </section>

    <div class="tarjeta">
        <div class="reportes-grid">
            <a class="reporte-card" href="{{ route('reportes.nominas') }}">
                <h3>Reporte de nominas</h3>
                <p>Filtra por periodo, estatus y empleado. Exporta en PDF o CSV.</p>
            </a>
            <a class="reporte-card" href="{{ route('reportes.empleados') }}">
                <h3>Reporte de empleados</h3>
                <p>Consulta altas, datos administrativos y estatus del personal.</p>
            </a>
            <a class="reporte-card" href="{{ route('reportes.incidencias') }}">
                <h3>Reporte de incidencias</h3>
                <p>Monitorea faltas, retardos, bonos y otras incidencias.</p>
            </a>
            @if(auth()->user()?->tienePermiso('ventas.gestionar') || auth()->user()?->tienePermiso('ventas.propias'))
                <a class="reporte-card" href="{{ route('ventas.index') }}">
                    <h3>Ventas y comisiones</h3>
                    <p>Visualiza resultados comerciales y montos por vendedor.</p>
                </a>
            @endif
            <a class="reporte-card" href="{{ route('usuarios.reporte.pdf') }}">
                <h3>Usuarios en PDF</h3>
                <p>Exporta el directorio de usuarios del sistema.</p>
            </a>
            <a class="reporte-card" href="{{ route('clientes.reporte.pdf') }}">
                <h3>Clientes en PDF</h3>
                <p>Descarga el catalogo de clientes en formato imprimible.</p>
            </a>
        </div>
    </div>
    </div>
@endsection
