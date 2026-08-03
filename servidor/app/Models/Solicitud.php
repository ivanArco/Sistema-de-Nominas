<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = [
        'empleado_id',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'estatus',
        'etapa_supervisor_estatus',
        'etapa_jefe_estatus',
        'revisado_por',
        'fecha_revision',
        'comentario_revision',
        'aprobado_supervisor_por',
        'fecha_aprobacion_supervisor',
        'comentario_supervisor',
        'aprobado_jefe_por',
        'fecha_aprobacion_jefe',
        'comentario_jefe',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_revision' => 'datetime',
        'fecha_aprobacion_supervisor' => 'datetime',
        'fecha_aprobacion_jefe' => 'datetime',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function supervisorRevisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_supervisor_por');
    }

    public function jefeRevisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_jefe_por');
    }
}
