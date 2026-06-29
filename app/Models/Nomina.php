<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nomina extends Model
{
    protected $fillable = [
        'empleado_id',
        'periodo_nomina_id',
        'dias_pagados',
        'total_percepciones',
        'total_deducciones',
        'neto_pagado',
        'estatus',
    ];
}
