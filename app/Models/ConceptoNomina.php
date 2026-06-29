<?php

namespace App\Models;

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
}
