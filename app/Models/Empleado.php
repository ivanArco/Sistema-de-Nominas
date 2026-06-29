<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $fillable = [
        'num_empleado',
        'nombre',
        'ap_paterno',
        'ap_materno',
        'curp',
        'rfc',
        'nss',
        'correo',
        'telefono',
        'f_ingreso',
        'f_baja',
        'tipo_cont',
        'jornada',
        'sal_dia',
        'sal_int',
        'depto_id',
        'puesto_id',
        'estatus',
    ];
}
