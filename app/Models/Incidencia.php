<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    protected $fillable = [
        'empleado_id',
        'periodo_nomina_id',
        'tipo',
        'descripcion',
        'cantidad',
        'monto',
    ];
}
