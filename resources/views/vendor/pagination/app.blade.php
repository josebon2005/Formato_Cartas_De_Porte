@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="Paginacion">
        <div class="pagination-summary">
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} registros
        </div>

        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="pagination-link disabled" aria-disabled="true">Anterior</span>
            @else
                <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-ellipsis" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-link active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="pagination-link disabled" aria-disabled="true">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
