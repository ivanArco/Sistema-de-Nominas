<?php

namespace App\Models;

use App\Services\SalarioIntegradoService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $fillable = [
        'num_empleado',
        'nombre',
        'ap_paterno',
        'ap_materno',
        'curp',
        'rfc',
        'nss',
        'correo',
        'telefono',
        'f_ingreso',
        'f_baja',
        'tipo_cont',
        'jornada',
        'tipo_pago',
        'sal_dia',
        'sal_int',
        'depto_id',
        'puesto_id',
        'porcentaje_infonavit',
        'porcentaje_afore',
        'usa_fondo_ahorro',
        'porcentaje_fondo_ahorro',
        'semanas_cotizadas',
        'fondo_retiro_acumulado',
        'estatus',
    ];

    protected $casts = [
        'f_ingreso' => 'date',
        'f_baja' => 'date',
        'sal_dia' => 'decimal:2',
        'sal_int' => 'decimal:2',
        'porcentaje_infonavit' => 'decimal:3',
        'porcentaje_afore' => 'decimal:3',
        'usa_fondo_ahorro' => 'boolean',
        'porcentaje_fondo_ahorro' => 'decimal:3',
        'semanas_cotizadas' => 'decimal:2',
        'fondo_retiro_acumulado' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Empleado $empleado): void {
            if (!config('nomina.calcular_salario_integrado_automatico', true)) {
                return;
            }

            $salarioDiario = (float) ($empleado->sal_dia ?? 0);
            $fechaIngreso = $empleado->f_ingreso;

            $empleado->sal_int = SalarioIntegradoService::calcular($salarioDiario, $fechaIngreso);
        });
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'depto_id');
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }

    public function nominas(): HasMany
    {
        return $this->hasMany(Nomina::class);
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(EmpleadoHistorial::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class);
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    public function metasVenta(): HasMany
    {
        return $this->hasMany(VentaMeta::class);
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(EvaluacionDesempeno::class);
    }
}
