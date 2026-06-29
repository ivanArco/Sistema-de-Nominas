<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaDetalle extends Model
{
    protected $fillable = [
        'nomina_id',
        'concepto_nomina_id',
        'cantidad',
        'importe',
        'observaciones',
    ];
}
