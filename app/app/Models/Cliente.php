<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'nombre_contacto',
        'correo_electronico',
        'telefono_contacto_1',
        'telefono_contacto_2',
        'direccion',
        'colonia',
        'codigo_postal',
        'ciudad',
        'estado',
        'estatus',
    ];
}
