@if ($paginator->hasPages())
    <style>
        .paginacion-simple {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding-top: 10px;
            border-top: 1px solid #e4ebf4;
        }

        .paginacion-simple .btn-pagina {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid #d8e3ef;
            background: #fff;
            color: #22445f;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .paginacion-simple .btn-pagina.inactivo {
            color: #98a9b8;
            background: #f8fafc;
            border-color: #e5edf5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>

    <nav class="paginacion-simple" role="navigation" aria-label="Paginacion simple">
        @if ($paginator->onFirstPage())
            <span class="btn-pagina inactivo">Anterior</span>
        @else
            <a class="btn-pagina" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="btn-pagina" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
        @else
            <span class="btn-pagina inactivo">Siguiente</span>
        @endif
    </nav>
@endif
