@props(['paginator'])

@php
    $lastPage = max(1, (int) $paginator->lastPage());
    $currentPage = (int) $paginator->currentPage();
    /** 한 번에 표시할 페이지 번호 개수 (블록 크기) */
    $blockSize = 10;
    $currentBlock = (int) floor(($currentPage - 1) / $blockSize);
    $startPage = $currentBlock * $blockSize + 1;
    $endPage = min($lastPage, $startPage + $blockSize - 1);
    $hasPrevBlock = $startPage > 1;
    $hasNextBlock = $endPage < $lastPage;
    $prevBlockFirst = max(1, $startPage - $blockSize);
    $nextBlockFirst = min($lastPage, $endPage + 1);
@endphp

<div class="board-pagination">
    <nav aria-label="페이지 네비게이션">
        <ul class="pagination">
            {{-- 첫 페이지 --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-angle-double-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}" title="첫 페이지">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
            @endif

            {{-- 이전 블록 (표시 구간만큼 앞으로) --}}
            @if (! $hasPrevBlock)
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($prevBlockFirst) }}" rel="prev" title="이전 {{ $blockSize }}페이지 묶음">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- 현재 블록의 페이지 번호 --}}
            @foreach ($paginator->getUrlRange($startPage, $endPage) as $page => $url)
                @if ($page === $currentPage)
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- 다음 블록 --}}
            @if (! $hasNextBlock)
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($nextBlockFirst) }}" rel="next" title="다음 {{ $blockSize }}페이지 묶음">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @endif

            {{-- 마지막 페이지 --}}
            @if ($currentPage >= $lastPage)
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-angle-double-right"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($lastPage) }}" title="마지막 페이지">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
</div>
