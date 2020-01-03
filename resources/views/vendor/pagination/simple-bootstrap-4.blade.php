@if ($paginator->hasPages())
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link"><i class="fal fa-angle-double-left"></i></span></li>
        @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="fal fa-angle-double-left"></i></a></li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="fal fa-angle-double-right"></i></a></li>
        @else
            <li class="page-item disabled"><span class="page-link"><i class="fal fa-angle-double-right"></i></span></li>
        @endif
    </ul>
@endif
