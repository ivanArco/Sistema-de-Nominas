@extends('layouts.app')

@section('contenido')
    <style>
        .usuarios-alta-layout {
            display: grid;
            gap: 16px;
        }

        .usuarios-alta-hero {
            border-radius: 18px;
            padding: 20px;
            border: 1px solid #cfe0ef;
            background:
                radial-gradient(circle at 12% 14%, rgba(255, 255, 255, 0.72) 0, rgba(255, 255, 255, 0) 35%),
                linear-gradient(130deg, #dff5ff 0%, #e9fdf1 50%, #fff2d9 100%);
        }

        .usuarios-alta-hero h2 {
            margin: 0;
            font-size: 29px;
            color: #17324a;
        }

        .usuarios-alta-hero p {
            margin: 8px 0 0;
            color: #4d6a84;
        }

        .usuarios-alta-form {
            border-radius: 16px;
            border: 1px solid #dde8f3;
            background: #fff;
            padding: 14px;
        }
    </style>

    <div class="usuarios-alta-layout">
        <section class="usuarios-alta-hero">
            <h2>Alta de Usuario</h2>
            <p>Completa los datos personales y de acceso. La contrasena inicial sera la CURP del usuario.</p>
        </section>

        <div class="usuarios-alta-form">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf
                @include('usuarios._formulario')
            </form>
        </div>
    </div>
@endsection
