<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    protected $fillable = [
        'empleado_id',
        'folio',
        'fecha_venta',
        'cliente_nombre',
        'monto_bruto',
        'porcentaje_comision',
        'comision_calculada',
        'bono_desempeno',
        'bono_estatus',
        'bono_autorizado_por',
        'bono_autorizado_fecha',
        'bono_autorizado_comentario',
        'estatus',
        'observaciones',
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'monto_bruto' => 'decimal:2',
        'porcentaje_comision' => 'decimal:2',
        'comision_calculada' => 'decimal:2',
        'bono_desempeno' => 'decimal:2',
        'bono_autorizado_fecha' => 'datetime',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function autorizadorBono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bono_autorizado_por');
    }
}
