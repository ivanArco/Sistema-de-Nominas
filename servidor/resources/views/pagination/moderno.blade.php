@if ($paginator->hasPages())
    <style>
        .paginacion-moderna {
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 10px;
            border-top: 1px solid #e4ebf4;
        }

        .paginacion-moderna .resumen {
            color: #4f6a81;
            font-size: 13px;
        }

        .paginacion-moderna .controles {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .paginacion-moderna .btn-pagina {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
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

        .paginacion-moderna .btn-pagina:hover {
            background: #f3f8ff;
        }

        .paginacion-moderna .btn-pagina.actual {
            background: #204b74;
            border-color: #204b74;
            color: #fff;
        }

        .paginacion-moderna .btn-pagina.inactivo {
            color: #98a9b8;
            background: #f8fafc;
            border-color: #e5edf5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>

    @php
        $inicio = $paginator->firstItem() ?? 0;
        $fin = $paginator->lastItem() ?? 0;
        $total = $paginator->total();
    @endphp

    <nav class="paginacion-moderna" role="navigation" aria-label="Paginacion">
        <div class="resumen">
            Mostrando {{ $inicio }} a {{ $fin }} de {{ $total }} resultados
        </div>

        <div class="controles">
            @if ($paginator->onFirstPage())
                <span class="btn-pagina inactivo">Anterior</span>
            @else
                <a class="btn-pagina" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="btn-pagina inactivo">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn-pagina actual" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="btn-pagina" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="btn-pagina" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="btn-pagina inactivo">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
