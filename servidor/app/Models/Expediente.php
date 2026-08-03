<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expediente extends Model
{
    protected $fillable = [
        'empleado_id',
        'tipo_documento',
        'nombre_archivo',
        'ruta_archivo',
        'fecha_documento',
        'observaciones',
        'cargado_por',
    ];

    protected $casts = [
        'fecha_documento' => 'date',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function cargador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cargado_por');
    }
}
