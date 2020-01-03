@if ($paginator->hasPages())
    <ul class="pagination">
        
        <?php
            $fa = Request::is('dashboard/*') ? 'fa' : 'fal';
            
            //echo $paginator->onFirstPage();
            
        ?>
        
        {{-- Previous Page Link --}}
        
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link"><i class="{{ $fa }} fa-angle-double-left"></i></span></li>
        @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="{{ $fa }} fa-angle-double-left"></i></a></li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            <?php
//            print_r($element);
//            exit;
            ?>
            
            {{--
            @if (is_array($element))
            @foreach ($element as $page => $url)
                <!--  Use three dots when current page is greater than 4.  -->
                @if ($paginator->currentPage() > 4 && $page === 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif

                <!--  Show active page else show the first and last two pages from current page.  -->
                @if ($page == $paginator->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @elseif (
                    $page === $paginator->currentPage() + 1 ||
                    $page === $paginator->currentPage() + 2 ||
                    //$page === $paginator->currentPage() + 3 ||
                    $page === $paginator->currentPage() - 1 ||
                    $page === $paginator->currentPage() - 2 ||
                    //$page === $paginator->currentPage() - 3 ||
                    $page === $paginator->lastPage() ||
                    $page === 1
                )
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif

                <!--  Use three dots when current page is away from end.  -->
                @if (
                    $paginator->currentPage() < $paginator->lastPage() - 3 &&
                    $page === $paginator->lastPage() - 1
                    )
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endforeach
            @endif
            --}}
            
            {{-- "Three Dots" Separator --}}
            
            @if (is_string($element))
                <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
            @endif
            

            {{-- Array Of Links --}}
            
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
            
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="{{ $fa }} fa-angle-double-right"></i></a></li>
        @else
            <li class="page-item disabled"><span class="page-link"><i class="{{ $fa }} fa-angle-double-right"></i></span></li>
        @endif
    </ul>
@endif
