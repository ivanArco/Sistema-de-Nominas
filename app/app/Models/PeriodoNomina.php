<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_pago' => 'date',
    ];

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }

    public function nominas(): HasMany
    {
        return $this->hasMany(Nomina::class);
    }
}
