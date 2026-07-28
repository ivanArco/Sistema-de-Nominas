<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\Nomina;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(): View
    {
        return view('reportes.index');
    }

    public function nominas(Request $request): View
    {
        $nominas = $this->consultarNominas($request)->paginate(30)->withQueryString();

        return view('reportes.nominas', [
            'nominas' => $nominas,
            'filtros' => $this->filtrosNominas($request),
            'modoImpresion' => $request->boolean('imprimir'),
        ]);
    }

    public function nominasPdf(Request $request)
    {
        $nominas = $this->consultarNominas($request)->get();
        $cliente = Cliente::query()->orderByDesc('id')->first();
        $empresaNombre = trim((string) ($cliente->razon_social ?? $cliente->nombre_comercial ?? ''));
        if ($empresaNombre === '') {
            $empresaNombre = (string) config('app.name', 'Sistema de Nomina');
        }

        $pdf = Pdf::loadView('reportes.nominas_pdf', [
            'nominas' => $nominas,
            'filtros' => $this->filtrosNominas($request),
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'empresaNombre' => $empresaNombre,
        ])->setPaper('letter', 'portrait');

        $nombreArchivo = 'reporte_nominas.pdf';
        if ($nominas->count() === 1) {
            $nomina = $nominas->first();
            $empleado = $nomina?->empleado;
            $nombreBase = trim(implode('_', array_filter([
                (string) ($empleado->num_empleado ?? ''),
                (string) ($empleado->nombre ?? ''),
                (string) ($empleado->ap_paterno ?? ''),
                (string) ($empleado->ap_materno ?? ''),
            ])));

            if ($nombreBase !== '') {
                $nombreLimpio = Str::of($nombreBase)->ascii()->replace(' ', '_')->replaceMatches('/[^A-Za-z0-9_\-]/', '')->toString();
                $nombreArchivo = 'nomina_' . $nombreLimpio . '.pdf';
            }
        }

        return $pdf->download($nombreArchivo);
    }

    public function nominasCsv(Request $request): StreamedResponse
    {
        $nominas = $this->consultarNominas($request)->get();

        return response()->streamDownload(function () use ($nominas) {
            $salida = fopen('php://output', 'w');
            fputcsv($salida, [
                'Empleado',
                'Periodo',
                'Tipo periodo',
                'Clasificacion',
                'Salario bruto',
                'Credito al salario',
                'ISR',
                'IMSS',
                'INFONAVIT',
                'Otras deducciones',
                'Deducciones totales',
                'Salario neto',
            ]);

            foreach ($nominas as $nomina) {
                $desglose = $this->desgloseNomina($nomina);

                fputcsv($salida, [
                    ($nomina->empleado->num_empleado ?? '-') . ' - ' . trim(($nomina->empleado->nombre ?? '') . ' ' . ($nomina->empleado->ap_paterno ?? '')),
                    ($nomina->periodo->anio ?? '-') . '/' . ($nomina->periodo->numero_periodo ?? '-'),
                    $nomina->periodo->tipo_periodo ?? '-',
                    $this->clasificacionPeriodo($nomina),
                    number_format($desglose['bruto'], 2, '.', ''),
                    number_format($desglose['credito_salario'], 2, '.', ''),
                    number_format($desglose['isr'], 2, '.', ''),
                    number_format($desglose['imss'], 2, '.', ''),
                    number_format($desglose['infonavit'], 2, '.', ''),
                    number_format($desglose['otras_deducciones'], 2, '.', ''),
                    number_format((float) $nomina->total_deducciones, 2, '.', ''),
                    number_format((float) $nomina->neto_pagado, 2, '.', ''),
                ]);
            }

            fclose($salida);
        }, 'reporte_nominas.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function empleados(Request $request): View
    {
        $texto = $request->string('texto')->trim()->toString();
        $estatus = $request->string('estatus')->toString();

        $empleados = Empleado::query()
            ->with(['departamento', 'puesto'])
            ->when($texto !== '', function ($consulta) use ($texto) {
                $consulta->where(function ($subconsulta) use ($texto) {
                    $subconsulta->where('num_empleado', 'like', "%{$texto}%")
                        ->orWhere('nombre', 'like', "%{$texto}%")
                        ->orWhere('curp', 'like', "%{$texto}%")
                        ->orWhere('rfc', 'like', "%{$texto}%");
                });
            })
            ->when($estatus !== '', fn ($consulta) => $consulta->where('estatus', $estatus))
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('reportes.empleados', [
            'empleados' => $empleados,
            'filtros' => $request->only(['texto', 'estatus']),
        ]);
    }

    public function incidencias(Request $request): View
    {
        $tipo = $request->string('tipo')->toString();
        $periodo = $request->string('tipo_periodo')->toString();

        $incidencias = Incidencia::query()
            ->with(['empleado', 'periodo'])
            ->when($tipo !== '', fn ($consulta) => $consulta->where('tipo', $tipo))
            ->when($periodo !== '', fn ($consulta) => $consulta->whereHas('periodo', fn ($sub) => $sub->where('tipo_periodo', $periodo)))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('reportes.incidencias', [
            'incidencias' => $incidencias,
            'filtros' => $request->only(['tipo', 'tipo_periodo']),
            'tipos' => ['FALTA', 'RETARDO', 'HORA_EXTRA', 'BONO', 'INCAPACIDAD', 'VACACIONES', 'VACACIONES_PAGADAS', 'DESCANSO', 'OTRO'],
        ]);
    }

    private function consultarNominas(Request $request)
    {
        $filtros = $this->filtrosNominas($request);

        return Nomina::query()
            ->with(['empleado', 'periodo', 'detalles.concepto'])
            ->when($filtros['nomina_ids'] !== [], fn ($consulta) => $consulta->whereIn('id', $filtros['nomina_ids']))
            ->when($filtros['tipo_periodo'] !== '', function ($consulta) use ($filtros) {
                $consulta->whereHas('periodo', fn ($sub) => $sub->where('tipo_periodo', $filtros['tipo_periodo']));
            })
            ->when($filtros['estatus'] !== '', fn ($consulta) => $consulta->where('estatus', $filtros['estatus']))
            ->when($filtros['empleado'] !== '', function ($consulta) use ($filtros) {
                $consulta->whereHas('empleado', function ($subconsulta) use ($filtros) {
                    $subconsulta->where('num_empleado', 'like', "%{$filtros['empleado']}%")
                        ->orWhere('nombre', 'like', "%{$filtros['empleado']}%")
                        ->orWhere('curp', 'like', "%{$filtros['empleado']}%")
                        ->orWhere('rfc', 'like', "%{$filtros['empleado']}%");
                });
            })
            ->latest();
    }

    private function filtrosNominas(Request $request): array
    {
        $nominaIds = collect((array) $request->input('nomina_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        return [
            'tipo_periodo' => $request->string('tipo_periodo')->toString(),
            'estatus' => $request->string('estatus')->toString(),
            'empleado' => $request->string('empleado')->trim()->toString(),
            'nomina_ids' => $nominaIds,
        ];
    }

    private function desgloseNomina(Nomina $nomina): array
    {
        $detalles = $nomina->detalles instanceof Collection ? $nomina->detalles : collect($nomina->detalles);

        $sumarClave = static function (Collection $detallesNomina, string $clave): float {
            return (float) $detallesNomina
                ->filter(fn ($detalle) => ($detalle->concepto->clave ?? null) === $clave)
                ->sum(fn ($detalle) => (float) $detalle->importe);
        };

        $isr = $sumarClave($detalles, 'D001');
        $imss = $sumarClave($detalles, 'D002');
        $infonavit = $sumarClave($detalles, 'D003');
        $afore = $sumarClave($detalles, 'D004');
        $fondo = $sumarClave($detalles, 'D005');
        $descuentoIncidencias = $sumarClave($detalles, 'D090');

        $otrasDeducciones = max(0.0, $descuentoIncidencias + $afore + $fondo);

        return [
            'bruto' => (float) $nomina->total_percepciones,
            'credito_salario' => 0.0,
            'isr' => $isr,
            'imss' => $imss,
            'infonavit' => $infonavit,
            'otras_deducciones' => $otrasDeducciones,
        ];
    }

    private function clasificacionPeriodo(Nomina $nomina): string
    {
        $periodo = $nomina->periodo;
        $tipo = strtoupper((string) ($periodo->tipo_periodo ?? ''));

        if ($tipo === 'SEMANAL') {
            $diaInicio = (int) optional($periodo->fecha_inicio)->format('d');
            $semanaMes = max(1, (int) ceil($diaInicio / 7));

            return 'Semana ' . $semanaMes;
        }

        if ($tipo === 'QUINCENAL' || $tipo === 'CATORCENAL') {
            $diaInicio = (int) optional($periodo->fecha_inicio)->format('d');
            $quincena = $diaInicio <= 15 ? 1 : 2;

            return 'Quincena ' . $quincena;
        }

        if ($tipo === 'MENSUAL') {
            return 'Mensual';
        }

        return 'Sin clasificar';
    }
}
