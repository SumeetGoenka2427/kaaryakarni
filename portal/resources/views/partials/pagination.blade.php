{{-- Pagination in the site's own style (no Tailwind). Used by every paginated list. --}}
@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="{{ __('pagination.label') }}">
        @if ($paginator->onFirstPage())
            <span class="page-link is-disabled" aria-disabled="true">{!! __('pagination.previous') !!}</span>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{!! __('pagination.previous') !!}</a>
        @endif

        @isset($elements)
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-link is-disabled" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-link is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endisset

        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{!! __('pagination.next') !!}</a>
        @else
            <span class="page-link is-disabled" aria-disabled="true">{!! __('pagination.next') !!}</span>
        @endif
    </nav>
@endif
