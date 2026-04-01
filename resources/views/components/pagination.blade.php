@props(['paginator'])

<div class="board-pagination">
    <nav aria-label="페이지 네비게이션">
        @php
            $lastPage = $paginator->lastPage();
            $currentPage = $paginator->currentPage();
            $prevTenPage = max(1, $currentPage - 10);
            $nextTenPage = min($lastPage, $currentPage + 10);
        @endphp
        <ul class="pagination">
            {{-- 첫 페이지로 이동 --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-angle-double-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}" title="첫 페이지로">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
            @endif

            {{-- 이전 페이지 링크 --}}
            @if ($currentPage <= 10)
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($prevTenPage) }}" rel="prev" title="이전 10페이지">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- 페이지 번호들 --}}
            @php $endPage = min($lastPage, 10); @endphp

            @foreach ($paginator->getUrlRange(1, $endPage) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- 다음 페이지 링크 --}}
            @if ($currentPage + 10 <= $lastPage)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($nextTenPage) }}" rel="next" title="다음 10페이지">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif

            {{-- 마지막 페이지로 이동 --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" title="마지막 페이지로">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-angle-double-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>   
   
</div>
