@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '중견/대기업, 학회/협회, 공공기관, 병원/의료, 대학/학원 등 다양한 분야의 홈페이지 제작 포트폴리오를 확인하세요. 홈페이지제작, 유지보수, 온라인쇼핑몰, SI시스템개발, 앱개발, AI솔루션까지 제공합니다.')
@section('sga_plus')
@php
    $sgaJsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
@endphp
,"mainEntity": {
    "@@type": "ItemList",
    "name": "@yield('title', '')",
    "description": "@yield('description')",
    "numberOfItems": "{{ $portfolioCount }}",
    "itemListElement": @json($listItems, $sgaJsonFlags)
}
@endsection

@section('content')
<main class="sub_contents_wrap">

	<section class="svisual g{{ $gNum }}" aria-labelledby="sub-visual-title">
		<div class="bg_box mojo_aos">
			<div class="inner">
				{{-- 현재 위치 정보를 제공하는 내비게이션 --}}
				<nav class="location" aria-label="현재 위치">
					<a href="/" class="home" aria-label="홈으로 이동">HOME</a>
					<span>{{ $gName }}</span>
					@if(isset($gNum) && ($gNum == '01' || $gNum == '02'))<span aria-current="location">{{ $sName }}</span>@endif
				</nav>
				<h1 class="sound_only">홈페이지코리아 포트폴리오</h1>
				<div id="sub-visual-title" class="title" aria-hidden="true">{{ $sName ?? '' }}</div>
				<h2 class="h2">홈페이지코리아와 함께 성장한 <br class="mo_vw">1,100개의 기업·기관을 확인하세요.</h2>
			</div>
		</div>
	</section>

	<section class="board_wrap" aria-label="Portfolio-list" data-header="light">
		<div class="inner">
			<h2 id="Portfolio-list" class="sound_only">전체 포트폴리오 목록</h2>
			
			<div class="board_top mojo_aos">
				<nav aria-label="포트폴리오 카테고리 필터">
					<ul class="tabs">
                        <li class="{{ $category === '' ? 'on' : '' }}">
                            <a href="{{ route('portfolio.portfolio_list', array_filter(['q' => $keyword])) }}" @if($category === '') aria-current="page" @endif>전체</a>
                        </li>
                        @foreach(\App\Models\Portfolio::CATEGORIES as $itemCategory)
						<li class="{{ $category === $itemCategory ? 'on' : '' }}">
                            <a href="{{ route('portfolio.portfolio_list', array_filter(['category' => $itemCategory, 'q' => $keyword])) }}" @if($category === $itemCategory) aria-current="page" @endif>{{ $itemCategory }}</a>
                        </li>
                        @endforeach
					</ul>
				</nav>
				<div class="search_area">
					<form action="{{ route('portfolio.portfolio_list') }}" role="search" method="GET">
						<label for="portfolio-search" class="sound_only">포트폴리오 검색</label>
						<div class="flex">
                            @if($category !== '')
                            <input type="hidden" name="category" value="{{ $category }}">
                            @endif
							<input type="text" id="portfolio-search" class="text" name="q" value="{{ $keyword }}" placeholder="검색어를 입력해 주세요. #결제 #유지보수 #앱">
							<button type="submit" class="btn">검색</button>
						</div>
					</form>
				</div>
			</div>
			
			<ul class="portfolio_list mojo_aos">
                @forelse($portfolios as $item)
                @php
                    $disableDetailLink = $item->is_direct_site_link && blank($item->site_url);
                @endphp
				<li>
                    @if($disableDetailLink)
					<span class="box" aria-disabled="true" tabindex="-1" aria-label="{{ $item->title }} 포트폴리오 링크 정보 없음">
                    @else
					<a href="{{ $item->publicListHref() }}" class="box" @if($item->publicListOpensInNewTab()) target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $item->title }} 포트폴리오 보기">
                    @endif
						<span class="img_area" aria-hidden="true">
                            @if(!empty($item->thumbnail_image))
							<span class="imgfit">
								<img src="{{ \Illuminate\Support\Facades\Storage::url($item->thumbnail_image) }}" alt="">
								<svg class="border_svg" viewBox="0 0 100 100" preserveAspectRatio="none">
									<defs>
										<linearGradient id="orangeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
											<stop offset="0%" stop-color="#FD710E" />
											<stop offset="100%" stop-color="#036AD8" />
										</linearGradient>
									</defs>
									<rect x="0" y="0" width="100" height="100" rx="2" ry="2" style="stroke: url(#orangeGrad);" />
								</svg>
							</span>
                            @endif
						</span>
						<span class="txt">
                            <span class="type">
                                <span class="industry">{{ $item->category ?: ($item->categories[0] ?? '-') }}</span>
                                <span class="tech">{{ $item->development_summary }}</span>
                            </span>
							<h3 class="tit">{{ $item->title }}</h3>
							<ul class="tags">
                                @foreach(($item->keywords ?? []) as $tag)
								<li>{{ $tag }}</li>
                                @endforeach
							</ul>
						</span>
                    @if($disableDetailLink)
					</span>
                    @else
					</a>
                    @endif
				</li>
                @empty
                <li>
                    <span class="box">
                        <span class="txt">
                            <h3 class="tit">등록된 포트폴리오가 없습니다.</h3>
                        </span>
                    </span>
                </li>
                @endforelse
			</ul>
			
			{{-- <div class="board-pagination">
                {{ $portfolios->links() }}
			</div> --}}
			<div class="board-pagination">
				@php
					$blockSize = max(1, (int) $portfolios->perPage()); // 리밋 수만큼 페이지 묶음 이동
					$totalPages = max(1, (int) $portfolios->lastPage());
					$currentPage = (int) $portfolios->currentPage();
					$currentBlock = (int) floor(($currentPage - 1) / $blockSize);
					$startPage = ($currentBlock * $blockSize) + 1;
					$endPage = min($startPage + $blockSize - 1, $totalPages);
					$hasPrevBlock = $startPage > 1;
					$hasNextBlock = $endPage < $totalPages;
					$prevBlockPage = max(1, $startPage - $blockSize);
					$nextBlockPage = min($totalPages, $startPage + $blockSize);
				@endphp
				<ul class="pagination">
					@if ($portfolios->onFirstPage())
					<li class="page-item arw_item disabled"><span class="page-link" aria-disabled="true"><i class="arrow two first"></i></span></li>
					@else
					<li class="page-item arw_item"><a href="{{ $portfolios->url(1) }}" class="page-link" title="첫 페이지로"><i class="arrow two first"></i></a></li>
					@endif

					@if ($hasPrevBlock)
					<li class="page-item arw_item"><a href="{{ $portfolios->url($prevBlockPage) }}" class="page-link" rel="prev" title="이전 단계"><i class="arrow one prev"></i></a></li>
					@else
					<li class="page-item arw_item disabled"><span class="page-link" aria-disabled="true"><i class="arrow one prev"></i></span></li>
					@endif

					@for ($page = $startPage; $page <= $endPage; $page++)
						@if ($page === $portfolios->currentPage())
					<li class="page-item active"><span class="page-link">{{ $page }}</span></li>
						@else
					<li class="page-item"><a class="page-link" href="{{ $portfolios->url($page) }}">{{ $page }}</a></li>
						@endif
					@endfor

					@if ($hasNextBlock)
					<li class="page-item arw_item"><a href="{{ $portfolios->url($nextBlockPage) }}" class="page-link" rel="next" title="다음 단계"><i class="arrow one next"></i></a></li>
					@else
					<li class="page-item arw_item disabled"><span class="page-link" aria-disabled="true"><i class="arrow one next"></i></span></li>
					@endif

					@if ($portfolios->hasMorePages())
					<li class="page-item arw_item"><a href="{{ $portfolios->url($portfolios->lastPage()) }}" class="page-link" title="끝 페이지로"><i class="arrow two last"></i></a></li>
					@else
					<li class="page-item arw_item disabled"><span class="page-link" aria-disabled="true"><i class="arrow two last"></i></span></li>
					@endif
				</ul>
			</div>
		</div>
	</section>
	
	<aside class="pop_notice" role="dialog" aria-labelledby="notice-title" aria-modal="false">
		<button type="button" class="btn_close" aria-label="공지사항 닫기"></button>
		<div class="flip">
			<div class="before" aria-hidden="true"></div>
			<div class="after">
				<h2 id="notice-title">안녕하세요!<br/> 홈코 포트폴리오에 <br class="pc_vw"/>관심 가져주셔서 감사합니다.</h2>
				<p>현재 사이트 리뉴얼 이후 <br class="mo_vw">포트폴리오를 정리 중입니다.<br/>더 좋은 내용으로 곧 업데이트될 <br class="mo_vw">예정이에요. 😎<br/>잠시만 기다려주시고, 다른 메뉴도 <br class="pc_vw"/>함께 둘러봐 주세요! 감사합니다.</p>
			</div>
		</div>
	</aside>

</main>
@endsection

@push('scripts')
<script src="{{ asset('js/portfolio-index.js') }}"></script>
@endpush