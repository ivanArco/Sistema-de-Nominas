<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's clients.
     */
    public function run(): void
    {
        Cliente::updateOrCreate([
            'nombre_comercial' => 'Soluciones TI SA de CV',
        ], [
            'razon_social' => 'Soluciones TI Sociedad Anonima de Capital Variable',
            'rfc' => 'SOTI010101AAA',
            'nombre_contacto' => 'Mariana Lopez',
            'correo_electronico' => 'mariana.lopez@solucionesti.com',
            'telefono_contacto_1' => '5551000001',
            'telefono_contacto_2' => '5551000002',
            'direccion' => 'Av. Tecnologica 123',
            'colonia' => 'Parque Industrial',
            'codigo_postal' => '55000',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'estatus' => 'ACTIVO',
        ]);

        Cliente::updateOrCreate([
            'nombre_comercial' => 'Servicios Integrales del Norte',
        ], [
            'razon_social' => 'Servicios Integrales del Norte SA de CV',
            'rfc' => 'SINT010101BBB',
            'nombre_contacto' => 'Carlos Mendoza',
            'correo_electronico' => 'carlos.mendoza@sin.com',
            'telefono_contacto_1' => '5552000001',
            'telefono_contacto_2' => '5552000002',
            'direccion' => 'Calle Industria 45',
            'colonia' => 'Zona Norte',
            'codigo_postal' => '56000',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'estatus' => 'ACTIVO',
        ]);
    }
}
