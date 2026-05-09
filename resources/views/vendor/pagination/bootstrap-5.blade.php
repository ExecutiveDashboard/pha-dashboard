@if ($paginator->hasPages())
<div class="pha-pagination">

    {{-- Prev --}}
    @if ($paginator->onFirstPage())
        <span class="pha-btn-page disabled">&#8592; Previous</span>
    @else
        <a class="pha-btn-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">&#8592; Previous</a>
    @endif

    {{-- Page Numbers --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pha-pg-dot">&#8230;</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pha-pg pha-pg-active">{{ $page }}</span>
                @else
                    <a class="pha-pg" href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a class="pha-btn-page" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &#8594;</a>
    @else
        <span class="pha-btn-page disabled">Next &#8594;</span>
    @endif

</div>
<div class="pha-pagination-meta">
    Showing <strong>{{ $paginator->firstItem() }}</strong>&ndash;<strong>{{ $paginator->lastItem() }}</strong>
    of <strong>{{ $paginator->total() }}</strong> results
</div>
@endif
