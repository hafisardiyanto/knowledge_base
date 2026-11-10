@if ($paginator->hasPages())
    <nav class="custom-pagination-nav">
        <ul class="custom-pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-nav-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-nav-link">&laquo; Sebelumnya</span>
                </li>
            @else
                <li class="page-nav-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="page-nav-link" rel="prev" aria-label="@lang('pagination.previous')">&laquo; Sebelumnya</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-nav-item disabled" aria-disabled="true"><span class="page-nav-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-nav-item active" aria-current="page"><span class="page-nav-link">{{ $page }}</span></li>
                        @else
                            <li class="page-nav-item"><a href="{{ $url }}" class="page-nav-link">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-nav-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="page-nav-link" rel="next" aria-label="@lang('pagination.next')">Berikutnya &raquo;</a>
                </li>
            @else
                <li class="page-nav-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-nav-link">Berikutnya &raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif

