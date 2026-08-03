<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ConceptoNomina extends Model
{
    protected $fillable = [
        'clave',
        'nombre',
        'tipo',
        'gravado',
        'activo',
    ];

    protected $casts = [
        'gravado' => 'boolean',
        'activo' => 'boolean',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(NominaDetalle::class);
    }
}
