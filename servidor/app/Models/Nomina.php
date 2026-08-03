<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'cierre_autorizado',
        'cierre_autorizado_por',
        'fecha_cierre_autorizado',
        'cierre_observaciones',
    ];

    protected $casts = [
        'dias_pagados' => 'decimal:2',
        'total_percepciones' => 'decimal:2',
        'total_deducciones' => 'decimal:2',
        'neto_pagado' => 'decimal:2',
        'cierre_autorizado' => 'boolean',
        'fecha_cierre_autorizado' => 'datetime',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoNomina::class, 'periodo_nomina_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(NominaDetalle::class);
    }

    public function autorizadorCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cierre_autorizado_por');
    }
}
