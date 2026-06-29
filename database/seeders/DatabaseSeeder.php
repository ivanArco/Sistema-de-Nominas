<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'supervisor@sistema.local',
        ], [
            'name' => 'supervisor1',
            'nombre_usuario' => 'supervisor1',
            'password' => Hash::make('Supervisor123!'),
            'nombre' => 'Usuario',
            'apellido_paterno' => 'Supervisor',
            'apellido_materno' => 'Sistema',
            'curp' => 'SUPS010101HDFAAA01',
            'telefono_contacto_1' => '5550000001',
            'telefono_contacto_2' => '5550000002',
            'fecha_contratacion' => now()->toDateString(),
            'area_contratacion' => 'Supervision',
            'numero_seguro_social' => 'NSS0000000001',
            'fecha_alta_servicio_salud' => now()->toDateString(),
            'direccion' => 'Direccion supervisor',
            'colonia' => 'Centro',
            'codigo_postal' => '00000',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'rol' => 'SUPERVISOR',
            'activo' => true,
        ]);
    }
}
