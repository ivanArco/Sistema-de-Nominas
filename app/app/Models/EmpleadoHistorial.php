<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpleadoHistorial extends Model
{
    protected $fillable = [
        'empleado_id',
        'fecha_movimiento',
        'tipo_movimiento',
        'salario_diario',
        'puesto_id',
        'semanas_cotizadas',
        'fondo_retiro_acumulado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_movimiento' => 'date',
        'salario_diario' => 'decimal:2',
        'semanas_cotizadas' => 'decimal:2',
        'fondo_retiro_acumulado' => 'decimal:2',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }
}
