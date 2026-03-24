@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '홈페이지 기획부터 SEO 최적화, 사용자 경험 개선까지, 성공적인 온라인 비즈니스를 위한 유용한 인사이트를 만나보세요.')
@section('sga_plus')
,"mainEntity": {
	"@@type": "ItemList",
	"name": @json($sName),
	"description": @json('홈페이지 기획부터 SEO 최적화, 사용자 경험 개선까지, 성공적인 온라인 비즈니스를 위한 유용한 인사이트를 만나보세요.'),
	"numberOfItems": @json($posts->total()),
	"itemListElement": @json($listItems)
},
@endsection

@section('content')
<main class="sub_contents_wrap">

	<section class="svisual g{{ $gNum }}" aria-labelledby="sub-visual-title" data-header="light">
		<div class="bg_box mojo_aos">
			<div class="inner">
				<nav class="location" aria-label="현재 위치">
					<a href="/" class="home" aria-label="홈으로 이동">HOME</a>
					<span>{{ $gName }}</span>
					@if(isset($gNum) && ($gNum == '01' || $gNum == '02'))<span aria-current="location">{{ $sName }}</span>@endif
				</nav>
				<h1 class="sound_only">홈페이지코리아 블로그</h1>
				<div id="sub-visual-title" class="title" aria-hidden="true">{{ $sName ?? '' }}</div>
				<h2 class="h2">홈페이지 제작부터 SEO/GEO, 사용자 경험 개선까지, 성공적인 온라인 비즈니스를 위한 인사이트를 만나보세요.</h2>
			</div>
		</div>
	</section>

	<section class="board_wrap" aria-label="blog-list" data-header="light">
		<div class="inner">
			<h2 id="blog-list" class="sound_only">전체 블로그 목록</h2>

            @if($featuredPost)
			<a href="{{ route('blog.blog_view', ['blogPost' => $featuredPost->id]) }}" class="blog_main_banner flex mojo_aos">
                @if($featuredPost->thumbnail_path)
				<span class="imgfit" aria-hidden="true">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($featuredPost->thumbnail_path) }}" alt="">
                </span>
                @endif
				<span class="txt">
					<span class="type">{{ $featuredPost->category_label }}</span>
					<h3>{{ $featuredPost->title }}</h3>
					<p>{{ $featuredExcerpt }}</p>
					<time class="date" datetime="{{ optional($featuredPost->published_at)->toDateString() }}">{{ optional($featuredPost->published_at)->format('Y.m.d') }}</time>
				</span>
			</a>
            @endif

			<div class="blog_tit" data-aos="fade-up">홈페이지코리아의 소식을 만나보세요.</div>
			<div class="board_top" data-aos="fade-up">
				<nav aria-label="블로그 카테고리 필터">
					<ul class="tabs">
						<li class="{{ $category === '' ? 'on' : '' }}">
                            <a href="{{ route('blog.blog_list', array_filter(['keyword' => $keyword])) }}" @if($category === '') aria-current="page" @endif>전체</a>
                        </li>
                        @foreach(\App\Models\BlogPost::CATEGORIES as $value => $label)
						<li class="{{ $category === $value ? 'on' : '' }}">
                            <a href="{{ route('blog.blog_list', array_filter(['category' => $value, 'keyword' => $keyword])) }}" @if($category === $value) aria-current="page" @endif>{{ $label }}</a>
                        </li>
                        @endforeach
					</ul>
				</nav>
				<div class="search_area">
					<form action="{{ route('blog.blog_list') }}" method="GET" role="search">
						<label for="blog-search" class="sound_only">블로그 검색</label>
						<div class="flex">
							<input type="text" id="blog-search" name="keyword" class="text" placeholder="검색어를 입력해 주세요." value="{{ $keyword }}">
                            @if($category !== '')
                            <input type="hidden" name="category" value="{{ $category }}">
                            @endif
							<button type="submit" class="btn">검색</button>
						</div>
					</form>
				</div>
			</div>

			<ul class="blog_list">
                @forelse($posts as $post)
				<li data-aos="fade-up">
					<a href="{{ route('blog.blog_view', ['blogPost' => $post->id]) }}">
                        @if($post->thumbnail_path)
						<span class="imgfit" aria-hidden="true"><img src="{{ \Illuminate\Support\Facades\Storage::url($post->thumbnail_path) }}" alt=""></span>
                        @endif
						<span class="txt">
							<span class="type">{{ $post->category_label }}</span>
							<h3>{{ $post->title }}</h3>
							<time class="date" datetime="{{ optional($post->published_at)->toDateString() }}">{{ optional($post->published_at)->format('Y.m.d') }}</time>
						</span>
					</a>
				</li>
                @empty
                <li data-aos="fade-up">
                    <span class="txt">
                        <h3>등록된 블로그가 없습니다.</h3>
                    </span>
                </li>
                @endforelse
			</ul>

            @if($posts->lastPage() > 1)
			<div class="board-pagination">
				<ul class="pagination">
					<li class="page-item arw_item {{ $posts->onFirstPage() ? 'disabled' : '' }}">
                        <a href="{{ $posts->url(1) ?: '#' }}" class="page-link" title="첫 페이지로"><i class="arrow two first"></i></a>
                    </li>
					<li class="page-item arw_item {{ $posts->onFirstPage() ? 'disabled' : '' }}">
                        <a href="{{ $posts->previousPageUrl() ?: '#' }}" class="page-link" rel="prev" title="이전 페이지"><i class="arrow one prev"></i></a>
                    </li>
                    @for($page = 1; $page <= $posts->lastPage(); $page++)
                    @if($page === 1 || $page === $posts->lastPage() || abs($posts->currentPage() - $page) <= 1)
					<li class="page-item {{ $posts->currentPage() === $page ? 'active' : '' }}">
                        @if($posts->currentPage() === $page)
						<span class="page-link">{{ $page }}</span>
                        @else
						<a class="page-link" href="{{ $posts->url($page) }}">{{ $page }}</a>
                        @endif
                    </li>
                    @endif
                    @endfor
					<li class="page-item arw_item {{ $posts->hasMorePages() ? '' : 'disabled' }}">
                        <a href="{{ $posts->nextPageUrl() ?: '#' }}" class="page-link" title="다음 페이지"><i class="arrow one next"></i></a>
                    </li>
					<li class="page-item arw_item {{ $posts->hasMorePages() ? '' : 'disabled' }}">
                        <a href="{{ $posts->url($posts->lastPage()) ?: '#' }}" class="page-link" title="끝 페이지로"><i class="arrow two last"></i></a>
                    </li>
				</ul>
			</div>
            @endif
		</div>
	</section>

</main>

@endsection

@push('scripts')
<script src="{{ asset('js/blog-index.js') }}"></script>
@endpush