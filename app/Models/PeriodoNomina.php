<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoNomina extends Model
{
    protected $fillable = [
        'anio',
        'numero_periodo',
        'tipo_periodo',
        'fecha_inicio',
        'fecha_fin',
        'fecha_pago',
        'estatus',
    ];
}
