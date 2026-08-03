<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaMeta extends Model
{
    protected $fillable = [
        'empleado_id',
        'periodo',
        'meta_monto',
        'meta_ventas',
        'avance_monto',
        'avance_ventas',
        'estatus',
    ];

    protected $casts = [
        'meta_monto' => 'decimal:2',
        'meta_ventas' => 'decimal:2',
        'avance_monto' => 'decimal:2',
        'avance_ventas' => 'decimal:2',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
