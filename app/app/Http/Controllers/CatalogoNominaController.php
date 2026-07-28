<?php

namespace App\Http\Controllers;

use App\Models\ConceptoNomina;
use App\Models\PeriodoNomina;
use Illuminate\View\View;

class CatalogoNominaController extends Controller
{
    /**
     * Muestra la gestion unificada de periodos y conceptos de nomina.
     */
    public function index(): View
    {
        $periodos = PeriodoNomina::query()
            ->orderByDesc('anio')
            ->orderByDesc('numero_periodo')
            ->paginate(8, ['*'], 'periodos_page');

        $conceptos = ConceptoNomina::query()
            ->orderBy('tipo')
            ->orderBy('clave')
            ->paginate(8, ['*'], 'conceptos_page');

        return view('catalogos_nomina.index', [
            'periodos' => $periodos,
            'conceptos' => $conceptos,
            'titulo' => 'Catalogos de Nomina',
        ]);
    }
}
