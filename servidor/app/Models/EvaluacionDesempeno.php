<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluacionDesempeno extends Model
{
    protected $table = 'evaluacion_desempenos';

    protected $fillable = [
        'empleado_id',
        'evaluador_id',
        'periodo',
        'calificacion',
        'fortalezas',
        'areas_mejora',
        'estatus',
    ];

    protected $casts = [
        'calificacion' => 'decimal:2',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }
}
